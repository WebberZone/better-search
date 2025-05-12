/**
 * Manages autocomplete search functionality for forms
 */
class SearchAutocomplete {
    static SELECTOR = '.search-form, form[role="search"]';
    static DEBOUNCE_DELAY = 300;

    static CACHE_TIMEOUT = 5 * 60 * 1000; // 5 minutes.

    constructor(form) {
        this.form = form;
        this.searchInput = form.querySelector('input[name="s"]');
        this.submitButton = form.querySelector('input[type="submit"], button[type="submit"]');
        this.selectedIndex = -1;
        this.debounceTimer = null;
        this.cache = new Map();

        if (!this.searchInput) return;

        this.initializeElements();
        this.bindEvents();
    }

    /**
     * Initializes DOM elements and sets up ARIA attributes
     */
    initializeElements() {
        // Create announcement region
        this.announceRegion = this.createAnnounceRegion();
        this.form.insertBefore(this.announceRegion, this.form.firstChild);

        // Create results container
        this.resultsContainer = this.createResultsContainer();
        this.insertResultsContainer();

        // Configure search input
        this.configureSearchInput();
    }

    /**
     * Creates announcement region for screen readers
     * @returns {HTMLDivElement}
     */
    createAnnounceRegion() {
        const region = document.createElement('div');
        region.className = 'bsearch-visually-hidden';
        region.setAttribute('aria-live', 'assertive');
        region.id = `announce-${this.generateId()}`;
        return region;
    }

    /**
     * Creates results container
     * @returns {HTMLDivElement}
     */
    createResultsContainer() {
        const container = document.createElement('div');
        container.className = 'bsearch-autocomplete-results';
        container.setAttribute('role', 'listbox');
        container.id = `search-suggestions-${this.generateId()}`;
        return container;
    }

    /**
     * Generates random ID for elements
     * @returns {string}
     */
    generateId() {
        return Math.random().toString(36).substring(2, 9);
    }

    /**
     * Inserts results container after submit button or input
     */
    insertResultsContainer() {
        const insertAfter = this.submitButton || this.searchInput;
        insertAfter.parentNode.insertBefore(this.resultsContainer, insertAfter.nextSibling);
    }

    /**
     * Configures search input attributes
     */
    configureSearchInput() {
        Object.entries({
            autocomplete: 'off',
            'aria-autocomplete': 'list',
            'aria-controls': this.resultsContainer.id,
            autocapitalize: 'off',
            spellcheck: 'false'
        }).forEach(([key, value]) => {
            this.searchInput.setAttribute(key, value);
        });
    }

    /**
     * Binds all event listeners
     */
    bindEvents() {
        this.form.addEventListener('submit', () => this.clearCache());
        this.searchInput.addEventListener('input', this.handleInput.bind(this));
        this.searchInput.addEventListener('keydown', this.handleInputKeydown.bind(this));
        this.searchInput.addEventListener('focus', this.handleInputFocus.bind(this));
        this.searchInput.addEventListener('blur', this.handleInputBlur.bind(this));

        if (this.submitButton) {
            this.submitButton.addEventListener('keydown', this.handleSubmitKeydown.bind(this));
        }

        this.resultsContainer.addEventListener('keydown', this.handleResultsKeydown.bind(this));
        this.resultsContainer.addEventListener('DOMNodeInserted', this.handleResultInsert.bind(this));

        document.addEventListener('click', this.handleDocumentClick.bind(this));
    }

    /**
     * Handles input changes with debouncing
     */
    handleInput() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            const searchTerm = this.searchInput.value.trim();

            if (searchTerm.length > 2) {
                this.announce(bsearch_live_search.strings.searching);
                this.fetchResults(searchTerm);
            } else {
                this.announce(searchTerm.length === 0 ? '' : bsearch_live_search.strings.min_chars);
                this.clearResults();
            }
        }, SearchAutocomplete.DEBOUNCE_DELAY);
    }

    /**
     * Handles keyboard navigation in input
     * @param {KeyboardEvent} event
     */
    handleInputKeydown(event) {
        const items = this.resultsContainer.querySelectorAll('li');

        switch (event.key) {
            case 'Escape':
                event.preventDefault();
                this.clearResults();
                this.announce(bsearch_live_search.strings.suggestions_closed);
                break;

            case 'ArrowDown':
                event.preventDefault();
                this.handleArrowDown(items);
                break;

            case 'ArrowUp':
                event.preventDefault();
                this.handleArrowUp(items);
                break;

            case 'Enter':
                this.handleEnter(items, event);
                break;
        }
    }

    /**
     * Handles ArrowDown navigation
     * @param {NodeList} items
     */
    handleArrowDown(items) {
        if (!items.length && this.searchInput.value.length > 2) {
            this.fetchResults(this.searchInput.value);
            return;
        }
        this.selectedIndex = items.length ?
            Math.min(this.selectedIndex + 1, items.length - 1) : 0;
        this.updateSelection(items);
    }

    /**
     * Handles ArrowUp navigation
     * @param {NodeList} items
     */
    handleArrowUp(items) {
        if (!items.length) return;
        this.selectedIndex = this.selectedIndex > 0 ?
            this.selectedIndex - 1 : items.length - 1;
        this.updateSelection(items);
    }

    /**
     * Handles Enter key
     * @param {NodeList} items
     * @param {KeyboardEvent} event
     */
    handleEnter(items, event) {
        if (items.length && this.selectedIndex >= 0) {
            event.preventDefault();
            const selectedItem = items[this.selectedIndex].querySelector('a');
            if (selectedItem?.href) {
                this.announce(bsearch_live_search.strings.navigating_to.replace('%s', selectedItem.textContent));
                window.location.href = selectedItem.href;
            }
        } else {
            this.announce(bsearch_live_search.strings.submitting_search);
            this.form.submit();
        }
    }

    /**
     * Handles submit button keyboard events
     * @param {KeyboardEvent} event
     */
    handleSubmitKeydown(event) {
        const items = this.resultsContainer.querySelectorAll('li');

        switch (event.key) {
            case 'Escape':
                event.preventDefault();
                this.clearResults();
                this.searchInput.focus();
                this.announce(bsearch_live_search.strings.suggestions_closed);
                break;

            case 'ArrowDown':
                if (!items.length) return;
                event.preventDefault();
                this.selectedIndex = 0;
                this.updateSelection(items);
                break;

            case 'ArrowUp':
                event.preventDefault();
                this.searchInput.focus();
                this.announce(bsearch_live_search.strings.back_to_input);
                break;
        }
    }

    /**
     * Handles results container keyboard events
     * @param {KeyboardEvent} event
     */
    handleResultsKeydown(event) {
        const items = this.resultsContainer.querySelectorAll('li');
        if (!items.length) return;

        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                this.updateSelection(items);
                break;

            case 'ArrowUp':
                event.preventDefault();
                this.handleResultsArrowUp(items);
                break;

            case 'Escape':
                event.preventDefault();
                this.clearResults();
                this.searchInput.focus();
                this.announce(bsearch_live_search.strings.suggestions_closed);
                break;

            case 'Enter':
                event.preventDefault();
                this.handleResultsEnter(items);
                break;
        }
    }

    /**
     * Handles ArrowUp in results
     * @param {NodeList} items
     */
    handleResultsArrowUp(items) {
        if (this.selectedIndex === 0) {
            (this.submitButton || this.searchInput).focus();
            this.selectedIndex = -1;
            this.announce(bsearch_live_search.strings.back_to_search);
        } else {
            this.selectedIndex--;
            this.updateSelection(items);
        }
    }

    /**
     * Handles Enter in results
     * @param {NodeList} items
     */
    handleResultsEnter(items) {
        if (this.selectedIndex >= 0 && this.selectedIndex < items.length) {
            const selectedItem = items[this.selectedIndex].querySelector('a');
            if (selectedItem?.href) {
                this.announce(bsearch_live_search.strings.navigating_to.replace('%s', selectedItem.textContent));
                window.location.href = selectedItem.href;
            } else {
                this.searchInput.value = items[this.selectedIndex].textContent;
                this.form.submit();
            }
        }
    }

    /**
     * Handles new result insertion
     * @param {Event} event
     */
    handleResultInsert(event) {
        if (event.target.tagName === 'A') {
            event.target.removeAttribute('tabindex');
        }
    }

    /**
     * Handles document clicks for closing suggestions
     * @param {MouseEvent} event
     */
    handleDocumentClick(event) {
        if (!this.form.contains(event.target) && !this.resultsContainer.contains(event.target)) {
            this.clearResults();
        }
    }

    /**
     * Handles input focus
     */
    handleInputFocus() {
        if (this.resultsContainer.innerHTML.trim() && this.searchInput.value.length > 2) {
            this.resultsContainer.style.display = 'block';
        }
    }

    /**
     * Handles input blur
     */
    handleInputBlur() {
        setTimeout(() => {
            this.clearResults();
            this.announce('Search suggestions closed');
        }, 100);
    }

    /**
     * Updates screen reader announcements
     * @param {string} message
     */
    announce(message) {
        this.announceRegion.textContent = message;
        console.log(`Announced: ${message}`);
    }

    /**
     * Clears search results
     */
    clearResults() {
        this.resultsContainer.innerHTML = '';
        this.resultsContainer.style.display = 'none';
        this.selectedIndex = -1;
        this.searchInput.removeAttribute('aria-activedescendant');
        this.announceRegion.textContent = '';
    }

    /**
     * Updates selection state
     * @param {NodeList} items
     */
    updateSelection(items) {
        items.forEach(item => {
            item.classList.remove('bsearch-selected');
            item.setAttribute('aria-selected', 'false');
        });

        const selectedItem = items[this.selectedIndex];
        if (selectedItem) {
            selectedItem.classList.add('bsearch-selected');
            selectedItem.setAttribute('aria-selected', 'true');
            selectedItem.scrollIntoView({ block: 'nearest' });
            this.searchInput.setAttribute('aria-activedescendant', selectedItem.id);
            this.announce(selectedItem.textContent);
        }
    }

    /**
     * Gets cached results if available and not expired
     * @param {string} searchTerm
     * @returns {Array|null}
     */
    getCachedResults(searchTerm) {
        const cached = this.cache.get(searchTerm);
        if (!cached) return null;

        const now = Date.now();
        if (now - cached.timestamp > SearchAutocomplete.CACHE_TIMEOUT) {
            this.cache.delete(searchTerm);
            return null;
        }

        return cached.results;
    }

    /**
     * Caches search results
     * @param {string} searchTerm
     * @param {Array} results
     */
    cacheResults(searchTerm, results) {
        // Limit cache size to prevent memory issues
        if (this.cache.size > 50) {
            const oldestKey = this.cache.keys().next().value;
            this.cache.delete(oldestKey);
        }

        this.cache.set(searchTerm, {
            results,
            timestamp: Date.now()
        });
    }

    /**
     * Clears the results cache
     */
    clearCache() {
        this.cache.clear();
    }

    /**
     * Fetches search results
     * @param {string} searchTerm
     */
    async fetchResults(searchTerm) {
        try {
            // Check cache first
            const cachedResults = this.getCachedResults(searchTerm);
            if (cachedResults) {
                this.displayResults(cachedResults);
                return;
            }

            const response = await fetch(bsearch_live_search.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Cache-Control': 'no-cache'
                },
                body: new URLSearchParams({
                    action: 'bsearch_live_search',
                    s: searchTerm
                }).toString()
            });

            const results = await response.json();
            this.cacheResults(searchTerm, results);
            this.displayResults(results);
        } catch (error) {
            console.error('Error:', error);
            this.clearResults();
            this.announce(bsearch_live_search.strings.error_loading);
        }
    }

    /**
     * Displays search results
     * @param {Array} results
     */
    displayResults(results) {
        this.resultsContainer.innerHTML = '';

        if (!results.length) {
            this.announce(bsearch_live_search.strings.no_suggestions);
            return;
        }

        const ul = document.createElement('ul');
        ul.setAttribute('role', 'listbox');

        results.forEach((result, index) => {
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.setAttribute('aria-selected', 'false');
            li.id = `search-suggestion-${index}`;

            const a = document.createElement('a');
            a.href = result.link;
            a.textContent = result.title;
            li.appendChild(a);
            ul.appendChild(li);
        });

        this.resultsContainer.appendChild(ul);
        this.resultsContainer.style.display = 'block';
        this.announce(bsearch_live_search.strings.suggestions_found.replace('%d', results.length));
    }
}

/**
 * Initializes search autocomplete for all matching forms
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll(SearchAutocomplete.SELECTOR)
        .forEach(form => new SearchAutocomplete(form));
});