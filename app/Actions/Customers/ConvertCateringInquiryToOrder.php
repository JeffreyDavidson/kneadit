<?php

namespace App\Actions\Customers;

use App\Actions\Orders\GenerateOrderNumber;
use App\Enums\Customers\CateringInquiryStatus;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Events\Orders\OrderCreated;
use App\Exceptions\Customers\InquiryNotConvertibleException;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;

class ConvertCateringInquiryToOrder
{
    public function __construct(
        private readonly GenerateOrderNumber $generateOrderNumber,
        private readonly TransitionCateringInquiryStatus $transitionInquiry,
    ) {}

    public function __invoke(CateringInquiry $inquiry): Order
    {
        if ($existing = $inquiry->order()->first()) {
            return $existing;
        }

        if ($inquiry->quoted_amount === null) {
            throw new InquiryNotConvertibleException($inquiry, 'No quoted amount set; revise the quote before confirming.');
        }

        $order = DB::transaction(function () use ($inquiry): Order {
            $customer = $this->findOrCreateCustomer($inquiry);

            $paymentStatus = $inquiry->deposit_paid_at
                ? PaymentStatus::Partial
                : PaymentStatus::Unpaid;

            $order = Order::query()->create([
                'order_number' => ($this->generateOrderNumber)(),
                'customer_id' => $customer->id,
                'catering_inquiry_id' => $inquiry->id,
                'status' => OrderStatus::Confirmed,
                'payment_status' => $paymentStatus,
                'subtotal' => $inquiry->quoted_amount,
                'total' => $inquiry->quoted_amount,
                'delivery_date' => $inquiry->event_date,
                'notes' => $inquiry->notes,
                'user_id' => auth()->id(),
            ]);

            $items = $inquiry->items()->get();

            if ($items->isNotEmpty()) {
                foreach ($items as $item) {
                    $order->orderItems()->create([
                        'name' => $item->name,
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'special_instructions' => $item->special_instructions,
                    ]);
                }
            } else {
                // Graceful fallback for inquiries quoted before the line-items
                // editor existed: collapse the whole quote into one summary line.
                $order->orderItems()->create([
                    'name' => "Catering — {$inquiry->event_type}, " . number_format($inquiry->guest_count) . ' guests',
                    'unit_price' => $inquiry->quoted_amount,
                    'quantity' => 1,
                ]);
            }

            ($this->transitionInquiry)($inquiry, CateringInquiryStatus::Confirmed);

            return $order;
        });

        event(new OrderCreated($order));

        return $order;
    }

    private function findOrCreateCustomer(CateringInquiry $inquiry): Customer
    {
        if ($inquiry->customer_email) {
            $existing = Customer::query()->forEmail($inquiry->customer_email)->first();

            if ($existing) {
                return $existing;
            }
        }

        return Customer::query()->create([
            'name' => $inquiry->customer_name,
            'email' => $inquiry->customer_email,
            'phone' => $inquiry->customer_phone,
        ]);
    }
}
