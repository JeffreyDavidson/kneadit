<div class="text-center py-8">
    <div class="inline-flex items-center justify-center rounded-full mb-6" style="width: 80px; height: 80px; background: linear-gradient(135deg, #d4f4dd, #a7f3d0);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#059669" style="width: 48px; height: 48px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
    </div>

    <h2 class="text-2xl font-bold mb-2" style="color: #3d2314;">You're all set!</h2>
    <p class="text-lg mb-8" style="color: #6b4c3b;">
        Your bakery is ready to go. Here's what you can do next:
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-lg mx-auto">
        <a href="{{ url('/admin') }}"
           class="flex flex-col items-center p-4 rounded-xl border transition-all hover:shadow-md"
           style="border-color: #e8d0b0; background: #fdf8f2;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6b4c3b" style="width: 32px; height: 32px;" class="mb-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            <span class="text-sm font-medium" style="color: #3d2314;">Dashboard</span>
        </a>

        <a href="{{ url('/admin/products') }}"
           class="flex flex-col items-center p-4 rounded-xl border transition-all hover:shadow-md"
           style="border-color: #e8d0b0; background: #fdf8f2;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6b4c3b" style="width: 32px; height: 32px;" class="mb-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="text-sm font-medium" style="color: #3d2314;">Add Products</span>
        </a>

        <a href="{{ url('/admin/manage-settings') }}"
           class="flex flex-col items-center p-4 rounded-xl border transition-all hover:shadow-md"
           style="border-color: #e8d0b0; background: #fdf8f2;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6b4c3b" style="width: 32px; height: 32px;" class="mb-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <span class="text-sm font-medium" style="color: #3d2314;">Settings</span>
        </a>
    </div>
</div>
