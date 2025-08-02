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
        this.observer = null;

        if (!this.searchInput) return;

        // Add class to identify forms with Better Search functionality
        this.form.classList.add('bsearch-enabled');

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
        // Always insert after the search input, regardless of form layout
        this.searchInput.parentNode.insertBefore(this.resultsContainer, this.searchInput.nextSibling);
        
        // Position results relative to search input for better vertical layout support
        this.positionResults();
    }

    /**
     * Positions the results container relative to the search input
     */
    positionResults() {
        const inputRect = this.searchInput.getBoundingClientRect();
        const formRect = this.form.getBoundingClientRect();
        
        // Calculate position relative to form container
        const top = inputRect.bottom - formRect.top + 4; // 4px margin
        const left = inputRect.left - formRect.left;
        const width = inputRect.width;
        
        // Apply positioning
        this.resultsContainer.style.position = 'absolute';
        this.resultsContainer.style.top = `${top}px`;
        this.resultsContainer.style.left = `${left}px`;
        this.resultsContainer.style.width = `${width}px`;
    }

    /**
     * Configures search input attributes
     */
    configureSearchInput() {
        Object.entries({
            autocomplete: 'off',
            'aria-autocomplete': 'list',
            'aria-controls': this.resultsContainer.id,
            'aria-expanded': 'false',
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
        this.searchInput.addEventListener('focusout', this.handleInputBlur.bind(this));

        if (this.submitButton) {
            this.submitButton.addEventListener('keydown', this.handleSubmitKeydown.bind(this));
        }

        this.resultsContainer.addEventListener('keydown', this.handleResultsKeydown.bind(this));
        this.resultsContainer.addEventListener('focusout', this.handleResultsBlur.bind(this));
        
        // Set up MutationObserver to watch for changes in the results container
        this.setupMutationObserver();

        document.addEventListener('click', this.handleDocumentClick.bind(this));
        
        // Reposition results on window resize
        window.addEventListener('resize', this.positionResults.bind(this));
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
        // If we're at the search input (selectedIndex = -1), move to the first item
        if (this.selectedIndex === -1) {
            this.selectedIndex = 0;
        } else {
            this.selectedIndex = items.length ?
                Math.min(this.selectedIndex + 1, items.length - 1) : 0;
        }
        this.updateSelection(items);
    }

    /**
     * Handles ArrowUp navigation
     * @param {NodeList} items
     */
    handleArrowUp(items) {
        if (!items.length) return;
        // If we're at the search input (selectedIndex = -1), move to the last item
        if (this.selectedIndex === -1) {
            this.selectedIndex = items.length - 1;
        } else {
            this.selectedIndex = this.selectedIndex > 0 ?
                this.selectedIndex - 1 : items.length - 1;
        }
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
            this.searchInput.focus();
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
     * Sets up MutationObserver to watch for changes in the results container
     */
    setupMutationObserver() {
        this.observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.tagName === 'A') {
                            // Only remove tabindex from links that are not meant to be focusable
                            // We preserve tabindex for proper keyboard navigation
                        } else if (node.querySelectorAll) {
                            // Don't remove tabindex from result links to preserve keyboard navigation
                            // Only remove from non-result elements if needed
                        }
                    });
                }
            });
        });
        
        this.observer.observe(this.resultsContainer, { childList: true, subtree: true });
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
            // Close results if focus moved outside the search functionality entirely
            const isSearchElement = document.activeElement?.closest(SearchAutocomplete.SELECTOR) !== null || 
                                  document.activeElement?.closest('.bsearch-autocomplete-results') !== null;
            if (!isSearchElement) {
                this.clearResults();
                this.announce(bsearch_live_search.strings.suggestions_closed);
            }
        }, 150);
    }

    /**
     * Handles results container blur
     */
    handleResultsBlur() {
        setTimeout(() => {
            // Close results if focus moved outside the search functionality entirely
            const isSearchElement = document.activeElement?.closest(SearchAutocomplete.SELECTOR) !== null || 
                                  document.activeElement?.closest('.bsearch-autocomplete-results') !== null;
            if (!isSearchElement) {
                this.clearResults();
                this.announce(bsearch_live_search.strings.suggestions_closed);
            }
        }, 150);
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
        // Disconnect and reconnect observer when clearing results to prevent memory leaks
        if (this.observer) {
            this.observer.disconnect();
            this.observer.observe(this.resultsContainer, { childList: true, subtree: true });
        }
        
        this.resultsContainer.innerHTML = '';
        this.resultsContainer.style.display = 'none';
        this.selectedIndex = -1;
        this.searchInput.removeAttribute('aria-activedescendant');
        this.searchInput.setAttribute('aria-expanded', 'false');
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
            
            // Move focus to the selected item for proper tab navigation continuation
            const link = selectedItem.querySelector('a');
            if (link) {
                link.focus();
            }
            
            // Announce with position information
            if (bsearch_live_search.strings.result_position) {
                const positionMessage = bsearch_live_search.strings.result_position
                    .replace('%1$d', this.selectedIndex + 1)
                    .replace('%2$d', items.length);
                this.announce(`${selectedItem.textContent}. ${positionMessage}`);
            } else {
                this.announce(selectedItem.textContent);
            }
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
                this.displayResults(cachedResults, cachedResults.length);
                return;
            }

            // Show loading spinner
            this.showLoadingSpinner();

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

            const responseJson = await response.json();
            // Handle both old format (direct array) and new format (wrapped object)
            const results = Array.isArray(responseJson) ? responseJson : responseJson.results;
            const total = Array.isArray(responseJson) ? responseJson.length : responseJson.total;
            this.cacheResults(searchTerm, results);
            this.displayResults(results, total);
        } catch (error) {
            console.error('Error:', error);
            this.clearResults();
            this.announce(bsearch_live_search.strings.error_loading);
        } finally {
            // Always hide loading spinner
            this.hideLoadingSpinner();
        }
    }

    /**
     * Displays search results
     * @param {Array} results
     * @param {number} total
     */
    displayResults(results, total = results.length) {
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
            // Preserve natural tab order for proper keyboard navigation
            // Add aria-label for better screen reader context
            if (bsearch_live_search.strings.view_post) {
                a.setAttribute('aria-label', bsearch_live_search.strings.view_post.replace('%s', result.title));
            }
            li.appendChild(a);
            ul.appendChild(li);
        });

        this.resultsContainer.appendChild(ul);
        this.resultsContainer.style.display = 'block';
        this.searchInput.setAttribute('aria-expanded', 'true');
        
        // Reposition results when showing them
        this.positionResults();
        
        this.announce(bsearch_live_search.strings.suggestions_found.replace('%d', total));
    }

    /**
     * Shows loading spinner in results container and search input
     */
    showLoadingSpinner() {
        // Clear any existing results
        this.resultsContainer.innerHTML = '';
        
        // Create loading spinner element
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'bsearch-loading-spinner';
        loadingDiv.innerHTML = '<div class="bsearch-spinner"></div>Searching...';
        
        // Show loading in results container
        this.resultsContainer.appendChild(loadingDiv);
        this.resultsContainer.style.display = 'block';
        
        // Reposition results when showing loading state
        this.positionResults();
        
        // Add loading class to search input for visual feedback
        this.searchInput.classList.add('bsearch-search-loading');
        
        // Announce to screen readers
        this.announce('Searching for results...');
    }

    /**
     * Hides loading spinner from results container and search input
     */
    hideLoadingSpinner() {
        // Remove loading class from search input
        this.searchInput.classList.remove('bsearch-search-loading');
        
        // Remove loading spinner from results (will be replaced by actual results or hidden)
        const loadingSpinner = this.resultsContainer.querySelector('.bsearch-loading-spinner');
        if (loadingSpinner) {
            loadingSpinner.remove();
        }
    }
}

/**
 * Initializes search autocomplete for all matching forms
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll(SearchAutocomplete.SELECTOR)
        .forEach(form => new SearchAutocomplete(form));
});