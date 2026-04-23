@php
    /** @var \App\Models\Inventory\Product $product */
    /** @var \App\Services\Settings\TenantSettings $settings */
    /** @var \App\Presenters\ProductLabelPresenter $label */
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Label — {{ $product->name }}</title>
    <style @cspnonce>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink: #1c1410;
            --muted: #6b5c4d;
            --hairline: #d4c8b2;
            --paper: #ffffff;
        }

        html, body {
            background: #f4ede0;
            color: var(--ink);
            font-family: 'Georgia', 'Times New Roman', serif;
            line-height: 1.5;
        }

        body {
            padding: 48px 24px;
            display: flex;
            justify-content: center;
        }

        .toolbar {
            position: fixed;
            top: 16px;
            right: 16px;
            display: flex;
            gap: 8px;
            z-index: 10;
        }
        .toolbar button,
        .toolbar a {
            font-family: system-ui, sans-serif;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 14px;
            border: 1px solid var(--hairline);
            background: var(--paper);
            color: var(--ink);
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }
        .toolbar button:hover,
        .toolbar a:hover { background: #faf4e8; }

        .label {
            width: 6.5in;
            max-width: 100%;
            min-height: 9in;
            background: var(--paper);
            padding: 48px;
            box-shadow: 0 20px 60px rgba(28, 20, 16, 0.15);
            border: 1px solid var(--hairline);
        }

        .brand-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--ink);
            margin-bottom: 32px;
        }
        .brand-name {
            font-family: 'Georgia', serif;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .brand-address {
            font-family: system-ui, sans-serif;
            font-size: 11px;
            color: var(--muted);
            text-align: right;
        }

        .product-name {
            font-family: 'Georgia', serif;
            font-size: 42px;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 32px;
        }

        .section-heading {
            font-family: system-ui, sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .ingredients {
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .allergen-statement {
            font-size: 15px;
            font-weight: 700;
            padding: 14px 16px;
            border: 2px solid var(--ink);
            background: #faf4e8;
            margin-bottom: 32px;
        }

        .meta {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
            font-family: system-ui, sans-serif;
            font-size: 11px;
            color: var(--muted);
            padding-top: 16px;
            border-top: 1px solid var(--hairline);
        }

        .meta strong {
            display: block;
            color: var(--ink);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .disclaimer {
            font-family: system-ui, sans-serif;
            font-size: 11px;
            font-style: italic;
            color: var(--muted);
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--hairline);
        }

        .no-data {
            font-style: italic;
            color: var(--muted);
            padding: 12px 0;
        }

        @media print {
            body { background: white; padding: 0; }
            .toolbar { display: none; }
            .label { box-shadow: none; border: 0; padding: 0.5in; }
            @page { size: letter; margin: 0.5in; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Print</button>
        <a href="javascript:window.close()">Close</a>
    </div>

    <div class="label">
        <div class="brand-row">
            <div class="brand-name">{{ $settings->store->name ?: 'Your Bakery' }}</div>
            @if ($settings->store->address)
                <div class="brand-address">{{ $settings->store->address }}</div>
            @endif
        </div>

        <h1 class="product-name">{{ $product->name }}</h1>

        <div class="section-heading">Ingredients</div>
        @if (count($label->ingredientNames()) > 0)
            <p class="ingredients">
                {{ implode(', ', $label->ingredientNames()) }}.
            </p>
        @else
            <p class="no-data">No recipe on file for this product. Add ingredients in the Recipes section.</p>
        @endif

        @if ($label->allergenStatement())
            <div class="allergen-statement">
                {{ $label->allergenStatement() }}
            </div>
        @elseif (count($label->ingredientNames()) > 0)
            <p class="no-data">
                No allergens tagged on this recipe's ingredients. Tag your pantry ingredients with their allergens to auto-generate a disclosure.
            </p>
        @endif

        <div class="meta">
            <div>
                <strong>Printed</strong>
                {{ now()->format('M j, Y') }}
            </div>
            @if ($product->price)
                <div>
                    <strong>Price</strong>
                    {{ $product->price->formatted() }}
                </div>
            @endif
        </div>

        <div class="disclaimer">
            Made in a home kitchen that is not subject to state inspection. Follow the food safety
            requirements of the cottage food laws in your state when selling or distributing.
        </div>
    </div>
</body>
</html>
