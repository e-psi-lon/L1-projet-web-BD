export function initializeSearchFeatures() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return;

    searchInput.addEventListener('input', () => {
        if (searchInput.value.length >= 3) {
            performLiveSearch(searchInput.value);
        } else if (searchInput.value.length === 0) {
            const resultsContainer = document.getElementById('search-results');
            if (resultsContainer) {
                resultsContainer.innerHTML = '';
            }
        }
    });
}

function performLiveSearch(query) {
    console.log("Querying for: ", query);
    fetch(`/api/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            console.log(data);
            const resultsContainer = document.getElementById('search-results');
            if (!resultsContainer) return;

            displaySearchResults(data, query, resultsContainer);
        })
        .catch(error => console.error('Erreur lors de la recherche', error));
}

function displaySearchResults(results, searchTerm, container) {
    // Empty the results container
    container.innerHTML = '';

    if (results.length === 0) {
        container.innerHTML = `
            <div class="alert alert-danger">
                <h2>Aucun résultat trouvé pour "${searchTerm}"</h2>
                <p>Essayez d'utiliser des termes de recherche différents ou plus généraux.</p>
            </div>`;
        return;
    }

    // Add a title to the results
    const titleElement = document.createElement('h2');
    titleElement.textContent = `Résultats de recherche pour "${searchTerm}"`;
    container.appendChild(titleElement);

    let currentBook = '';
    let currentAuthor = '';
    let bookElement = null;
    let chaptersContainer = null;

    results.forEach(result => {
        if (currentBook !== result.book_title || currentAuthor !== result.author_name) {
            // Start of a new book
            currentBook = result.book_title;
            currentAuthor = result.author_name;

            // Create a new book element
            bookElement = document.createElement('div');
            bookElement.className = 'search-result-book card';

            // Create the book header
            const bookHeader = document.createElement('div');
            bookHeader.className = 'book-header';

            const bookTitle = document.createElement('h3');
            const bookLink = document.createElement('a');
            bookLink.href = `/authors/${result.url_name}/books/${result.url_title}`;
            bookLink.textContent = result.book_title;
            bookTitle.appendChild(bookLink);

            const authorParagraph = document.createElement('p');
            const authorLink = document.createElement('a');
            authorLink.href = `/authors/${result.url_name}`;
            authorLink.textContent = result.author_name;
            authorParagraph.appendChild(document.createTextNode('par '));
            authorParagraph.appendChild(authorLink);

            bookHeader.appendChild(bookTitle);
            bookHeader.appendChild(authorParagraph);

            // Create a new chapters container
            chaptersContainer = document.createElement('div');
            chaptersContainer.className = 'book-chapters';

            // Combine elements into a single book element
            bookElement.appendChild(bookHeader);
            bookElement.appendChild(chaptersContainer);

            // Add the book element to the main container
            container.appendChild(bookElement);
        }

        // Create a new chapter element
        const chapterElement = document.createElement('div');
        chapterElement.className = 'search-result-chapter';

        // Create the book title
        const chapterTitle = document.createElement('h4');
        const chapterLink = document.createElement('a');
        chapterLink.href = `/authors/${result.url_name}/books/${result.url_title}/chapters/${result.chapter_number}`;
        chapterLink.textContent = result.chapter_title ? result.chapter_title : `Chapitre ${result.chapter_number}`;
        chapterTitle.appendChild(chapterLink);

        // Create the text preview
        const previewElement = document.createElement('div');
        previewElement.className = 'chapter-preview';
        previewElement.innerHTML = truncateText(result.content, 150, searchTerm);

        // Assemble the chapter element
        chapterElement.appendChild(chapterTitle);
        chapterElement.appendChild(previewElement);

        // Append the chapter element to the chapters container
        chaptersContainer.appendChild(chapterElement);
    });
}

function truncateText(text, length = 150, searchTerm = '') {
    // Remove any HTML tags
    text = text.replace(/<[^>]*>/g, '');

    if (searchTerm && searchTerm.trim() !== '') {
        const pos = text.toLowerCase().indexOf(searchTerm.toLowerCase());
        if (pos !== -1) {
            const start = Math.max(0, pos - length / 2);
            if (start > 0) {
                text = '...' + text.substring(start);
            }
        }
    }

    if (text.length > length) {
        text = text.substring(0, length) + '...';
    }

    if (searchTerm && searchTerm.trim() !== '') {
        const escapedSearchTerm = searchTerm.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        const regex = new RegExp(`(${escapedSearchTerm})`, 'gi');
        text = text.replace(regex, '<mark>$1</mark>');
    }

    return text;
}