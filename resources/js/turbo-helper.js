export function setupTurboNavigation() {
    // Handle link clicks
    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link) return;

        // Skip for external links or links with specific attributes
        if (
            link.getAttribute('target') === '_blank' ||
            link.getAttribute('data-turbo') === 'false' ||
            link.href.startsWith('mailto:') ||
            link.href.startsWith('tel:')
        ) {
            return;
        }

        // Prevent default for internal links
        if (link.origin === window.location.origin) {
            event.preventDefault();
            Turbo.visit(link.href);
        }
    });

    // Handle form submissions
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (form.getAttribute('data-turbo') === 'false') return;

        event.preventDefault();
        Turbo.navigator.submitForm(form);
    });
}