document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide icons
    lucide.createIcons();
    
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    // Check for saved dark mode preference or respect OS preference
    if (localStorage.getItem('darkMode') === 'enabled' || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches && 
         localStorage.getItem('darkMode') !== 'disabled')) {
        document.body.classList.add('dark-mode');
        darkModeToggle.checked = true;
    }
    
    // Listen for toggle clicks
    darkModeToggle.addEventListener('change', () => {
        if (darkModeToggle.checked) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'disabled');
        }
    });
});