<?php

namespace App\Actions\Customers;

use App\DataTransferObjects\Customers\CateringEventDetails;
use App\Models\Customers\CateringInquiry;

class UpdateCateringEventDetails
{
    public function __invoke(CateringInquiry $inquiry, CateringEventDetails $details): void
    {
        $inquiry->update([
            'event_type' => $details->eventType,
            'event_date' => $details->eventDate,
            'guest_count' => $details->guestCount,
            'budget' => $details->budget,
            'details' => $details->details,
            'dietary_requirements' => $details->dietaryRequirements,
            'venue_address' => $details->venueAddress,
        ]);
    }
}
