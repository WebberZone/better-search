document.addEventListener('DOMContentLoaded', function () {
    const searchForms = document.querySelectorAll('.search-form, form[role="search"]');

    searchForms.forEach(form => {
        const searchInput = form.querySelector('input[name="s"]');
        const submitButton = form.querySelector('input[type="submit"], button[type="submit"]');
        if (!searchInput) return;

        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'bsearch-autocomplete-results';
        resultsContainer.setAttribute('aria-live', 'polite');
        resultsContainer.setAttribute('role', 'listbox');
        resultsContainer.id = 'search-suggestions';
        searchInput.setAttribute('aria-autocomplete', 'list');
        searchInput.setAttribute('aria-controls', 'search-suggestions');

        // Move resultsContainer after the submit button in the DOM
        if (submitButton && submitButton.nextSibling) {
            submitButton.parentNode.insertBefore(resultsContainer, submitButton.nextSibling);
        } else {
            searchInput.parentNode.insertBefore(resultsContainer, searchInput.nextSibling);
        }

        let debounceTimer;
        let selectedIndex = -1;

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchTerm = this.value;
                if (searchTerm.length > 2) {
                    fetchResults(searchTerm, resultsContainer);
                } else {
                    clearResults(resultsContainer);
                }
            }, 300);
        });

        // Handle keyboard navigation from search input
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                clearResults(resultsContainer);
                return;
            }

            const items = resultsContainer.querySelectorAll('li');

            if (e.key === 'ArrowDown' && this.value.length > 2) {
                e.preventDefault();
                if (submitButton) {
                    submitButton.focus();
                } else if (items.length) {
                    const firstItem = items[0].querySelector('a');
                    if (firstItem) {
                        firstItem.focus();
                        selectedIndex = 0;
                        updateSelection(items);
                    }
                } else {
                    fetchResults(this.value, resultsContainer);
                }
            }
        });

        // Handle keyboard navigation from submit button
        if (submitButton) {
            submitButton.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    clearResults(resultsContainer);
                    searchInput.focus();
                    return;
                }

                const items = resultsContainer.querySelectorAll('li');

                if (e.key === 'ArrowDown' && items.length > 0) {
                    e.preventDefault();
                    const firstItem = items[0].querySelector('a');
                    if (firstItem) {
                        firstItem.focus();
                        selectedIndex = 0;
                        updateSelection(items);
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    searchInput.focus();
                }
            });

            // Ensure natural tab order by removing tabindex from result links
            resultsContainer.addEventListener('DOMNodeInserted', function (e) {
                if (e.target.tagName === 'A') {
                    e.target.removeAttribute('tabindex');
                }
            });
        }

        // Handle keyboard navigation in results
        resultsContainer.addEventListener('keydown', function (e) {
            const items = this.querySelectorAll('li');
            if (!items.length) return;

            const currentLink = document.activeElement;
            const currentItem = currentLink?.closest('li');
            const currentIndex = Array.from(items).indexOf(currentItem);

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (currentIndex < items.length - 1) {
                        items[currentIndex + 1].querySelector('a').focus();
                        selectedIndex = currentIndex + 1;
                        updateSelection(items);
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (currentIndex > 0) {
                        items[currentIndex - 1].querySelector('a').focus();
                        selectedIndex = currentIndex - 1;
                        updateSelection(items);
                    } else {
                        submitButton ? submitButton.focus() : searchInput.focus();
                        selectedIndex = -1;
                        updateSelection(items);
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    clearResults(resultsContainer);
                    searchInput.focus();
                    break;
            }
        });

        // Handle click outside
        document.addEventListener('click', function (event) {
            if (!form.contains(event.target) && !resultsContainer.contains(event.target)) {
                clearResults(resultsContainer);
            }
        });

        // Keep results visible on input focus
        searchInput.addEventListener('focus', function () {
            if (resultsContainer.innerHTML.trim() !== '' && this.value.length > 2) {
                resultsContainer.style.display = 'block';
            }
        });
    });

    function clearResults(resultsContainer) {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        selectedIndex = -1;
        const form = resultsContainer.closest('.search-form, form[role="search"]');
        if (form) {
            const input = form.querySelector('input[name="s"]');
            if (input) {
                input.removeAttribute('aria-activedescendant');
            }
        }
    }

    function updateSelection(items) {
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.setAttribute('aria-selected', 'true');
                item.classList.add('selected');
                const form = item.closest('.search-form, form[role="search"]');
                if (form) {
                    const input = form.querySelector('input[name="s"]');
                    if (input) {
                        input.setAttribute('aria-activedescendant', item.id);
                    }
                }
            } else {
                item.setAttribute('aria-selected', 'false');
                item.classList.remove('selected');
            }
        });
    }

    function fetchResults(searchTerm, resultsContainer) {
        fetch(bsearch_live_search.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Cache-Control': 'no-cache'
            },
            body: new URLSearchParams({
                action: 'bsearch_live_search',
                s: searchTerm
            }).toString()
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (results) {
                displayResults(results, resultsContainer);
            })
            .catch(function (error) {
                console.error('Error:', error);
                clearResults(resultsContainer);
            });
    }

    function displayResults(results, resultsContainer) {
        resultsContainer.innerHTML = '';
        if (results.length > 0) {
            resultsContainer.style.display = 'block';
            const ul = document.createElement('ul');
            ul.setAttribute('role', 'presentation');

            results.forEach((result, index) => {
                const li = document.createElement('li');
                li.setAttribute('role', 'option');
                li.setAttribute('aria-selected', 'false');
                li.id = `search-suggestion-${index}`;

                const a = document.createElement('a');
                a.href = result.link;
                a.textContent = result.title;
                a.addEventListener('focus', function () {
                    selectedIndex = index;
                    updateSelection(resultsContainer.querySelectorAll('li'));
                });

                li.appendChild(a);
                ul.appendChild(li);
            });

            resultsContainer.appendChild(ul);
            resultsContainer.setAttribute('aria-label', `${results.length} search suggestions found`);
        } else {
            clearResults(resultsContainer);
            resultsContainer.setAttribute('aria-label', 'No search suggestions found');
        }
    }
});