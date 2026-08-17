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

/** @return array<string, mixed> */
function formRequestRules(string $requestClass): array
{
    if (! is_a($requestClass, FormRequest::class, true)) {
        throw new RuntimeException("{$requestClass} is not a form request.");
    }

    $request = new $requestClass;

    if (! method_exists($request, 'rules')) {
        throw new RuntimeException("{$requestClass} does not define validation rules.");
    }

    return $request->rules();
}

dataset('requestsWithRequiredFields', [
    'ForgotPasswordRequest' => [ForgotPasswordRequest::class, ['email']],
    'ResetPasswordRequest' => [ResetPasswordRequest::class, ['token', 'email', 'password']],
    'ApplyDiscountRequest' => [ApplyDiscountRequest::class, ['code', 'subtotal']],
    'CheckGiftCardBalanceRequest' => [CheckGiftCardBalanceRequest::class, ['code']],
    'RedeemLoyaltyRewardRequest' => [RedeemLoyaltyRewardRequest::class, ['email']],
    'TrackOrderRequest' => [TrackOrderRequest::class, ['email']],
    'StoreContactMessageRequest' => [StoreContactMessageRequest::class, ['name', 'email', 'subject', 'message']],
    'StoreApiContactRequest' => [StoreApiContactRequest::class, ['name', 'email', 'subject', 'message']],
    'StoreOrderMessageRequest' => [StoreOrderMessageRequest::class, ['message', 'sender_name', 'sender_email']],
    'StoreSurveyResponseRequest' => [StoreSurveyResponseRequest::class, ['answers']],
    'PurchaseGiftCardRequest' => [PurchaseGiftCardRequest::class, ['purchaser_name', 'purchaser_email', 'initial_balance']],
    'StoreApiReviewRequest' => [App\Http\Requests\Api\StoreApiReviewRequest::class, ['customer_name', 'customer_email', 'product_id', 'rating', 'comment']],
    'StoreApiWaitlistRequest' => [App\Http\Requests\Api\StoreApiWaitlistRequest::class, ['customer_name', 'customer_email', 'customer_phone', 'requested_date']],
    'StoreReviewRequest' => [App\Http\Requests\Storefront\StoreReviewRequest::class, ['rating']],
    'StoreProductWaitlistRequest' => [App\Http\Requests\Storefront\StoreProductWaitlistRequest::class, ['product_id', 'customer_email']],
    'StoreApiFavoriteRequest' => [App\Http\Requests\Api\StoreApiFavoriteRequest::class, ['email', 'product_id']],
    'StoreApiOrderRequest' => [App\Http\Requests\Api\StoreApiOrderRequest::class, ['customer_name', 'customer_email', 'items', 'delivery_date', 'delivery_type']],
    'StoreOnboardingRequest' => [App\Http\Requests\Storefront\StoreOnboardingRequest::class, ['store_name', 'subdomain', 'storefront_choice']],
    'StoreGalleryPhotoRequest' => [App\Http\Requests\Storefront\StoreGalleryPhotoRequest::class, ['customer_name', 'customer_email', 'photo']],
    // StoreCateringInquiry excluded — rules() calls settings() which needs tenant DB
]);

test('form request rejects empty data for required fields', function (string $requestClass, array $requiredFields) {
    $validator = validator([], formRequestRules($requestClass));

    expect($validator->fails())->toBeTrue();

    foreach ($requiredFields as $field) {
        expect($validator->errors()->has($field))->toBeTrue(
            "Expected '{$field}' to be required in {$requestClass}",
        );
    }
})->with('requestsWithRequiredFields');

dataset('requestsWithEmailFields', [
    'ForgotPasswordRequest' => [ForgotPasswordRequest::class, 'email'],
    'ResetPasswordRequest' => [ResetPasswordRequest::class, 'email'],
    'RedeemLoyaltyRewardRequest' => [RedeemLoyaltyRewardRequest::class, 'email'],
    'TrackOrderRequest' => [TrackOrderRequest::class, 'email'],
    'StoreContactMessageRequest' => [StoreContactMessageRequest::class, 'email'],
    'StoreApiContactRequest' => [StoreApiContactRequest::class, 'email'],
    'StoreOrderMessageRequest' => [StoreOrderMessageRequest::class, 'sender_email'],
    'PurchaseGiftCardRequest' => [PurchaseGiftCardRequest::class, 'purchaser_email'],
    'StoreApiReviewRequest' => [App\Http\Requests\Api\StoreApiReviewRequest::class, 'customer_email'],
    'StoreApiWaitlistRequest' => [App\Http\Requests\Api\StoreApiWaitlistRequest::class, 'customer_email'],
    'StoreApiOrderRequest' => [App\Http\Requests\Api\StoreApiOrderRequest::class, 'customer_email'],
    'StoreProductWaitlistRequest' => [App\Http\Requests\Storefront\StoreProductWaitlistRequest::class, 'customer_email'],
    'StoreGalleryPhotoRequest' => [App\Http\Requests\Storefront\StoreGalleryPhotoRequest::class, 'customer_email'],
    'StoreApiFavoriteRequest' => [App\Http\Requests\Api\StoreApiFavoriteRequest::class, 'email'],
]);

test('form request rejects invalid email', function (string $requestClass, string $emailField) {
    $validator = validator([$emailField => 'not-an-email'], formRequestRules($requestClass));

    expect($validator->errors()->has($emailField))->toBeTrue(
        "Expected '{$emailField}' to reject invalid email in {$requestClass}",
    );
})->with('requestsWithEmailFields');
