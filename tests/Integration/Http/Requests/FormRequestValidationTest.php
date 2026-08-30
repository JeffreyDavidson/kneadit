<?php

use App\Http\Requests\Api\CheckGiftCardBalanceRequest;
use App\Http\Requests\Api\StoreApiContactRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Order\ApplyDiscountRequest;
use App\Http\Requests\Order\PurchaseGiftCardRequest;
use App\Http\Requests\Order\RedeemLoyaltyRewardRequest;
use App\Http\Requests\Storefront\StoreContactMessageRequest;
use App\Http\Requests\Storefront\StoreOrderMessageRequest;
use App\Http\Requests\Storefront\StoreSurveyResponseRequest;
use App\Http\Requests\Storefront\TrackOrderRequest;
use Illuminate\Foundation\Http\FormRequest;

test('form requests reject empty data for required fields', function () {
    /** @var array<class-string<FormRequest>, list<string>> $requests */
    $requests = [
        ForgotPasswordRequest::class => ['email'],
        ResetPasswordRequest::class => ['token', 'email', 'password'],
        ApplyDiscountRequest::class => ['code', 'subtotal'],
        CheckGiftCardBalanceRequest::class => ['code'],
        RedeemLoyaltyRewardRequest::class => ['email'],
        TrackOrderRequest::class => ['email'],
        StoreContactMessageRequest::class => ['name', 'email', 'subject', 'message'],
        StoreApiContactRequest::class => ['name', 'email', 'subject', 'message'],
        StoreOrderMessageRequest::class => ['message', 'sender_name', 'sender_email'],
        StoreSurveyResponseRequest::class => ['answers'],
        PurchaseGiftCardRequest::class => ['purchaser_name', 'purchaser_email', 'initial_balance'],
        App\Http\Requests\Api\StoreApiReviewRequest::class => ['customer_name', 'customer_email', 'product_id', 'rating', 'comment'],
        App\Http\Requests\Api\StoreApiWaitlistRequest::class => ['customer_name', 'customer_email', 'customer_phone', 'requested_date'],
        App\Http\Requests\Storefront\StoreReviewRequest::class => ['rating'],
        App\Http\Requests\Storefront\StoreProductWaitlistRequest::class => ['product_id', 'customer_email'],
        App\Http\Requests\Api\StoreApiFavoriteRequest::class => ['email', 'product_id'],
        App\Http\Requests\Api\StoreApiOrderRequest::class => ['customer_name', 'customer_email', 'items', 'delivery_date', 'delivery_type'],
        App\Http\Requests\Storefront\StoreOnboardingRequest::class => ['store_name', 'subdomain', 'storefront_choice'],
        App\Http\Requests\Storefront\StoreGalleryPhotoRequest::class => ['customer_name', 'customer_email', 'photo'],
        // StoreCateringInquiry excluded — rules() calls settings() which needs tenant DB
    ];

    foreach ($requests as $requestClass => $requiredFields) {
        $request = new $requestClass;
        $validator = validator([], $request->rules());

        expect($validator->fails())->toBeTrue();

        foreach ($requiredFields as $field) {
            expect($validator->errors()->has($field))->toBeTrue(
                "Expected '{$field}' to be required in {$requestClass}",
            );
        }
    }
});

test('form requests reject invalid email values', function () {
    /** @var array<class-string<FormRequest>, string> $requests */
    $requests = [
        ForgotPasswordRequest::class => 'email',
        ResetPasswordRequest::class => 'email',
        RedeemLoyaltyRewardRequest::class => 'email',
        TrackOrderRequest::class => 'email',
        StoreContactMessageRequest::class => 'email',
        StoreApiContactRequest::class => 'email',
        StoreOrderMessageRequest::class => 'sender_email',
        PurchaseGiftCardRequest::class => 'purchaser_email',
        App\Http\Requests\Api\StoreApiReviewRequest::class => 'customer_email',
        App\Http\Requests\Api\StoreApiWaitlistRequest::class => 'customer_email',
        App\Http\Requests\Api\StoreApiOrderRequest::class => 'customer_email',
        App\Http\Requests\Storefront\StoreProductWaitlistRequest::class => 'customer_email',
        App\Http\Requests\Storefront\StoreGalleryPhotoRequest::class => 'customer_email',
        App\Http\Requests\Api\StoreApiFavoriteRequest::class => 'email',
    ];

    foreach ($requests as $requestClass => $emailField) {
        $request = new $requestClass;
        $validator = validator([$emailField => 'not-an-email'], $request->rules());

        expect($validator->errors()->has($emailField))->toBeTrue(
            "Expected '{$emailField}' to reject invalid email in {$requestClass}",
        );
    }
});
