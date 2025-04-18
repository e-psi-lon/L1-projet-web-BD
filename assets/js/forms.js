export function initializeForms() {
    // Add event listeners to form elements
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', (event) => {
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

export function showError(field, message) {
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

export function clearError(field) {
    // Remove error message if exists
    const error = field.parentNode.querySelector('.error-message');
    if (error) {
        error.remove();
    }

    // Reset field style
    field.style.borderColor = '';
}

export function passwordValidation(passwordField, confirmPasswordField) {
    const password = passwordField.value;
    const confirmPassword = confirmPasswordField.value;

    if (password !== confirmPassword) {
        showError(confirmPasswordField, 'Les mots de passe ne correspondent pas');
        showError(passwordField, 'Les mots de passe ne correspondent pas');
        return false;
    } else {
        clearError(confirmPasswordField);
        clearError(passwordField);
        return true;
    }
}