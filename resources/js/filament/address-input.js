import { registerAddressInput } from '../address-input';

// The Filament panels run Livewire's own Alpine, so register before it starts.
document.addEventListener('alpine:init', () => registerAddressInput(window.Alpine));
