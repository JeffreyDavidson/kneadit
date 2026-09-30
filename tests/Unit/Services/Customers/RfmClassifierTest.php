<?php

use App\Enums\Customers\RfmSegment;
use App\Services\Customers\RfmClassifier;

test('classify assigns the segment for recency, frequency and monetary values', function (float $recencyDays, int $frequency, float $monetary, RfmSegment $expected) {
    $segment = (new RfmClassifier)->classify($recencyDays, $frequency, $monetary);

    expect($segment)->toBe($expected);
})->with([
    'champion at the lower bounds' => [29.9, 4, 500.0, RfmSegment::Champions],
    'champion well above the bounds' => [1.0, 12, 4000.0, RfmSegment::Champions],
    'recency of exactly 30 days is no longer a champion' => [30.0, 4, 500.0, RfmSegment::Loyal],
    'one order short of champion' => [10.0, 3, 500.0, RfmSegment::Loyal],
    'spend just under the champion threshold' => [10.0, 4, 499.99, RfmSegment::Loyal],
    'loyal at the lower bounds' => [59.9, 3, 200.0, RfmSegment::Loyal],
    'recency of exactly 60 days is at risk' => [60.0, 3, 200.0, RfmSegment::AtRisk],
    'spend just under the loyal threshold' => [59.9, 3, 199.99, RfmSegment::Hibernating],
    'frequency just under the loyal threshold' => [59.9, 2, 1000.0, RfmSegment::Hibernating],
    'at risk just before the upper recency bound' => [179.9, 5, 900.0, RfmSegment::AtRisk],
    'recency of exactly 180 days is hibernating' => [180.0, 3, 200.0, RfmSegment::Hibernating],
    'at risk needs the loyal spend' => [100.0, 3, 199.99, RfmSegment::Hibernating],
    'new customer with one order' => [2.0, 1, 40.0, RfmSegment::New],
    'new customer at the upper bounds' => [29.9, 2, 450.0, RfmSegment::New],
    'recency of exactly 30 days is no longer new' => [30.0, 2, 40.0, RfmSegment::Hibernating],
    'recent customer with three low-spend orders' => [10.0, 3, 50.0, RfmSegment::Hibernating],
    'long inactive customer' => [400.0, 1, 25.0, RfmSegment::Hibernating],
    'customer with no activity' => [0.0, 0, 0.0, RfmSegment::New],
]);

test('thresholds are exposed as constants shared by the report and campaign surfaces', function () {
    expect(RfmClassifier::RECENT_DAYS)->toBe(30)
        ->and(RfmClassifier::ENGAGED_DAYS)->toBe(60)
        ->and(RfmClassifier::AT_RISK_DAYS)->toBe(180)
        ->and(RfmClassifier::FREQUENT_ORDERS)->toBe(4)
        ->and(RfmClassifier::LOYAL_ORDERS)->toBe(3)
        ->and(RfmClassifier::BIG_SPEND_DOLLARS)->toBe(500)
        ->and(RfmClassifier::LOYAL_SPEND_DOLLARS)->toBe(200);
});
