<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCateringInquiryRequest;
use App\Models\CateringInquiry;
use Illuminate\Http\RedirectResponse;

class SubmitCateringInquiryController extends Controller
{
    public function __invoke(StoreCateringInquiryRequest $request): RedirectResponse
    {
        CateringInquiry::query()->create($request->validated());

        return to_route('storefront.catering')
            ->with('success', 'Thank you for your inquiry! We\'ll review your request and get back to you with a custom quote soon.');
    }
}
