<?php

namespace App\Actions\Tenants;

use App\Enums\Platform\PlatformSenderType;
use App\Models\Platform\PlatformMessage;
use App\Models\Platform\Tenant;

class SendTenantNudge
{
    public function __invoke(Tenant $tenant): PlatformMessage
    {
        $storeName = $tenant->store_name ?? $tenant->name;

        return PlatformMessage::query()->create([
            'tenant_id' => $tenant->id,
            'sender_type' => PlatformSenderType::Admin,
            'subject' => "We noticed you haven't been around lately",
            'body' => "Hi {$storeName}!\n\nWe noticed it's been a little quiet on your end. Just wanted to check in — is there anything we can help with?\n\nWhether you need help setting up your storefront, adding products, or just have questions, we're here for you.\n\nThe KneadIt Team",
            'is_read' => false,
        ]);
    }
}
