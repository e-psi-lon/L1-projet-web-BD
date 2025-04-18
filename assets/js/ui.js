export function initializeResponsiveMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');

            // Optional: toggle aria-expanded attribute for accessibility
            const expanded = mainNav.classList.contains('active');
            menuToggle.setAttribute('aria-expanded', expanded);
        });

        // Close menu when clicking outside
        document.addEventListener('click', (event) => {
            if (!mainNav.contains(event.target) && !menuToggle.contains(event.target) && mainNav.classList.contains('active')) {
                mainNav.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
}