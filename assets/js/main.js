// Main JavaScript file for Corpus Digitale

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    try {
        lucide.createIcons();
    } catch (error) {
        console.error('Lucide icons could not be initialized:', error);
    }
    // Initialize interactive elements
    initializeForms();
    initializeDarkMode();
    initializeSearchFeatures();
    initializeResponsiveMenu();
});

function initializeForms() {
    // Add event listeners to form elements
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    showError(field, 'Ce champ est obligatoire');
                } else {
                    clearError(field);
                }
            });


            if (!valid) {
                event.preventDefault();
            }
        });
    });
}

function showError(field, message) {
    // Clear any existing error
    clearError(field);

    // Create error element
    const error = document.createElement('div');
    error.className = 'error-message';
    error.textContent = message;
    error.style.color = 'red';
    error.style.fontSize = '0.8rem';
    error.style.marginTop = '5px';

    // Insert error after field
    field.parentNode.insertBefore(error, field.nextSibling);

    // Highlight the field
    field.style.borderColor = 'red';
}

function clearError(field) {
    // Remove error message if exists
    const error = field.parentNode.querySelector('.error-message');
    if (error) {
        error.remove();
    }

    // Reset field style
    field.style.borderColor = '';
}

function initializeSearchFeatures() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        if (this.value.length >= 3) {
            performLiveSearch(this.value);
        }
    });
}

function performLiveSearch(query) {
    fetch(`/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsContainer = document.getElementById('search-results');
            if (!resultsContainer) return;

            resultsContainer.innerHTML = '';

            if (data.length === 0) {
                resultsContainer.innerHTML = '<p>Aucun résultat trouvé</p>';
                return;
            }

            data.forEach(item => {
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item';
                resultItem.innerHTML = `
                    <h3><a href="${item.url}">${item.title}</a></h3>
                    <p>${item.snippet}</p>
                `;
                resultsContainer.appendChild(resultItem);
            });
        })
        .catch(error => console.error('Erreur lors de la recherche', error));
}

function initializeDarkMode() {
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
}


function initializeResponsiveMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            
            // Optional: toggle aria-expanded attribute for accessibility
            const expanded = mainNav.classList.contains('active');
            menuToggle.setAttribute('aria-expanded', expanded);
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mainNav.contains(event.target) && !menuToggle.contains(event.target) && mainNav.classList.contains('active')) {
                mainNav.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
}

function editAccount() {
    const form = document.getElementById('accountForm');
    const inputs = form.querySelectorAll('input, select');
    const editButton = document.getElementById('editButton');
    const saveButton = document.getElementById('saveButton');
    const cancelButton = document.getElementById('cancelButton');

    editButton.style.display = 'none';
    saveButton.style.display = 'inline-block';
    cancelButton.style.display = 'inline-block';

    inputs.forEach(input => {
        input.removeAttribute('disabled');
    });

    cancelButton.addEventListener('click', function() {
        window.location.reload();
    });

}