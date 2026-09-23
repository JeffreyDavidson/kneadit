{{-- PWA Install Prompt --}}
<div
    id="pwaInstall"
    class="bg-warm-900 text-warm-200 border-warm-500/15 fixed right-6 bottom-20 z-[9998] hidden max-w-[280px] rounded-2xl border px-5 py-4 text-[0.85rem] shadow-2xl"
>
    <div class="flex items-start gap-3">
        <div class="flex-1">
            <strong class="text-warm-500 text-[0.9rem]">Add to Home Screen</strong>
            <p class="text-warm-600 mt-1 mb-3 text-[0.8rem] leading-snug">
                Quick access to your favorite bakery — no app store needed.
            </p>
            <div class="flex gap-2">
                <button
                    id="pwaInstallBtn"
                    class="bg-warm-500 cursor-pointer rounded-full border-0 px-4 py-1.5 text-xs font-bold text-white"
                >
                    Install
                </button>
                <button
                    onclick="dismissPwa()"
                    class="text-warm-600 border-warm-700 cursor-pointer rounded-full border bg-transparent px-3 py-1.5 text-xs"
                >
                    Not now
                </button>
            </div>
        </div>
        <button
            onclick="dismissPwa()"
            class="text-warm-600 cursor-pointer border-0 bg-transparent p-0 text-[1.1rem] leading-none"
            aria-label="Dismiss install prompt"
        >
            &times;
        </button>
    </div>
</div>
<script @cspnonce>
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        if (!localStorage.getItem('pwaDismissed')) {
            document.getElementById('pwaInstall').style.display = 'block';
        }
    });
    document.getElementById('pwaInstallBtn').addEventListener('click', function () {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function () {
                deferredPrompt = null;
                document.getElementById('pwaInstall').style.display = 'none';
            });
        }
    });
    function dismissPwa() {
        document.getElementById('pwaInstall').style.display = 'none';
        localStorage.setItem('pwaDismissed', '1');
    }
</script>
