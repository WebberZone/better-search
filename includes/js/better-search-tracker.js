document.addEventListener('DOMContentLoaded', function() {
    fetch(ajax_bsearch_tracker.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cache-Control': 'no-cache'
        },
        body: new URLSearchParams({
            action: 'bsearch_tracker',
			bsearch_search_query: ajax_bsearch_tracker.bsearch_search_query
        }).toString()
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        // handle the response data
    })
    .catch(function(error) {
        console.error('Error:', error);
    });
});