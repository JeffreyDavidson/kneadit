/*
 * The one address input, shared by the storefront (<x-address-input>) and the
 * Filament admin (App\Filament\Forms\Components\AddressInput).
 *
 * As the person types, Google Places (API New) suggests addresses in the
 * bakery's country; picking one fills the box. With `parts` (Filament only:
 * the Livewire paths of the city, state and ZIP fields beside it) the box
 * gets the street line and the parts go to those fields; without, the box
 * gets Google's whole formatted address. Typing by hand always works, and
 * when Google can't be reached the box simply stays a text box.
 *
 * Google's script is loaded through its official loader the first time
 * someone types, never on page load. One session token covers the typing
 * and the pick (Google bills the session, not each keystroke); a new one
 * starts after each pick.
 *
 * `value` may be a Livewire entangle (Filament) or an x-modelable target
 * (storefront x-model).
 */
const MIN_LENGTH = 3;
const DEBOUNCE_MS = 200;

let places = null;

function loadPlaces(key) {
    places ??= import('@googlemaps/js-api-loader').then(({ setOptions, importLibrary }) => {
        setOptions({ key, v: 'weekly' });

        return importLibrary('places');
    });

    return places;
}

function component(components, type, short = false) {
    const found = components.find((part) => part.types.includes(type));

    return (short ? found?.shortText : found?.longText) ?? '';
}

/** Street line, city, state and ZIP from a place's address components. */
export function addressParts(components = []) {
    const street = [component(components, 'street_number'), component(components, 'route')]
        .filter(Boolean)
        .join(' ');
    const unit = component(components, 'subpremise');

    return {
        street: unit && street ? `${street} #${unit}` : street,
        city: component(components, 'locality') || component(components, 'postal_town'),
        state: component(components, 'administrative_area_level_1', true),
        zip: component(components, 'postal_code'),
    };
}

export function addressInput({ value = '', key = '', country = 'us', parts = {} } = {}) {
    // Kept out of Alpine's reactive data: Google's objects have private
    // fields, which a reactive proxy can't reach.
    let predictions = [];
    let token = null;
    let timer = null;
    let latest = 0;

    return {
        value,
        open: false,
        suggestions: [],
        active: -1,
        listId: '',

        init() {
            this.listId = this.$id('address-suggestions');
        },

        optionId(index) {
            return `${this.listId}-${index}`;
        },

        search() {
            clearTimeout(timer);

            const input = (this.value || '').trim();

            if (!key || input.length < MIN_LENGTH) {
                this.close();

                return;
            }

            timer = setTimeout(() => this.fetch(input), DEBOUNCE_MS);
        },

        async fetch(input) {
            const request = ++latest;
            let found = [];

            try {
                const { AutocompleteSessionToken, AutocompleteSuggestion } = await loadPlaces(key);
                token ??= new AutocompleteSessionToken();

                const { suggestions } = await AutocompleteSuggestion.fetchAutocompleteSuggestions({
                    input,
                    sessionToken: token,
                    includedRegionCodes: [country],
                });

                found = suggestions.map((suggestion) => suggestion.placePrediction).filter(Boolean);
            } catch {
                // Google unreachable or the key refused: the box stays a plain text box.
            }

            // A slower answer to an earlier keystroke doesn't replace a newer one.
            if (request !== latest) {
                return;
            }

            predictions = found;
            this.suggestions = predictions.map((prediction) => ({
                main: (prediction.mainText ?? prediction.text).toString(),
                secondary: prediction.secondaryText?.toString() ?? '',
            }));
            this.active = -1;
            this.open = this.suggestions.length > 0;
        },

        move(step) {
            if (!this.open) {
                return;
            }

            const count = this.suggestions.length;
            this.active = (this.active + step + count) % count;
        },

        choose(event) {
            if (!this.open || this.active < 0) {
                return;
            }

            event.preventDefault();
            this.pick(this.active);
        },

        async pick(index) {
            const prediction = predictions[index];
            this.close();

            if (!prediction) {
                return;
            }

            const place = prediction.toPlace();

            try {
                await place.fetchFields({ fields: ['addressComponents', 'formattedAddress'] });
            } catch {
                return;
            } finally {
                token = null;
            }

            this.fill(place);
        },

        fill(place) {
            const paths = Object.entries(parts);

            if (paths.length === 0) {
                this.value = place.formattedAddress ?? this.value;

                return;
            }

            const found = addressParts(place.addressComponents ?? []);
            this.value = found.street || this.value;

            paths.forEach(([part, path]) => this.$wire.$set(path, found[part], false));
        },

        close() {
            this.open = false;
            this.active = -1;
        },

        escape(event) {
            if (!this.open) {
                return;
            }

            // In a Filament modal, Escape would otherwise close the modal too.
            event.stopPropagation();
            this.close();
        },
    };
}

export function registerAddressInput(Alpine) {
    Alpine.data('addressInput', addressInput);
}
