<?php

namespace App\Services\Customers;

use App\Enums\Customers\RfmSegment;

/**
 * Single source of truth for RFM segment thresholds + classification.
 *
 * Used by RfmReport (admin analytics) and ResolveCampaignRecipients
 * (customer email campaign targeting). Extracting here means changing
 * a threshold updates both surfaces in lockstep.
 */
class RfmClassifier
{
    public const int RECENT_DAYS = 30;

    public const int ENGAGED_DAYS = 60;

    public const int AT_RISK_DAYS = 180;

    public const int FREQUENT_ORDERS = 4;

    public const int LOYAL_ORDERS = 3;

    public const int BIG_SPEND_DOLLARS = 500;

    public const int LOYAL_SPEND_DOLLARS = 200;

    public function classify(int $recencyDays, int $frequency, float $monetary): RfmSegment
    {
        if ($recencyDays < self::RECENT_DAYS && $frequency >= self::FREQUENT_ORDERS && $monetary >= self::BIG_SPEND_DOLLARS) {
            return RfmSegment::Champions;
        }

        if ($recencyDays < self::ENGAGED_DAYS && $frequency >= self::LOYAL_ORDERS && $monetary >= self::LOYAL_SPEND_DOLLARS) {
            return RfmSegment::Loyal;
        }

        if ($recencyDays >= self::ENGAGED_DAYS && $recencyDays < self::AT_RISK_DAYS && $frequency >= self::LOYAL_ORDERS && $monetary >= self::LOYAL_SPEND_DOLLARS) {
            return RfmSegment::AtRisk;
        }

        if ($recencyDays < self::RECENT_DAYS && $frequency <= 2) {
            return RfmSegment::New;
        }

        return RfmSegment::Hibernating;
    }
}
