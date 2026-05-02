<x-filament-panels::page>
    <div class="space-y-10 max-w-4xl">

        {{-- ─── What are webhooks ───────────────────────────────────────── --}}
        <section class="space-y-3">
            <h2 class="text-xl font-semibold">What are webhooks?</h2>
            <p>
                Webhooks let KneadIt push order events to a URL of your choice in real time —
                no polling, no scheduled syncs. When an order is created, updated,
                cancelled, or delivered, we POST a JSON body to the webhook URL configured
                under <strong>Settings → Integrations</strong>. Use them to wire orders into
                Zapier, QuickBooks, your CRM, a custom dashboard, or anything that can
                accept an HTTP request.
            </p>
        </section>

        {{-- ─── Configure ───────────────────────────────────────────────── --}}
        <section class="space-y-3">
            <h2 class="text-xl font-semibold">Configuring</h2>
            <ol class="list-decimal list-inside space-y-1">
                <li>Go to <strong>Settings → Integrations</strong>.</li>
                <li>Paste your endpoint URL (HTTPS strongly recommended).</li>
                <li>Save. We auto-generate a 40-character signing secret on first save.</li>
                <li>Copy the secret into your endpoint code so you can verify signatures.</li>
                <li>Click <strong>Send Test</strong> to fire a synthetic <code>order.created</code>.</li>
                <li>View results in <strong>Operations → Webhook Deliveries</strong>.</li>
            </ol>
        </section>

        {{-- ─── Request shape ────────────────────────────────────────────── --}}
        <section class="space-y-3">
            <h2 class="text-xl font-semibold">Request format</h2>
            <p>Every dispatch is an HTTP <code>POST</code> with these headers:</p>
            <ul class="list-disc list-inside space-y-1">
                <li><code>Content-Type: application/json</code></li>
                <li><code>X-KneadIt-Event</code> — the event name (e.g. <code>order.created</code>)</li>
                <li><code>X-KneadIt-Signature</code> — HMAC-SHA256 of the request body, signed with your secret</li>
            </ul>
            <p>The body always wraps event data in this envelope:</p>
            <pre class="rounded bg-gray-100 dark:bg-gray-800 p-4 text-sm overflow-x-auto"><code>{
  "event": "order.created",
  "timestamp": "2026-05-01T14:00:00+00:00",
  "data": { ... event-specific payload ... }
}</code></pre>
        </section>

        {{-- ─── Event catalog ───────────────────────────────────────────── --}}
        <section class="space-y-6">
            <h2 class="text-xl font-semibold">Events</h2>

            <div class="space-y-2">
                <h3 class="font-semibold"><code>order.created</code></h3>
                <p class="text-sm">Fires when a new order is placed.</p>
                <pre class="rounded bg-gray-100 dark:bg-gray-800 p-4 text-sm overflow-x-auto"><code>{
  "order_number": "ORD-1042",
  "customer_name": "Maya Reyes",
  "customer_email": "maya@example.com",
  "total": 47.50,
  "status": "pending",
  "payment_status": "unpaid",
  "delivery_date": "2026-05-04",
  "items": [
    { "product": "Sourdough Loaf", "quantity": 2, "unit_price": 9.00 }
  ]
}</code></pre>
            </div>

            <div class="space-y-2">
                <h3 class="font-semibold"><code>order.updated</code></h3>
                <p class="text-sm">Fires on every status transition. Includes the previous status.</p>
                <pre class="rounded bg-gray-100 dark:bg-gray-800 p-4 text-sm overflow-x-auto"><code>{
  "order_number": "ORD-1042",
  "status": "confirmed",
  "previous_status": "pending",
  "payment_status": "paid",
  "total": 47.50
}</code></pre>
            </div>

            <div class="space-y-2">
                <h3 class="font-semibold"><code>order.cancelled</code></h3>
                <p class="text-sm">Fires once when an order moves to <code>cancelled</code>.
                <code>order.updated</code> also fires for the same transition — subscribe to
                whichever fits your handler.</p>
                <pre class="rounded bg-gray-100 dark:bg-gray-800 p-4 text-sm overflow-x-auto"><code>{
  "order_number": "ORD-1042",
  "previous_status": "baking",
  "customer_name": "Maya Reyes",
  "customer_email": "maya@example.com",
  "total": 47.50,
  "cancelled_at": "2026-05-02T10:14:00+00:00"
}</code></pre>
            </div>

            <div class="space-y-2">
                <h3 class="font-semibold"><code>order.delivered</code></h3>
                <p class="text-sm">Fires once when an order moves to <code>delivered</code>.</p>
                <pre class="rounded bg-gray-100 dark:bg-gray-800 p-4 text-sm overflow-x-auto"><code>{
  "order_number": "ORD-1042",
  "previous_status": "ready",
  "customer_name": "Maya Reyes",
  "customer_email": "maya@example.com",
  "total": 47.50,
  "delivered_at": "2026-05-04T15:32:00+00:00"
}</code></pre>
            </div>
        </section>

        {{-- ─── Signature verification ──────────────────────────────────── --}}
        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Verifying signatures</h2>
            <p>Compute HMAC-SHA256 over the raw request body using your secret.
            Compare your computed value to the <code>X-KneadIt-Signature</code> header.</p>

            <div class="space-y-2">
                <h3 class="font-semibold text-sm">PHP</h3>
                <pre class="rounded bg-gray-100 dark:bg-gray-800 p-4 text-sm overflow-x-auto"><code>$body = file_get_contents('php://input');
$expected = hash_hmac('sha256', $body, $_ENV['KNEADIT_WEBHOOK_SECRET']);
$received = $_SERVER['HTTP_X_KNEADIT_SIGNATURE'] ?? '';

if (! hash_equals($expected, $received)) {
    http_response_code(401);
    exit;
}</code></pre>
            </div>

            <div class="space-y-2">
                <h3 class="font-semibold text-sm">Node.js</h3>
                <pre class="rounded bg-gray-100 dark:bg-gray-800 p-4 text-sm overflow-x-auto"><code>import crypto from 'node:crypto';

const expected = crypto
  .createHmac('sha256', process.env.KNEADIT_WEBHOOK_SECRET)
  .update(rawBody)
  .digest('hex');

const received = req.headers['x-kneadit-signature'] || '';

if (!crypto.timingSafeEqual(Buffer.from(expected), Buffer.from(received))) {
  return res.status(401).end();
}</code></pre>
            </div>

            <div class="space-y-2">
                <h3 class="font-semibold text-sm">Python</h3>
                <pre class="rounded bg-gray-100 dark:bg-gray-800 p-4 text-sm overflow-x-auto"><code>import hmac, hashlib, os

expected = hmac.new(
    os.environ['KNEADIT_WEBHOOK_SECRET'].encode(),
    raw_body,
    hashlib.sha256,
).hexdigest()

received = request.headers.get('X-KneadIt-Signature', '')

if not hmac.compare_digest(expected, received):
    abort(401)</code></pre>
            </div>
        </section>

        {{-- ─── Retry behavior ────────────────────────────────────────────── --}}
        <section class="space-y-3">
            <h2 class="text-xl font-semibold">Retry behavior</h2>
            <p>If your endpoint returns a non-2xx status or the connection fails,
            we retry up to <strong>2 additional times</strong> with a 100ms backoff,
            then give up. Every attempt is recorded in
            <strong>Operations → Webhook Deliveries</strong>, including the response
            body (truncated to 2KB) and any error message.</p>
            <p>You can manually re-fire any past delivery using the
            <strong>Redeliver</strong> action on the deliveries page.</p>
        </section>

        {{-- ─── Best practices ──────────────────────────────────────────── --}}
        <section class="space-y-3">
            <h2 class="text-xl font-semibold">Best practices</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>Return <code>2xx</code> as fast as possible — do real work async.</li>
                <li>Idempotency: dedupe on <code>(order_number, event)</code>; we may resend on retry.</li>
                <li>Use HTTPS. Reject any request whose signature doesn't verify.</li>
                <li>Treat customer data (name, email) according to your privacy policy.</li>
                <li>Rotate the secret if you suspect it's been exposed —
                <strong>Settings → Integrations → Regenerate</strong>.</li>
            </ul>
        </section>
    </div>
</x-filament-panels::page>
