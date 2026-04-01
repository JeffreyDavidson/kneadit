@props([
    'name',
    'textSize' => 'text-5xl',
])

<div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, var(--warm-700), var(--warm-800));">
    <span class="{{ $textSize }} font-display font-bold" style="color: var(--warm-600); opacity: 0.3;">{{ strtoupper(substr($name, 0, 1)) }}</span>
</div>
