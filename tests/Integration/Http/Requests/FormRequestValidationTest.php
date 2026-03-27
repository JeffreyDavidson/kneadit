<?php

use App\Http\Requests\ApplyCouponRequest;
use App\Http\Requests\ApplyGiftCardRequest;
use App\Http\Requests\CheckGiftCardBalanceRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\PurchaseGiftCardRequest;
use App\Http\Requests\RedeemLoyaltyRewardRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\StoreApiContactRequest;
use App\Http\Requests\StoreContactMessageRequest;
use App\Http\Requests\StoreOrderMessageRequest;
use App\Http\Requests\StoreSurveyResponseRequest;
use App\Http\Requests\TrackOrderRequest;

dataset('requestsWithRequiredFields', [
    'LoginRequest' => [LoginRequest::class, ['email', 'password']],
    'ForgotPasswordRequest' => [ForgotPasswordRequest::class, ['email']],
    'ResetPasswordRequest' => [ResetPasswordRequest::class, ['token', 'email', 'password']],
    'ApplyCouponRequest' => [ApplyCouponRequest::class, ['code', 'subtotal']],
    'ApplyGiftCardRequest' => [ApplyGiftCardRequest::class, ['code', 'subtotal']],
    'CheckGiftCardBalanceRequest' => [CheckGiftCardBalanceRequest::class, ['code']],
    'RedeemLoyaltyRewardRequest' => [RedeemLoyaltyRewardRequest::class, ['email']],
    'TrackOrderRequest' => [TrackOrderRequest::class, ['email']],
    'StoreContactMessageRequest' => [StoreContactMessageRequest::class, ['name', 'email', 'subject', 'message']],
    'StoreApiContactRequest' => [StoreApiContactRequest::class, ['name', 'email', 'subject', 'message']],
    'StoreOrderMessageRequest' => [StoreOrderMessageRequest::class, ['message', 'sender_name', 'sender_email']],
    'StoreSurveyResponseRequest' => [StoreSurveyResponseRequest::class, ['answers']],
    'PurchaseGiftCardRequest' => [PurchaseGiftCardRequest::class, ['purchaser_name', 'purchaser_email', 'initial_balance']],
]);

test('form request rejects empty data for required fields', function (string $requestClass, array $requiredFields) {
    $request = new $requestClass;
    $validator = validator([], $request->rules());

    expect($validator->fails())->toBeTrue();

    foreach ($requiredFields as $field) {
        expect($validator->errors()->has($field))->toBeTrue(
            "Expected '{$field}' to be required in {$requestClass}",
        );
    }
})->with('requestsWithRequiredFields');

dataset('requestsWithEmailFields', [
    'LoginRequest' => [LoginRequest::class, 'email'],
    'ForgotPasswordRequest' => [ForgotPasswordRequest::class, 'email'],
    'ResetPasswordRequest' => [ResetPasswordRequest::class, 'email'],
    'RedeemLoyaltyRewardRequest' => [RedeemLoyaltyRewardRequest::class, 'email'],
    'TrackOrderRequest' => [TrackOrderRequest::class, 'email'],
    'StoreContactMessageRequest' => [StoreContactMessageRequest::class, 'email'],
    'StoreApiContactRequest' => [StoreApiContactRequest::class, 'email'],
    'StoreOrderMessageRequest' => [StoreOrderMessageRequest::class, 'sender_email'],
    'PurchaseGiftCardRequest' => [PurchaseGiftCardRequest::class, 'purchaser_email'],
]);

test('form request rejects invalid email', function (string $requestClass, string $emailField) {
    $request = new $requestClass;
    $validator = validator([$emailField => 'not-an-email'], $request->rules());

    expect($validator->errors()->has($emailField))->toBeTrue(
        "Expected '{$emailField}' to reject invalid email in {$requestClass}",
    );
})->with('requestsWithEmailFields');
