<section class="biscotto-contact-hero">
    <h1>Let's Talk</h1>
    <p>Questions, custom orders, or just want to chat about bread? We're all ears.</p>
    <div aria-hidden="true"><span></span>✦<span></span></div>
</section>

<section class="biscotto-contact-stage">
    <div class="biscotto-contact-envelope">
        <div id="contact-form" class="biscotto-contact-form">
            <h2>Send a Message</h2>
            <p>We'll get back to you as soon as we can.</p>

            @session('success')
                <div class="biscotto-contact-success" role="status">✓ <span>{{ $value }}</span></div>
            @endsession

            <form
                action="{{ route('contact.store') }}"
                method="POST"
                x-data="{ submitting: false }"
                @submit="submitting = true"
                data-test="contact-form"
            >
                @csrf
                <div class="biscotto-contact-row">
                    <div>
                        <label for="name">Name</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            placeholder="Your name"
                            required
                            data-test="contact-form-name"
                            @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                        />
                        @error('name')
                            <p id="name-error" class="biscotto-contact-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="you@email.com"
                            required
                            data-test="contact-form-email"
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                        />
                        @error('email')
                            <p id="email-error" class="biscotto-contact-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="subject">Subject</label>
                    <select
                        id="subject"
                        name="subject"
                        required
                        data-test="contact-form-subject"
                        @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror
                    >
                        <option value="">Choose a topic...</option>
                        @foreach (['General Question', 'Custom Order', 'Feedback', 'Other'] as $subject)
                            <option value="{{ $subject }}" @selected(old('subject') === $subject)>
                                {{ $subject }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject')
                        <p id="subject-error" class="biscotto-contact-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="message">Message</label>
                    <textarea
                        id="message"
                        name="message"
                        rows="6"
                        placeholder="What's on your mind..."
                        required
                        data-test="contact-form-message"
                        @error('message') aria-invalid="true" aria-describedby="message-error" @enderror
                    >{{ old('message') }}</textarea>
                    @error('message')
                        <p id="message-error" class="biscotto-contact-error">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" :disabled="submitting" data-test="contact-form-submit">
                    <span x-show="! submitting">Send Message ✦</span>
                    <span x-cloak x-show="submitting">Sending...</span>
                </button>
            </form>
        </div>

        <aside class="biscotto-contact-info">
            <h2>Get in Touch</h2>
            <dl>
                @if ($settings->store->address)
                    <div>
                        <dt>Location</dt>
                        <dd>{{ $settings->store->address }}</dd>
                    </div>
                @endif
                @if ($settings->store->email)
                    <div>
                        <dt>Email</dt>
                        <dd><a href="mailto:{{ $settings->store->email }}">{{ $settings->store->email }}</a></dd>
                    </div>
                @endif
                @if ($settings->store->phone)
                    <div>
                        <dt>Phone</dt>
                        <dd><a href="tel:{{ $settings->store->phone }}">{{ $settings->store->phone }}</a></dd>
                    </div>
                @endif
            </dl>
            <hr />
            <h2>Before You Order</h2>
            <dl>
                <div>
                    <dt>Lead Time</dt>
                    <dd>{{ $settings->orders->leadTimeHours }} hours in advance. Great sourdough can't be rushed!</dd>
                </div>
                <div>
                    <dt>Pickup & Delivery</dt>
                    <dd>Choose the option that works for you during checkout.</dd>
                </div>
            </dl>
            <blockquote>
                Every loaf is made with care, from feeding the starter to pulling it out of the oven.
            </blockquote>
        </aside>
    </div>
</section>

@if (! empty($settings->homepage->faqItems))
    <section id="faq" class="biscotto-faq" x-data="{ open: null }">
        <header>
            <h2>Frequently Asked <em>Questions</em></h2>
            <p>Everything you need to know before you order.</p>
        </header>
        <div class="biscotto-faq-list">
            @foreach ($settings->homepage->faqItems as $faq)
                <article :class="{ 'open': open === {{ $loop->index }} }">
                    <button
                        type="button"
                        @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}"
                        :aria-expanded="open === {{ $loop->index }}"
                        aria-controls="faq-answer-{{ $loop->index }}"
                    >
                        {{ $faq['question'] }}
                    </button>
                    <div id="faq-answer-{{ $loop->index }}" x-show="open === {{ $loop->index }}" x-collapse>
                        <p>{{ $faq['answer'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
        <p class="biscotto-faq-cta">
            Still have questions? <a href="#contact-form">Send us a message</a> and we'll be happy to help.
        </p>
    </section>
@endif
