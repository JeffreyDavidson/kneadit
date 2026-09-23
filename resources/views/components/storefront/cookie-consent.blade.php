{{-- Cookie Consent Banner --}}
<div
    id="cookieConsent"
    role="region"
    aria-label="Cookie consent"
    class="bg-warm-900 text-warm-200 fixed right-0 bottom-0 left-0 z-[9999] hidden px-6 py-4 text-[0.85rem] leading-relaxed shadow-2xl"
>
    <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-4">
        <p class="m-0 min-w-[200px] flex-1">
            We use cookies to improve your experience. By continuing to browse, you agree to our use of cookies.
            <a href="/privacy" class="text-warm-500 underline">Privacy Policy</a>
        </p>
        <button
            onclick="acceptCookies()"
            class="bg-warm-500 cursor-pointer rounded-full border-0 px-6 py-2 text-xs font-bold whitespace-nowrap text-white transition-colors"
        >
            Accept
        </button>
    </div>
</div>
<script @cspnonce>
    function acceptCookies() {
        document.getElementById('cookieConsent').style.display = 'none';
        localStorage.setItem('cookieConsent', '1');
    }
    if (!localStorage.getItem('cookieConsent')) {
        document.getElementById('cookieConsent').style.display = 'block';
    }
</script>
