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

<div class="mb-2 space-y-6">
    {{-- Google search result preview --}}
    <div>
        <div class="text-cinnamon mb-3 text-[0.7rem] font-semibold tracking-[0.08em] uppercase">
            Google search preview
        </div>
        <div class="border-honey/10 rounded-lg border bg-white p-5">
            <div class="mb-1 flex items-center gap-2 text-[0.75rem] text-gray-500">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-[0.6rem] font-bold text-gray-700">K</span>
                <span>KneadIt</span>
                <span class="text-gray-400">·</span>
                <span class="truncate">{{ $fullUrl }}</span>
            </div>
            <div class="mb-2 text-[1.15rem] leading-tight font-normal text-[#1a0dab]">{{ $displayTitle }}</div>
            <div class="text-[0.82rem] leading-snug text-gray-700">
                {{ \Illuminate\Support\Str::limit($description, 160, '…') }}
            </div>
        </div>
    </div>

    {{-- Character meters --}}
    <div class="space-y-5">
        <div>
            <div class="mb-2 flex items-baseline justify-between">
                <span class="text-cinnamon text-[0.7rem] font-semibold tracking-[0.08em] uppercase">Meta title</span>
                <span class="{{ $titleTone }} text-[0.75rem] font-semibold tabular-nums">{{ $titleLen }} / 60</span>
            </div>
            <div class="bg-espresso h-1.5 overflow-hidden rounded-full">
                <div
                    class="h-full rounded-full transition-all
                    @if ($titleLen === 0) bg-cinnamon
                    @elseif ($titleLen < 40) bg-amber-500
                    @elseif ($titleLen <= 60) bg-emerald-500
                    @elseif ($titleLen <= 70) bg-amber-500
                    @else bg-red-500
                    @endif"
                    style="width: {{ min(100, ($titleLen / 60) * 100) }}%;"
                ></div>
            </div>
            <div class="{{ $titleTone }} text-[0.7rem] mt-2">{{ $titleHint }}</div>
        </div>

        <div>
            <div class="mb-2 flex items-baseline justify-between">
                <span class="text-cinnamon text-[0.7rem] font-semibold tracking-[0.08em] uppercase">Meta description</span>
                <span class="{{ $descTone }} text-[0.75rem] font-semibold tabular-nums">{{ $descLen }} / 160</span>
            </div>
            <div class="bg-espresso h-1.5 overflow-hidden rounded-full">
                <div
                    class="h-full rounded-full transition-all
                    @if ($descLen === 0) bg-cinnamon
                    @elseif ($descLen < 120) bg-amber-500
                    @elseif ($descLen <= 160) bg-emerald-500
                    @else bg-red-500
                    @endif"
                    style="width: {{ min(100, ($descLen / 160) * 100) }}%;"
                ></div>
            </div>
            <div class="{{ $descTone }} text-[0.7rem] mt-2">{{ $descHint }}</div>
        </div>
    </div>

    {{-- URL preview --}}
    <div>
        <div class="text-cinnamon mb-2 text-[0.7rem] font-semibold tracking-[0.08em] uppercase">Post URL</div>
        <div class="bg-espresso border-honey/10 text-parchment rounded-lg border px-3 py-2.5 font-mono text-[0.8rem] break-all">
            {{ $fullUrl }}
        </div>
    </div>
</div>
