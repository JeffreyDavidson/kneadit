<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\CreateCateringInquiry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreCateringInquiryRequest;
use Illuminate\Http\RedirectResponse;

class SubmitCateringInquiryController extends Controller
{
    public function __invoke(StoreCateringInquiryRequest $request, CreateCateringInquiry $createInquiry): RedirectResponse
    {
        $createInquiry($request->validated());

        $message = settingsPageContent('catering')['flash_success']
            ?? "Thank you for your inquiry! We'll review your request and get back to you with a custom quote soon.";

        return to_route('storefront.catering')->with('success', $message);
    }
}
