<div class="kn-wizard-progress" role="group" aria-label="Setup progress">
    <p class="kn-wizard-progress-label">
        <span class="kn-wizard-progress-count">Step {{ $number }} of {{ $total }}</span>
        <span aria-hidden="true">&middot;</span>
        <span class="kn-wizard-progress-name">{{ $name }}</span>
    </p>

    <div class="kn-wizard-progress-dots" aria-hidden="true">
        @foreach (range(1, $total) as $dot)
            <span
                @class([
                    'kn-wizard-progress-dot',
                    'kn-wizard-progress-dot-done' => $dot < $number,
                    'kn-wizard-progress-dot-current' => $dot === $number,
                ])
            ></span>
        @endforeach
    </div>
</div>
