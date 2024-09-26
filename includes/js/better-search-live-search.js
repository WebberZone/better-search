// Save this as js/better-search-live-search.js in your plugin directory

document.addEventListener('DOMContentLoaded', function () {
    const searchForms = document.querySelectorAll('.search-form, form[role="search"]');

    searchForms.forEach(form => {
        const searchInput = form.querySelector('input[name="s"]');
        if (!searchInput) return;

        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'bsearch-autocomplete-results';
        searchInput.parentNode.insertBefore(resultsContainer, searchInput.nextSibling);

        let debounceTimer;

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchTerm = this.value;
                if (searchTerm.length > 2) {
                    fetchResults(searchTerm, resultsContainer);
                } else {
                    resultsContainer.innerHTML = '';
                    resultsContainer.style.display = 'none'; // Hide the container when input is less than 3 characters
                }
            }, 300);
        });

        // Hide autocomplete results when clicking outside the input field or results container
        document.addEventListener('click', function (event) {
            if (!form.contains(event.target) && !resultsContainer.contains(event.target)) {
                resultsContainer.style.display = 'none'; // Hide the results container
            }
        });

        // Keep the container open if clicking inside it or on the input
        searchInput.addEventListener('focus', function () {
            if (resultsContainer.innerHTML.trim() !== '') {
                resultsContainer.style.display = 'block';
            }
        });
    });

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
            });
    }

    function displayResults(results, resultsContainer) {
        resultsContainer.innerHTML = ''; // Clear previous results
        if (results.length > 0) {
            resultsContainer.style.display = 'block'; // Show the container if results exist
            const ul = document.createElement('ul');
            results.forEach(result => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = result.link;
                a.textContent = result.title;
                li.appendChild(a);
                ul.appendChild(li);
            });
            resultsContainer.appendChild(ul);
        } else {
            resultsContainer.style.display = 'none'; // Hide the container if no results
        }
    }
});
