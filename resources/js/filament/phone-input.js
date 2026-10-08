import { registerPhoneInput } from '../phone-input';

// The Filament panels run Livewire's own Alpine, so register before it starts.
document.addEventListener('alpine:init', () => registerPhoneInput(window.Alpine));
