<section class="faq" id="faq">
    <div class="faq-header">
        <h2 class="reveal">Common questions</h2>
    </div>
    <div class="faq-list">
        @foreach ([
            ['What is cottage food?', 'Cottage food laws let you sell homemade food products directly to consumers from your home kitchen, no commercial kitchen required. Rules vary by state, but KneadIt helps you stay compliant with built-in revenue tracking and state-specific limits.'],
            ['Do I need any special equipment?', 'Nope! KneadIt runs entirely in your browser on any phone, tablet, or laptop. No special hardware, no app to install. If you can check email, you can run KneadIt.'],
            ['How does the free trial work?', 'Every plan comes with a 30-day free trial, no credit card required. Pick any plan, try the full feature set, and only pay when you’re ready to commit.'],
            ['Can I use my own domain name?', 'Yes! Pro plan members can connect a custom domain to their storefront. Starter and Growth plans get a yourname.getkneadit.app subdomain that looks great too.'],
            ['What payment methods are supported?', 'KneadIt integrates with PayPal for professional invoicing with automatic payment reminders. You can also track cash and other payment methods. Your customers pay you directly. KneadIt never touches your money.'],
            ['When does KneadIt launch?', 'KneadIt is live! Sign up today and start your 30-day free trial. Early adopters lock in founding member pricing — those rates stay with you forever.'],
        ] as [$question, $answer])
            <div class="faq-item reveal reveal-d{{ $loop->iteration > 1 ? $loop->iteration - 1 : '' }}">
                <button class="faq-q" onclick="this.parentElement.classList.toggle('open')">
                    {{ $question }}<span class="faq-icon">+</span>
                </button>
                <div class="faq-a">
                    <div class="faq-a-inner">{{ $answer }}</div>
                </div>
            </div>
        @endforeach
    </div>
</section>
