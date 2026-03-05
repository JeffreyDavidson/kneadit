<div class="text-center py-8">
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-6" style="background: linear-gradient(135deg, #d4f4dd, #a7f3d0);">
        <x-heroicon-o-check-circle class="w-12 h-12 text-emerald-600" />
    </div>

    <h2 class="text-2xl font-bold mb-2" style="color: #3d2314;">You're all set!</h2>
    <p class="text-lg mb-8" style="color: #6b4c3b;">
        Your bakery is ready to go. Here's what you can do next:
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-lg mx-auto">
        <a href="{{ url('/admin') }}"
           class="flex flex-col items-center p-4 rounded-xl border transition-all hover:shadow-md"
           style="border-color: #e8d0b0; background: #fdf8f2;">
            <x-heroicon-o-home class="w-8 h-8 mb-2" style="color: #6b4c3b;" />
            <span class="text-sm font-medium" style="color: #3d2314;">Dashboard</span>
        </a>

        <a href="{{ url('/admin/products') }}"
           class="flex flex-col items-center p-4 rounded-xl border transition-all hover:shadow-md"
           style="border-color: #e8d0b0; background: #fdf8f2;">
            <x-heroicon-o-plus-circle class="w-8 h-8 mb-2" style="color: #6b4c3b;" />
            <span class="text-sm font-medium" style="color: #3d2314;">Add Products</span>
        </a>

        <a href="{{ url('/admin/manage-settings') }}"
           class="flex flex-col items-center p-4 rounded-xl border transition-all hover:shadow-md"
           style="border-color: #e8d0b0; background: #fdf8f2;">
            <x-heroicon-o-cog-6-tooth class="w-8 h-8 mb-2" style="color: #6b4c3b;" />
            <span class="text-sm font-medium" style="color: #3d2314;">Settings</span>
        </a>
    </div>
</div>
