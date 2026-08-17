<?php

use App\Actions\Tenants\CompleteReferral;
use App\Actions\Tenants\CompleteTenantOnboarding;
use App\Actions\Tenants\CreateTenant;
use App\Events\Platform\TenantOnboarded;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Event;

test('it creates the tenant before completing the referral and announcing onboarding', function () {
    Event::fake([TenantOnboarded::class]);

    $user = new User(['name' => 'Baker', 'email' => 'baker@example.com']);
    $tenant = new Tenant(['id' => 'daily-bread']);

    $createTenant = new class($tenant) extends CreateTenant {
        /** @var list<string> */
        public array $calls = [];

        public function __construct(private readonly Tenant $tenant) {}

        public function __invoke(
            User $user,
            string $storeName,
            string $subdomain,
            bool $useKneadItStorefront,
            ?string $externalWebsite = null,
        ): Tenant {
            $this->calls[] = "{$user->email}|{$storeName}|{$subdomain}|{$useKneadItStorefront}|{$externalWebsite}";

            return $this->tenant;
        }
    };

    $completeReferral = new class extends CompleteReferral {
        /** @var list<string> */
        public array $calls = [];

        public function __invoke(?string $referralCode, string $tenantId, string $email): void
        {
            $this->calls[] = "{$referralCode}|{$tenantId}|{$email}";
        }
    };

    $result = (new CompleteTenantOnboarding($createTenant, $completeReferral))(
        $user,
        'Daily Bread',
        'daily-bread',
        false,
        'https://dailybread.example',
        'REFERRAL',
        'https://daily-bread.kneadit.test/admin',
    );

    expect($result)->toBe($tenant)
        ->and($createTenant->calls)->toBe([
            'baker@example.com|Daily Bread|daily-bread||https://dailybread.example',
        ])
        ->and($completeReferral->calls)->toBe([
            'REFERRAL|daily-bread|baker@example.com',
        ]);

    Event::assertDispatched(
        TenantOnboarded::class,
        fn (TenantOnboarded $event): bool => $event->user === $user
            && $event->tenant === $tenant
            && $event->adminUrl === 'https://daily-bread.kneadit.test/admin',
    );
});
