<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Terms of Service | KneadIt</title>
    <meta
        name="description"
        content="KneadIt Terms of Service — the rules and guidelines for using our cottage food baker management platform."
    />
    <link rel="icon" href="/images/logo-icon.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="{{ asset('css/legal.css') }}" />
    @include('partials.fathom')
</head>
<body>
    <nav class="site-nav">
        <a href="/" class="nav-brand">KneadIt</a>
        <div class="nav-links">
            <a href="/#features">Features</a>
            <a href="/#pricing">Pricing</a>
            <a href="/#contact">Contact</a>
            <a href="/register" class="nav-cta">Get Started</a>
        </div>
    </nav>

    <div class="legal-hero">
        <h1>Terms of Service</h1>
        <p class="effective">Effective Date: March 11, 2026</p>
    </div>

    <div class="legal-wrap">
        <div class="legal-toc">
            <h2>Table of Contents</h2>
            <ol>
                <li><a href="#acceptance">Acceptance of Terms</a></li>
                <li><a href="#description">Service Description</a></li>
                <li><a href="#accounts">Account Registration</a></li>
                <li><a href="#billing">Subscription &amp; Billing</a></li>
                <li><a href="#free-trial">Free Trial</a></li>
                <li><a href="#acceptable-use">Acceptable Use</a></li>
                <li><a href="#prohibited">Prohibited Content &amp; Conduct</a></li>
                <li><a href="#ip">Intellectual Property</a></li>
                <li><a href="#third-party">Third-Party Services</a></li>
                <li><a href="#termination">Termination &amp; Data</a></li>
                <li><a href="#disclaimers">Disclaimer of Warranties</a></li>
                <li><a href="#liability">Limitation of Liability</a></li>
                <li><a href="#indemnification">Indemnification</a></li>
                <li><a href="#governing-law">Governing Law</a></li>
                <li><a href="#modifications">Modifications to Terms</a></li>
                <li><a href="#contact">Contact Information</a></li>
            </ol>
        </div>

        <div class="legal-section" id="acceptance">
            <h2>1. Acceptance of Terms</h2>
            <p>
                By accessing or using KneadIt ("the Service"), operated by Infinity Digital LLC ("we," "us," or "our"),
                you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, you may
                not use the Service.
            </p>
            <p>
                These Terms apply to all users of the Service, including bakery owners ("Bakers") who manage their
                businesses through KneadIt, and their customers ("End Customers") who place orders through Baker
                storefronts.
            </p>
        </div>

        <div class="legal-section" id="description">
            <h2>2. Service Description</h2>
            <p>
                KneadIt is a multi-tenant software-as-a-service (SaaS) platform designed for cottage food bakers. The
                Service provides tools for:
            </p>
            <ul>
                <li>Order management and tracking</li>
                <li>Product catalog and category management</li>
                <li>Customer relationship management</li>
                <li>Recipe management and ingredient costing</li>
                <li>Financial tracking and reporting</li>
                <li>Customizable online storefronts (hosted at <em>bakeryname</em>.getkneadit.app)</li>
                <li>Invoicing and payment tracking</li>
            </ul>
            <p>
                KneadIt does not directly process payments between Bakers and End Customers. Payment processing is
                handled through third-party services (such as PayPal) configured by each Baker independently.
            </p>
        </div>

        <div class="legal-section" id="accounts">
            <h2>3. Account Registration</h2>
            <p>
                To use KneadIt, you must create an account and provide accurate, complete, and current information. You
                are responsible for:
            </p>
            <ul>
                <li>Maintaining the confidentiality of your account credentials</li>
                <li>All activities that occur under your account</li>
                <li>Notifying us immediately of any unauthorized use of your account</li>
            </ul>
            <p>
                You must be at least 18 years old to create an account. By registering, you represent that you are
                legally able to enter into a binding contract.
            </p>
            <p>
                Each Baker account is assigned a unique subdomain (<em>bakeryname</em>.getkneadit.app). You are
                responsible for all content published on your subdomain.
            </p>
        </div>

        <div class="legal-section" id="billing">
            <h2>4. Subscription &amp; Billing</h2>
            <p>KneadIt offers three subscription plans:</p>
            <ul>
                <li><strong>Starter</strong> — $9/month</li>
                <li><strong>Growth</strong> — $19/month</li>
                <li><strong>Pro</strong> — $29/month</li>
            </ul>
            <p>
                All payments are processed securely through <strong>Stripe</strong>. By subscribing, you authorize us to
                charge your payment method on a recurring monthly basis until you cancel.
            </p>
            <h3>Cancellation</h3>
            <p>You may cancel your subscription at any time through your account settings. Upon cancellation:</p>
            <ul>
                <li>Your subscription remains active until the end of the current billing period</li>
                <li>No partial refunds are issued for unused time within a billing period</li>
                <li>Your storefront and data will remain accessible until the end of the billing period</li>
            </ul>
            <h3>Price Changes</h3>
            <p>
                We may change subscription prices with at least 30 days' notice. Founding member pricing, where
                applicable, is locked in for the duration of your continuous subscription.
            </p>
        </div>

        <div class="legal-section" id="free-trial">
            <h2>5. Free Trial</h2>
            <p>
                New accounts receive a 30-day free trial. No credit card is required to start a trial. At the end of the
                trial period, you must subscribe to a paid plan to continue using the Service. If you do not subscribe,
                your account will be suspended, though your data will be retained for a reasonable period to allow you
                to reactivate.
            </p>
        </div>

        <div class="legal-section" id="acceptable-use">
            <h2>6. Acceptable Use</h2>
            <p>
                You agree to use KneadIt only for lawful purposes related to managing a cottage food or home baking
                business. You must comply with all applicable local, state, and federal laws, including cottage food
                regulations in your jurisdiction.
            </p>
        </div>

        <div class="legal-section" id="prohibited">
            <h2>7. Prohibited Content &amp; Conduct</h2>
            <p>You may not use KneadIt to:</p>
            <ul>
                <li>Sell products that violate local cottage food or food safety regulations</li>
                <li>Post content that is defamatory, obscene, or infringes on any third party's rights</li>
                <li>Engage in fraudulent, misleading, or deceptive business practices</li>
                <li>Distribute malware, spam, or other harmful content</li>
                <li>Attempt to gain unauthorized access to the Service or other users' accounts</li>
                <li>Resell, sublicense, or redistribute the Service without our written consent</li>
                <li>Use the Service in any way that could damage, disable, or impair our infrastructure</li>
                <li>Scrape, crawl, or otherwise extract data from the Service in an automated manner</li>
            </ul>
            <p>
                We reserve the right to suspend or terminate accounts that violate these rules, with or without notice.
            </p>
        </div>

        <div class="legal-section" id="ip">
            <h2>8. Intellectual Property</h2>
            <h3>Your Content</h3>
            <p>
                You retain full ownership of all content you upload to KneadIt, including product photos, descriptions,
                recipes, and business information ("Your Content"). By using the Service, you grant us a limited,
                non-exclusive license to host, display, and transmit Your Content solely for the purpose of operating
                the Service.
            </p>
            <h3>Our Platform</h3>
            <p>
                KneadIt, including its design, code, features, branding, and documentation, is the intellectual property
                of Infinity Digital LLC. You may not copy, modify, distribute, or reverse-engineer any part of the
                platform.
            </p>
            <h3>Storefront Branding</h3>
            <p>
                While you may customize your storefront's appearance (colors, logo, content), the underlying platform
                technology and "Powered by KneadIt" attribution remain our property.
            </p>
        </div>

        <div class="legal-section" id="third-party">
            <h2>9. Third-Party Services</h2>
            <p>KneadIt integrates with third-party services to provide its functionality:</p>
            <ul>
                <li><strong>Stripe</strong> — for subscription payment processing</li>
                <li><strong>Resend</strong> — for transactional email delivery</li>
                <li><strong>Cloudflare</strong> — for content delivery and security</li>
            </ul>
            <p>
                Your use of these services is subject to their respective terms and privacy policies. We are not
                responsible for the actions, practices, or policies of any third-party service.
            </p>
            <p>
                Payment processing between Bakers and their End Customers (e.g., via PayPal) is configured and managed
                entirely by the Baker. KneadIt is not a party to those transactions.
            </p>
        </div>

        <div class="legal-section" id="termination">
            <h2>10. Termination &amp; Data</h2>
            <h3>By You</h3>
            <p>
                You may terminate your account at any time by canceling your subscription and contacting us to request
                account deletion.
            </p>
            <h3>By Us</h3>
            <p>
                We may suspend or terminate your account if you violate these Terms, fail to pay subscription fees, or
                if we discontinue the Service. We will provide reasonable notice when possible.
            </p>
            <h3>Effect of Termination</h3>
            <p>Upon termination:</p>
            <ul>
                <li>Your storefront will be taken offline</li>
                <li>
                    Your data (products, orders, customers, recipes) will be retained for 30 days, during which you may
                    request an export
                </li>
                <li>After the 30-day retention period, your data will be permanently deleted</li>
                <li>Any outstanding payment obligations survive termination</li>
            </ul>
        </div>

        <div class="legal-section" id="disclaimers">
            <h2>11. Disclaimer of Warranties</h2>
            <p>
                THE SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, WHETHER EXPRESS OR
                IMPLIED, INCLUDING BUT NOT LIMITED TO IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
                PURPOSE, AND NON-INFRINGEMENT.
            </p>
            <p>
                We do not warrant that the Service will be uninterrupted, error-free, or secure. We do not guarantee any
                specific results from using the Service.
            </p>
        </div>

        <div class="legal-section" id="liability">
            <h2>12. Limitation of Liability</h2>
            <p>
                TO THE MAXIMUM EXTENT PERMITTED BY LAW, INFINITY DIGITAL LLC SHALL NOT BE LIABLE FOR ANY INDIRECT,
                INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, OR ANY LOSS OF PROFITS, REVENUE, DATA, OR
                BUSINESS OPPORTUNITIES ARISING FROM YOUR USE OF THE SERVICE.
            </p>
            <p>
                OUR TOTAL LIABILITY FOR ANY CLAIMS RELATED TO THE SERVICE SHALL NOT EXCEED THE AMOUNT YOU PAID US IN THE
                TWELVE (12) MONTHS PRECEDING THE CLAIM.
            </p>
        </div>

        <div class="legal-section" id="indemnification">
            <h2>13. Indemnification</h2>
            <p>
                You agree to indemnify, defend, and hold harmless Infinity Digital LLC and its owner, Jeffrey Davidson,
                from any claims, damages, losses, or expenses (including reasonable attorney fees) arising from:
            </p>
            <ul>
                <li>Your use of the Service</li>
                <li>Your violation of these Terms</li>
                <li>Your violation of any applicable law or regulation</li>
                <li>Any content you publish through the Service</li>
                <li>Any dispute between you and your End Customers</li>
            </ul>
        </div>

        <div class="legal-section" id="governing-law">
            <h2>14. Governing Law</h2>
            <p>
                These Terms are governed by and construed in accordance with the laws of the State of Florida, United
                States, without regard to its conflict of law provisions. Any disputes arising from these Terms or the
                Service shall be resolved in the courts located in the State of Florida.
            </p>
        </div>

        <div class="legal-section" id="modifications">
            <h2>15. Modifications to Terms</h2>
            <p>
                We may update these Terms from time to time. When we make material changes, we will notify you via email
                or through the Service at least 30 days before the changes take effect. Your continued use of the
                Service after the effective date constitutes acceptance of the updated Terms.
            </p>
        </div>

        <div class="legal-section" id="contact">
            <h2>16. Contact Information</h2>
            <p>If you have questions about these Terms, please contact us:</p>
            <ul>
                <li><strong>Email:</strong> <a href="mailto:hello@getkneadit.app">hello@getkneadit.app</a></li>
                <li><strong>Company:</strong> Infinity Digital LLC</li>
                <li><strong>Location:</strong> Florida, USA</li>
            </ul>
        </div>
    </div>

    <footer>
        <div class="footer-brand">KneadIt</div>
        <div class="footer-tagline">Business management for cottage food bakers</div>
        <div class="footer-links">
            <a href="/privacy">Privacy</a>
            <a href="/terms">Terms</a>
            <a href="/#contact">Contact</a>
        </div>
        <div class="footer-made">© 2026 KneadIt · Created by Infinity Digital</div>
    </footer>
</body>
</html>
