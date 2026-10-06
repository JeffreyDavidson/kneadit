@php
    $record = $getRecord();
    $title = $get('meta_title') ?: $get('title') ?: 'Post title goes here';
    $description = $get('meta_description') ?: $get('excerpt') ?: 'Your meta description will appear here. Write something compelling that summarizes the post — Google and social platforms use this when showing your page.';
    $slug = $get('slug') ?: 'post-slug';
    $fullUrl = url('/blog/'.$slug);

    $displayTitle = \Illuminate\Support\Str::limit($title, 60, '…');

    $titleLen = mb_strlen($title);
    $titleTone = match (true) {
        $titleLen === 0 => 'text-(--kn-muted)',
        $titleLen < 40 => 'text-(--kn-warning)',
        $titleLen <= 60 => 'text-(--kn-success)',
        $titleLen <= 70 => 'text-(--kn-warning)',
        default => 'text-(--kn-danger)',
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
        $descLen === 0 => 'text-(--kn-muted)',
        $descLen < 120 => 'text-(--kn-warning)',
        $descLen <= 160 => 'text-(--kn-success)',
        default => 'text-(--kn-danger)',
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
        <div class="mb-3 text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase">
            Google search preview
        </div>
        <div class="rounded-lg border border-[#dadce0] bg-white p-5">
            <div class="mb-1 flex items-center gap-2 text-[0.75rem] text-[#5f6368]">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#f1f3f4] text-[0.6rem] font-bold text-[#3c4043]">K</span>
                <span>KneadIt</span>
                <span class="text-[#5f6368]">·</span>
                <span class="truncate">{{ $fullUrl }}</span>
            </div>
            <div class="mb-2 text-[1.15rem] leading-tight font-normal text-[#1a0dab]">{{ $displayTitle }}</div>
            <div class="text-[0.82rem] leading-snug text-[#3c4043]">
                {{ \Illuminate\Support\Str::limit($description, 160, '…') }}
            </div>
        </div>
    </div>

    {{-- Character meters --}}
    <div class="space-y-5">
        <div>
            <div class="mb-2 flex items-baseline justify-between">
                <span class="text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase">Meta title</span>
                <span class="{{ $titleTone }} text-[0.75rem] font-semibold tabular-nums">{{ $titleLen }} / 60</span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-(--kn-surface-sunken)">
                <div
                    class="h-full rounded-full transition-all
                    @if ($titleLen === 0) bg-(--kn-muted)
                    @elseif ($titleLen < 40) bg-(--kn-warning)
                    @elseif ($titleLen <= 60) bg-(--kn-success)
                    @elseif ($titleLen <= 70) bg-(--kn-warning)
                    @else bg-(--kn-danger)
                    @endif"
                    style="width: {{ min(100, ($titleLen / 60) * 100) }}%;"
                ></div>
            </div>
            <div class="{{ $titleTone }} text-[0.7rem] mt-2">{{ $titleHint }}</div>
        </div>

        <div>
            <div class="mb-2 flex items-baseline justify-between">
                <span class="text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase">Meta description</span>
                <span class="{{ $descTone }} text-[0.75rem] font-semibold tabular-nums">{{ $descLen }} / 160</span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-(--kn-surface-sunken)">
                <div
                    class="h-full rounded-full transition-all
                    @if ($descLen === 0) bg-(--kn-muted)
                    @elseif ($descLen < 120) bg-(--kn-warning)
                    @elseif ($descLen <= 160) bg-(--kn-success)
                    @else bg-(--kn-danger)
                    @endif"
                    style="width: {{ min(100, ($descLen / 160) * 100) }}%;"
                ></div>
            </div>
            <div class="{{ $descTone }} text-[0.7rem] mt-2">{{ $descHint }}</div>
        </div>
    </div>

    {{-- URL preview --}}
    <div>
        <div class="mb-2 text-[0.7rem] font-semibold tracking-[0.08em] text-(--kn-muted) uppercase">Post URL</div>
        <div class="rounded-lg border border-(--kn-border) bg-(--kn-surface-sunken) px-3 py-2.5 font-mono text-[0.8rem] break-all text-(--kn-ink)">
            {{ $fullUrl }}
        </div>
    </div>
</div>
