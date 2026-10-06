<div class="space-y-4">
    {{-- DNS Instructions --}}
    <div class="rounded-xl border border-(--kn-info) bg-(--kn-info-tint) p-4 dark:border-blue-800 dark:bg-blue-900/20">
        <h4 class="mb-2 flex items-center gap-2 text-sm font-semibold text-(--kn-info) dark:text-blue-200">
            <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-4 w-4" />
            DNS Setup Instructions
        </h4>
        <ol class="list-inside list-decimal space-y-1 text-sm text-(--kn-info) dark:text-blue-300">
            <li>Go to your domain registrar's DNS settings</li>
            <li>
                Add an <strong>A record</strong> pointing to:
                <code class="rounded bg-(--kn-info-tint) px-1.5 py-0.5 font-mono text-xs dark:bg-blue-800">{{ config('services.forge.server_ip') }}</code>
            </li>
            <li>Save the changes and wait for DNS propagation (up to 48 hours)</li>
            <li>Come back here and click "Verify domain"</li>
        </ol>
    </div>

    {{-- Status --}}
    @if ($this->dns_status)
        @php($dnsOk = $this->dns_status === \App\Enums\Platform\DnsVerificationStatus::Verified)
        <div @class([
            'rounded-xl border p-4',
            'border-(--kn-success) bg-(--kn-success-tint) dark:border-green-800 dark:bg-green-900/20' => $dnsOk && $this->https_ok,
            'border-(--kn-warning) bg-(--kn-warning-tint) dark:border-yellow-800 dark:bg-yellow-900/20' => ! ($dnsOk && $this->https_ok),
        ])>
            <ul class="space-y-1 text-sm font-medium">
                <li class="flex items-center gap-2">
                    <x-filament::icon
                        :icon="$dnsOk ? 'heroicon-o-check-circle' : 'heroicon-o-clock'"
                        :class="$dnsOk ? 'h-5 w-5 text-(--kn-success)' : 'h-5 w-5 text-(--kn-warning)'"
                    />
                    <span>{{ $this->via_proxy ? 'DNS: served through a proxy (Cloudflare)' : ($dnsOk ? 'DNS: OK' : 'DNS: not pointing here') }}</span>
                </li>
                @if ($dnsOk)
                    <li class="flex items-center gap-2">
                        <x-filament::icon
                            :icon="$this->https_ok ? 'heroicon-o-check-circle' : 'heroicon-o-clock'"
                            :class="$this->https_ok ? 'h-5 w-5 text-(--kn-success)' : 'h-5 w-5 text-(--kn-warning)'"
                        />
                        <span>{{ $this->https_ok ? 'HTTPS: OK' : 'HTTPS: no valid certificate yet' }}</span>
                    </li>
                @endif
            </ul>
        </div>
    @endif

    {{-- Link status --}}
    @if (tenant()->custom_domain)
        <p class="text-sm text-(--kn-muted)">
            @if ($this->verifiedOn)
                Verified on {{ $this->verifiedOn }}. Links in your emails and messages use {{ $this->linkBaseUrl }}.
            @else
                Not verified: links in your emails and messages use {{ $this->subdomainUrl }} until your domain reaches us and answers over HTTPS.
            @endif
        </p>
    @endif

    {{-- Plan Note --}}
    <div class="rounded-xl border border-(--kn-border) bg-(--kn-surface-sunken) p-4">
        <p class="text-sm text-(--kn-muted)">
            <x-filament::icon icon="heroicon-o-light-bulb" class="inline h-4 w-4 align-[-2px]" />
            <strong>Custom domains are available on Growth and Pro plans.</strong>
            Your current plan: <span class="font-semibold capitalize">{{ tenant()->plan?->value ?? 'trial' }}</span>
        </p>
    </div>
</div>
