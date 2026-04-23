<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Mail\Customers\CustomerCampaignMail;
use App\Models\Engagement\CustomerCampaign;

class PreviewCustomerCampaignController extends Controller
{
    public function __invoke(CustomerCampaign $campaign): CustomerCampaignMail
    {
        return new CustomerCampaignMail($campaign, trackingToken: null);
    }
}
