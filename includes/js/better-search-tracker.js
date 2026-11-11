document.addEventListener('DOMContentLoaded', function () {
    var data = {
        action: 'bsearch_tracker',
        bsearch_search_query: ajax_bsearch_tracker.bsearch_search_query
    };
    if (ajax_bsearch_tracker.bsearch_no_results) {
        data.bsearch_no_results = 1;
        data.bsearch_token = ajax_bsearch_tracker.bsearch_token;
    }
    fetch(ajax_bsearch_tracker.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cache-Control': 'no-cache'
        },
        body: new URLSearchParams(data).toString()
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (text) {
            // handle the response text
        })
        .catch(function (error) {
            console.error('Error:', error);
        });
});