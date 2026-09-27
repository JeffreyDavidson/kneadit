<?php

namespace App\ViewModels\Filament\CateringInquiries;

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use App\Models\Orders\Order;
use App\Services\Customers\CateringDepositCalculator;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Carbon;

final readonly class CateringInquiryViewModel
{
    public CateringInquiryStatus $status;

    public ?Carbon $eventDate;

    public ?string $eventCountdown;

    public bool $eventPast;

    public bool $depositPaid;

    public int $depositPercent;

    public ?float $suggestedDeposit;

    /** @var array{label: string, bg: string, border: string, text: string}|null */
    public ?array $depositChip;

    public function __construct(
        public CateringInquiry $inquiry,
        public ?Order $order,
        TenantSettings $settings,
        CateringDepositCalculator $depositCalculator,
    ) {
        $this->status = $inquiry->status;
        $this->eventDate = $inquiry->event_date;
        $this->eventCountdown = $this->eventDate?->isFuture()
            ? $this->eventDate->diffForHumans(['parts' => 1, 'short' => false])
            : null;
        $this->eventPast = $this->eventDate?->isPast() ?? false;
        $this->depositPaid = $inquiry->deposit_paid_at !== null;
        $this->depositPercent = $settings->catering->depositPercent;
        $this->suggestedDeposit = $inquiry->quoted_amount && $this->depositPercent > 0
            ? $depositCalculator->suggestedAmount($inquiry, $this->depositPercent)
            : null;
        $this->depositChip = match (true) {
            $this->depositPaid => ['label' => 'Deposit received', 'bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400'],
            in_array($this->status, [CateringInquiryStatus::Quoted, CateringInquiryStatus::Confirmed], true) => ['label' => 'Deposit pending', 'bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/25', 'text' => 'text-amber-400'],
            default => null,
        };
    }
}
