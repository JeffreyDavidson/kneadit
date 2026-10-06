@php
    /** @var array<string, string> $titles */
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Email previews · KneadIt</title>
    <style @cspnonce>
        body {
            margin: 0;
            padding: 32px 16px;
            background: #faf6ef;
            color: #1c1410;
            font-family: system-ui, sans-serif;
        }
        main {
            max-width: 640px;
            margin: 0 auto;
        }
        h1 {
            font-size: 1.5rem;
            margin: 0 0 4px;
        }
        p {
            color: #6b5b4e;
            margin: 0 0 24px;
        }
        ul {
            list-style: none;
            margin: 0;
            padding: 0;
            background: #fff;
            border: 1px solid #eadfcf;
            border-radius: 8px;
        }
        li {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 16px;
            border-top: 1px solid #eadfcf;
        }
        li:first-child {
            border-top: 0;
        }
        a {
            color: #1c1410;
        }
        .text {
            color: #6b5b4e;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <main>
        <h1>Email previews</h1>
        <p>KneadIt's own emails with sample data. Only available on your machine; nothing is sent.</p>
        <ul>
            @foreach ($titles as $slug => $title)
                <li>
                    <a href="{{ route('mailPreviews.show', ['mail' => $slug]) }}">{{ $title }}</a>
                    <a class="text" href="{{ route('mailPreviews.show', ['mail' => $slug, 'format' => 'text']) }}"
                        >Plain text</a>
                </li>
            @endforeach
        </ul>
    </main>
</body>
</html>
