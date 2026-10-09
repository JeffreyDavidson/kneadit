{{--
    The suggestions list of the shared `addressInput` Alpine component
    (resources/js/address-input.js), used inside <x-address-input> and the
    Filament AddressInput field. Google's terms ask for a "Google Maps"
    credit wherever its suggestions show without a map.
--}}
<div x-show="open" x-cloak class="kn-address-suggestions">
    <ul x-bind:id="listId" role="listbox" aria-label="Address suggestions">
        <template x-for="(suggestion, index) in suggestions" x-bind:key="index">
            <li
                role="option"
                x-bind:id="optionId(index)"
                x-bind:aria-selected="active === index"
                x-bind:class="{ 'kn-address-suggestion-active': active === index }"
                x-on:mousedown.prevent="pick(index)"
                x-on:mouseenter="active = index"
                class="kn-address-suggestion"
            >
                <span x-text="suggestion.main" class="kn-address-suggestion-main"></span>
                <span x-text="suggestion.secondary" class="kn-address-suggestion-secondary"></span>
            </li>
        </template>
    </ul>
    <p class="kn-address-attribution" translate="no">Google Maps</p>
</div>
