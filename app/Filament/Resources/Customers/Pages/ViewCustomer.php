<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customers\Customer;
use App\Presenters\CustomerPresenter;
use Filament\Resources\Pages\ViewRecord;

/**
 * Customer 360 — single-page aggregation of everything we know about
 * one customer: header stats, recent orders, notes, and address. Backed
 * by CustomerPresenter::toDetailArray() which already memoizes the
 * CustomerIntelligence metrics call.
 *
 * @property-read Customer $record
 */
class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected string $view = 'filament.resources.customers.pages.view-customer';

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        return [
            'detail' => CustomerPresenter::for($this->record)->toDetailArray(),
        ];
    }
}
