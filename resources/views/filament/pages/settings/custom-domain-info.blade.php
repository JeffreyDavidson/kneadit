<div class="space-y-4">
    {{-- DNS Instructions --}}
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
        <h4 class="mb-2 flex items-center gap-2 text-sm font-semibold text-blue-900 dark:text-blue-200">
            <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-4 w-4" />
            DNS Setup Instructions
        </h4>
        <ol class="list-inside list-decimal space-y-1 text-sm text-blue-800 dark:text-blue-300">
            <li>Go to your domain registrar's DNS settings</li>
            <li>
                Add an <strong>A record</strong> pointing to:
                <code class="rounded bg-blue-100 px-1.5 py-0.5 font-mono text-xs dark:bg-blue-800">{{ config('services.forge.server_ip') }}</code>
            </li>
            <li>Save the changes and wait for DNS propagation (up to 48 hours)</li>
            <li>Come back here and click "Verify DNS"</li>
        </ol>
    </div>

    {{-- Status --}}
    @if ($this->dns_status)
        <div @class([
            'rounded-xl border p-4',
            'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' => $this->dns_status === \App\Enums\Platform\DnsVerificationStatus::Verified,
            'border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-900/20' => $this->dns_status === \App\Enums\Platform\DnsVerificationStatus::Pending,
        ])>
            <div class="flex items-center gap-2">
                @if ($this->dns_status === \App\Enums\Platform\DnsVerificationStatus::Verified)
                    <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5 text-green-600" />
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">DNS Verified — Your domain is active!</span>
                @else
                    <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 text-yellow-600" />
                    <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">DNS Pending — Domain not yet pointing to our server</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Plan Note --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-light-bulb" class="inline h-4 w-4 align-[-2px]" />
            <strong>Custom domains are available on Growth and Pro plans.</strong>
            Your current plan: <span class="font-semibold capitalize">{{ tenant()->plan?->value ?? 'trial' }}</span>
        </p>
    </div>
</div>
