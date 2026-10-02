<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Marketing;

use App\Http\Controllers\Controller;
use App\Mail\Customers\CustomerCampaignMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\CustomerCampaign;

class PreviewCustomerCampaignController extends Controller
{
    public function __invoke(CustomerCampaign $campaign): CustomerCampaignMail
    {
        // Stand-in recipient so the preview shows the unsubscribe footer; its link points nowhere real.
        $recipient = new Customer;
        $recipient->id = 0;
        $recipient->name = 'Preview customer';

        return new CustomerCampaignMail($campaign, $recipient);
    }
}
