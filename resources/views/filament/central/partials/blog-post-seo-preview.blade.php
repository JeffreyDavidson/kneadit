@php
    $record = $getRecord();
    $title = $get('meta_title') ?: $get('title') ?: 'Post title goes here';
    $description = $get('meta_description') ?: $get('excerpt') ?: 'Your meta description will appear here. Write something compelling that summarizes the post — Google and social platforms use this when showing your page.';
    $slug = $get('slug') ?: 'post-slug';
    $fullUrl = url('/blog/' . $slug);

    $displayTitle = \Illuminate\Support\Str::limit($title, 60, '…');

    $titleLen = mb_strlen($title);
    $titleTone = match (true) {
        $titleLen === 0 => 'text-cinnamon',
        $titleLen < 40 => 'text-amber-400',
        $titleLen <= 60 => 'text-emerald-400',
        $titleLen <= 70 => 'text-amber-400',
        default => 'text-red-400',
    };
    $titleHint = match (true) {
        $titleLen === 0 => 'Start typing…',
        $titleLen < 40 => 'A bit short — aim for 40-60',
        $titleLen <= 60 => 'Great length',
        $titleLen <= 70 => 'Getting long',
        default => 'Too long — Google will truncate',
    };

    $descLen = mb_strlen($description);
    $descTone = match (true) {
        $descLen === 0 => 'text-cinnamon',
        $descLen < 120 => 'text-amber-400',
        $descLen <= 160 => 'text-emerald-400',
        default => 'text-red-400',
    };
    $descHint = match (true) {
        $descLen === 0 => 'Start typing…',
        $descLen < 120 => 'A bit short — aim for 120-160',
        $descLen <= 160 => 'Great length',
        default => 'Too long — Google will truncate',
    };
@endphp

<div class="space-y-6 mb-2">
    {{-- Google search result preview --}}
    <div>
        <div class="text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-3">Google search preview</div>
        <div class="bg-white rounded-lg border border-honey/10 p-5">
            <div class="flex items-center gap-2 text-[0.75rem] text-gray-500 mb-1">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 text-[0.6rem] font-bold text-gray-700">K</span>
                <span>KneadIt</span>
                <span class="text-gray-400">·</span>
                <span class="truncate">{{ $fullUrl }}</span>
            </div>
            <div class="text-[1.15rem] leading-tight text-[#1a0dab] font-normal mb-2">{{ $displayTitle }}</div>
            <div class="text-[0.82rem] text-gray-700 leading-snug">{{ \Illuminate\Support\Str::limit($description, 160, '…') }}</div>
        </div>
    </div>

    {{-- Character meters --}}
    <div class="space-y-5">
        <div>
            <div class="flex items-baseline justify-between mb-2">
                <span class="text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold">Meta title</span>
                <span class="{{ $titleTone }} text-[0.75rem] font-semibold tabular-nums">{{ $titleLen }} / 60</span>
            </div>
            <div class="h-1.5 rounded-full bg-espresso overflow-hidden">
                <div class="h-full rounded-full transition-all
                    @if ($titleLen === 0) bg-cinnamon
                    @elseif ($titleLen < 40) bg-amber-500
                    @elseif ($titleLen <= 60) bg-emerald-500
                    @elseif ($titleLen <= 70) bg-amber-500
                    @else bg-red-500
                    @endif"
                    style="width: {{ min(100, ($titleLen / 60) * 100) }}%;"></div>
            </div>
            <div class="{{ $titleTone }} text-[0.7rem] mt-2">{{ $titleHint }}</div>
        </div>

        <div>
            <div class="flex items-baseline justify-between mb-2">
                <span class="text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold">Meta description</span>
                <span class="{{ $descTone }} text-[0.75rem] font-semibold tabular-nums">{{ $descLen }} / 160</span>
            </div>
            <div class="h-1.5 rounded-full bg-espresso overflow-hidden">
                <div class="h-full rounded-full transition-all
                    @if ($descLen === 0) bg-cinnamon
                    @elseif ($descLen < 120) bg-amber-500
                    @elseif ($descLen <= 160) bg-emerald-500
                    @else bg-red-500
                    @endif"
                    style="width: {{ min(100, ($descLen / 160) * 100) }}%;"></div>
            </div>
            <div class="{{ $descTone }} text-[0.7rem] mt-2">{{ $descHint }}</div>
        </div>
    </div>

    {{-- URL preview --}}
    <div>
        <div class="text-cinnamon text-[0.7rem] uppercase tracking-[0.08em] font-semibold mb-2">Post URL</div>
        <div class="bg-espresso border border-honey/10 rounded-lg px-3 py-2.5 text-parchment text-[0.8rem] font-mono break-all">
            {{ $fullUrl }}
        </div>
    </div>
</div>
