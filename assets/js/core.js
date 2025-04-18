import { initializeForms } from './forms.js';
import { initializeSearchFeatures } from './search.js';
import { initializeResponsiveMenu } from './ui.js';

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide icons
    try {
        lucide.createIcons();
    } catch (error) {
        console.error('Lucide icons could not be initialized:', error);
    }
    // Initialize interactive elements
    initializeForms();
    initializeSearchFeatures();
    initializeResponsiveMenu();
});