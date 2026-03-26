Hi {{ $user->name }},

Your KneadIt free trial for {{ $storeName }} ends {{ $daysLeft === 1 ? 'tomorrow' : "in {$daysLeft} days" }}.

Subscribe now to keep your bakery running without interruption:
https://getkneadit.app/billing/plans
@if($daysLeft <= 3)

After your trial expires, your storefront will be paused until you subscribe.
@endif

Questions? Just reply to this email.

— The KneadIt Team
