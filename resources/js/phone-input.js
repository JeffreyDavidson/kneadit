import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/styles';

/*
 * The one phone input, shared by the storefront (<x-phone-input>) and the
 * Filament admin (App\Filament\Forms\Components\PhoneInput).
 *
 * The person picks a country from the flag (the list shows each country's
 * dial code, the bakery's country first) and types; the number formats as
 * they type in that country's national format, e.g. (913) 387-7359. Typing a
 * number that starts with + and a country code switches the flag. `value`
 * always holds E.164 (+19133877359), or what was typed when it isn't a number
 * yet, so the server can say why.
 *
 * The dial code isn't shown beside the flag: intl-tel-input then formats in
 * the international style (913-387-7359) rather than the national one.
 *
 * `value` may be a Livewire entangle (Filament) or an x-modelable target
 * (storefront x-model), so outside changes are written back into the input.
 */
export function phoneInput({ value = '', country = 'us' } = {}) {
    // Kept out of Alpine's reactive data: the instance has private fields,
    // which a reactive proxy can't reach.
    let iti = null;

    return {
        value,

        init() {
            const input = this.$refs.input;

            iti = intlTelInput(input, {
                initialCountry: country || 'us',
                countryOrder: [country || 'us'],
                numberDisplayFormat: 'NATIONAL',
                separateDialCode: false,
                formatAsYouType: true,
                strictMode: true,
                containerClass: 'kn-phone-input',
                fullscreenParent: this.$root.closest('.fi-modal-window'),
                // Bundled by Vite as a separate chunk, served from this app (never a CDN).
                loadUtils: () => import('intl-tel-input/utils'),
            });

            input.addEventListener('input', () => this.sync());
            input.addEventListener('countrychange', () => this.sync());

            // The server already printed a stored number formatted; anything else
            // waits for the formatting utils (and a number typed meanwhile is kept).
            if (input.value === '' && this.value) {
                this.show(this.value);
            }

            iti.promise.then(() => {
                if (this.value) {
                    this.show(this.value);
                }
            });

            this.$watch('value', (next) => {
                if ((next || '') !== this.current()) {
                    this.show(next);
                }
            });
        },

        show(number) {
            iti.setNumber(number || '');
        },

        sync() {
            this.value = this.current();
        },

        current() {
            const typed = this.$refs.input.value.trim();

            if (typed === '') {
                return '';
            }

            // Until the formatting utils have loaded there is nothing to convert with.
            if (!intlTelInput.utils) {
                return typed;
            }

            return iti.getNumber('E164') || typed;
        },

        destroy() {
            iti?.destroy();
        },
    };
}

export function registerPhoneInput(Alpine) {
    Alpine.data('phoneInput', phoneInput);
}
