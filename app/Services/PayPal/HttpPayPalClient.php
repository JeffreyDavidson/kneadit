<?php

namespace App\Services\PayPal;

use App\DataTransferObjects\PayPal\PayPalResponse;
use App\Services\PayPal\Contracts\PayPalClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class HttpPayPalClient implements PayPalClient
{
    public function __construct(private TokenManager $tokenManager) {}

    public function getInvoice(string $invoiceId): PayPalResponse
    {
        return $this->toPayPalResponse($this->request()->get("/v2/invoicing/invoices/{$invoiceId}"));
    }

    /** @param array<string, mixed> $invoiceData */
    public function createInvoice(array $invoiceData, string $requestId): PayPalResponse
    {
        return $this->toPayPalResponse($this->request()
            ->withHeader('PayPal-Request-Id', $requestId)
            ->post('/v2/invoicing/invoices', $invoiceData));
    }

    public function sendInvoice(string $invoiceId): PayPalResponse
    {
        return $this->toPayPalResponse($this->request()
            ->post("/v2/invoicing/invoices/{$invoiceId}/send", ['send_to_invoicer' => true]));
    }

    public function cancelInvoice(string $invoiceId): PayPalResponse
    {
        return $this->toPayPalResponse($this->request()
            ->post("/v2/invoicing/invoices/{$invoiceId}/cancel", [
                'subject' => 'Invoice cancelled',
                'note' => 'This invoice has been cancelled.',
                'send_to_invoicer' => true,
                'send_to_recipient' => true,
            ]));
    }

    private function request(): PendingRequest
    {
        $accessToken = $this->tokenManager->getAccessToken();

        if (! $accessToken) {
            throw new \RuntimeException('PayPal access token is unavailable.');
        }

        return Http::timeout(10)->connectTimeout(3)->retry(3, 100)
            ->withToken($accessToken)
            ->acceptJson()
            ->baseUrl($this->tokenManager->getBaseUrl());
    }

    private function toPayPalResponse(Response $response): PayPalResponse
    {
        $json = $response->json();
        $data = [];

        if (is_array($json)) {
            foreach ($json as $key => $value) {
                if (is_string($key)) {
                    $data[$key] = $value;
                }
            }
        }

        return new PayPalResponse(
            successful: $response->successful(),
            status: $response->status(),
            data: $data,
        );
    }
}
