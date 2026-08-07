<script @cspnonce>
    document.addEventListener('livewire:navigating', () => {
        const sidebar = document.querySelector('.fi-sidebar-nav');
        if (sidebar) window.__sidebarScroll = sidebar.scrollTop;
    });
    document.addEventListener('livewire:navigated', () => {
        const sidebar = document.querySelector('.fi-sidebar-nav');
        if (sidebar && window.__sidebarScroll) sidebar.scrollTop = window.__sidebarScroll;
    });
    // Disable 1Password/autofill on all Filament form fields
    function disable1Password() {
        document
            .querySelectorAll('.fi-fo-field-wrp input, .fi-fo-field-wrp textarea, .fi-fo-field-wrp select')
            .forEach((el) => {
                el.setAttribute('autocomplete', 'off');
                el.setAttribute('data-1p-ignore', '');
                el.setAttribute('data-lpignore', 'true');
                el.setAttribute('data-form-type', 'other');
            });
    }
    document.addEventListener('livewire:navigated', disable1Password);
    document.addEventListener('livewire:morph', disable1Password);
    setTimeout(disable1Password, 500);
</script>
