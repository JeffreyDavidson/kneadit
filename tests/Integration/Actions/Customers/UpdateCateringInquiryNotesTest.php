<?php

use App\Actions\Customers\UpdateCateringInquiryNotes;
use App\Models\Customers\CateringInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('updates catering inquiry notes', function () {
    $inquiry = CateringInquiry::factory()->create(['notes' => null]);

    resolve(UpdateCateringInquiryNotes::class)(
        $inquiry,
        'Talked to the chef about gluten-free options.',
    );

    expect($inquiry->refresh()->notes)
        ->toBe('Talked to the chef about gluten-free options.');
});
