import { passwordValidation } from './forms.js';

export function editAccount() {
    const form = document.getElementById('accountForm');
    const inputs = form.querySelectorAll('input, select');
    const editButton = document.getElementById('editButton');
    const saveButton = document.getElementById('saveButton');
    const cancelButton = document.getElementById('cancelButton');
    const passwordField = document.getElementById('password-confirm-field');

    editButton.style.display = 'none';
    saveButton.style.display = 'inline-block';
    cancelButton.style.display = 'inline-block';
    passwordField.style.display = 'block';

    inputs.forEach(input => {
        input.removeAttribute('readonly');
    });

    cancelButton.addEventListener('click', () => {
        window.location.reload();
    });

    form.addEventListener('submit', (event) => {
        if (!passwordValidation(document.getElementById('password'), document.getElementById('confirm-password'))) {
            event.preventDefault();
        }
    });
}