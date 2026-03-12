@if(config('services.fathom.site_id'))
<script src="https://cdn.usefathom.com/script.js" data-site="{{ config('services.fathom.site_id') }}" defer></script>
@endif
