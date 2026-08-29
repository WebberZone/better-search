/**
 * Client-side search term highlighting for Better Search.
 *
 * Covers full-page-cache scenarios where the PHP <mark>-wrapping filters never run, by
 * mirroring the same logic client-side via a TreeWalker.
 */
(function () {
	'use strict';

	/**
	 * Extract terms to highlight from a raw search query string.
	 *
	 * Mirrors PHP Display::extract_highlight_terms(): keeps quoted phrases intact, excludes
	 * "-"-prefixed terms, strips boolean-mode operators.
	 *
	 * @param {string} searchQuery Raw search query.
	 * @return {string[]} Unique, non-empty terms/phrases.
	 */
	function extractHighlightTerms(searchQuery) {
		if (!searchQuery) {
			return [];
		}

		// Decode URL-encoded characters. Guard against malformed sequences (e.g. lone %).
		searchQuery = searchQuery.replace(/\+/g, ' ');
		try {
			searchQuery = decodeURIComponent(searchQuery);
		} catch (e) {
			// Leave the partially-decoded string as-is.
		}

		var keys = [];
		// Same tokeniser pattern as the PHP version.
		// Built via new RegExp() so that engines without lookbehind support
		// throw at runtime (catchable) instead of at parse time (fatal).
		var simplePattern = /"[^"]*(?:"|$)|[^\t ",+]+/g;
		var tokens = [];
		var m;

		try {
			var tokenPattern = new RegExp('".*?(?:"|$)|((?:^|(?<=[\\t ",+]))[^\\t ",+]+)', 'g');
			while ((m = tokenPattern.exec(searchQuery)) !== null) {
				tokens.push(m[0]);
			}
		} catch (e) {
			// Lookbehind not supported — use the simplified pattern.
			tokens = [];
			while ((m = simplePattern.exec(searchQuery)) !== null) {
				tokens.push(m[0]);
			}
		}

		var seen = {};
		for (var i = 0; i < tokens.length; i++) {
			var token = tokens[i].replace(/^\s+|\s+$/g, '');
			if (token === '') {
				continue;
			}

			// Quoted phrase — keep as a single term.
			if (token.charAt(0) === '"') {
				var phrase = token.replace(/^"+|"+$/g, '').replace(/^\s+|\s+$/g, '');
				if (phrase !== '' && !seen[phrase]) {
					seen[phrase] = true;
					keys.push(phrase);
				}
				continue;
			}

			// Excluded term (boolean NOT) — skip.
			if (token.charAt(0) === '-') {
				continue;
			}

			// Strip boolean mode operators.
			token = token.replace(/^[+\-~<>()*!]+|[+\-~<>()*!]+$/g, '');

			// Split on whitespace / dots (same as PHP preg_split '/[\s\.]+/').
			var words = token.split(/[\s.]+/);
			for (var j = 0; j < words.length; j++) {
				var word = words[j];
				if (word !== '' && !seen[word]) {
					seen[word] = true;
					keys.push(word);
				}
			}
		}

		return keys;
	}

	/**
	 * Parse the search query from a URL string.
	 *
	 * Supports both query-string format (?s=term) and pretty-permalink
	 * format (/search/term/).
	 *
	 * @param {string} url URL to parse.
	 * @return {string} Raw search query, or empty string.
	 */
	function parseSearchQuery(url) {
		if (!url) {
			return '';
		}

		// Try ?s= or &s= query parameter.
		var paramMatch = url.match(/[?&]s=([^&]+)/);
		if (paramMatch) {
			return paramMatch[1];
		}

		// Try pretty permalink /search/term/.
		var pathMatch = url.match(/\/search\/([^/?#]+)/i);
		if (pathMatch) {
			return pathMatch[1];
		}

		return '';
	}

	/**
	 * Escape a string for use in a RegExp.
	 *
	 * @param {string} str String to escape.
	 * @return {string} Escaped string.
	 */
	function escapeRegExp(str) {
		return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
	}

	/**
	 * Highlight terms inside a single text node by splitting it and inserting
	 * <mark> elements around each match.
	 *
	 * @param {Text}   textNode Text node to process.
	 * @param {RegExp} pattern  Compiled highlight pattern.
	 * @param {string} tag      HTML tag to use for highlight wrapper.
	 * @param {string} cls      CSS class to apply to the wrapper.
	 */
	function highlightTextNode(textNode, pattern, tag, cls, useBoundaries) {
		var text = textNode.nodeValue;
		if (!pattern.test(text)) {
			return;
		}
		// Reset lastIndex since the pattern is /g.
		pattern.lastIndex = 0;

		var frag = document.createDocumentFragment();
		var lastIndex = 0;
		var m;

		while ((m = pattern.exec(text)) !== null) {
			// In boundary mode, group 1 is the (unwrapped) separator and group 2 is the term.
			var matchStart = useBoundaries ? m.index + m[1].length : m.index;
			var matchText = useBoundaries ? m[2] : m[0];

			// Text before the match.
			if (matchStart > lastIndex) {
				frag.appendChild(document.createTextNode(text.slice(lastIndex, matchStart)));
			}

			// Highlighted match.
			var mark = document.createElement(tag);
			mark.className = cls;
			mark.appendChild(document.createTextNode(matchText));
			frag.appendChild(mark);

			lastIndex = matchStart + matchText.length;

			// Prevent infinite loop on zero-width matches.
			if (m[0].length === 0) {
				pattern.lastIndex++;
			}
		}

		// Remaining text after the last match.
		if (lastIndex < text.length) {
			frag.appendChild(document.createTextNode(text.slice(lastIndex)));
		}

		textNode.parentNode.replaceChild(frag, textNode);
	}

	/**
	 * Walk the DOM within a root element and highlight all text nodes.
	 *
	 * Skips script, style, and already-highlighted elements.
	 *
	 * @param {Element} root    Root element to search within.
	 * @param {RegExp}  pattern Compiled highlight pattern.
	 * @param {string}  tag     HTML tag to use for highlight wrapper.
	 * @param {string}  cls     CSS class to apply to the wrapper.
	 */
	function highlightInElement(root, pattern, tag, cls, useBoundaries) {
		var skipTags = { SCRIPT: true, STYLE: true, NOSCRIPT: true, TEXTAREA: true, SELECT: true };
		var walker = document.createTreeWalker(
			root,
			NodeFilter.SHOW_TEXT,
			{
				acceptNode: function (node) {
					var parent = node.parentNode;
					// Skip nodes inside skip tags.
					while (parent && parent !== root) {
						if (skipTags[parent.nodeName]) {
							return NodeFilter.FILTER_REJECT;
						}
						// Skip text already inside a highlight wrapper.
						if (
							parent.nodeName === tag.toUpperCase() &&
							parent.classList &&
							parent.classList.contains(cls)
						) {
							return NodeFilter.FILTER_REJECT;
						}
						parent = parent.parentNode;
					}
					return NodeFilter.FILTER_ACCEPT;
				},
			}
		);

		// Collect text nodes first (modifying the DOM mid-walk breaks the walker).
		var textNodes = [];
		var node;
		while ((node = walker.nextNode())) {
			textNodes.push(node);
		}

		for (var i = 0; i < textNodes.length; i++) {
			highlightTextNode(textNodes[i], pattern, tag, cls, useBoundaries);
		}
	}

	/**
	 * Main entry point.
	 *
	 * Runs after the DOM is ready.  Reads configuration from the
	 * bsearch_highlight global injected via wp_localize_script(), then
	 * detects the search query and applies highlighting.
	 */
	function init() {
		var config = (typeof bsearch_highlight !== 'undefined') ? bsearch_highlight : {};
		var tag = config.tag || 'mark';
		var cls = config.cls || 'bsearch_highlight';
		var siteUrl = (config.site_url || '').replace(/^https?:\/\//i, '');
		var maxTerms = parseInt(config.max_terms, 10) || 50;
		var useBoundaries = !!config.use_boundaries;

		// Selectors for elements to highlight within (content area only).
		var selectors = config.selectors || '.entry-content, .entry-title, .entry-summary';

		// 1. Try to get the query from document.referrer.
		var rawQuery = '';
		var referrer = document.referrer || '';

		if (referrer) {
			var schemelessReferrer = referrer.replace(/^https?:\/\//i, '');
			if (siteUrl && schemelessReferrer.toLowerCase().indexOf(siteUrl.toLowerCase()) === 0) {
				rawQuery = parseSearchQuery(referrer);
			}
		}

		// 2. Nothing from referrer — bail (we do not add URL params by design).
		if (!rawQuery) {
			return;
		}

		// 3. Extract individual terms.
		var terms = extractHighlightTerms(rawQuery);
		if (terms.length === 0) {
			return;
		}

		// Cap the number of terms.
		if (terms.length > maxTerms) {
			terms = terms.slice(0, maxTerms);
		}

		// 4. Sort longest-first (mirrors PHP: longer phrases matched before subwords).
		terms.sort(function (a, b) {
			return b.length - a.length;
		});

		// 5. Build a combined RegExp (case-insensitive, global).
		// Multi-word phrases allow flexible whitespace between words.
		var escapedTerms = terms.map(function (term) {
			return escapeRegExp(term).replace(/\s+/g, '\\s+');
		});
		var pattern;
		try {
			if (useBoundaries) {
				// Mirrors PHP Helpers::highlight(): group 1 is the separator (not wrapped),
				// group 2 is the term (wrapped). Requires Unicode property escape support.
				pattern = new RegExp(
					'(^|[\\s\\p{P}\\p{Z}])(' + escapedTerms.join('|') + ')(?=[\\s\\p{P}\\p{Z}]|$)',
					'giu'
				);
			} else {
				pattern = new RegExp(escapedTerms.join('|'), 'gi');
			}
		} catch (e) {
			// Unicode property escapes unsupported (old engines) — fall back to unbounded matching.
			useBoundaries = false;
			try {
				pattern = new RegExp(escapedTerms.join('|'), 'gi');
			} catch (e2) {
				return;
			}
		}

		// 6. Apply highlighting to each matching element.
		// Filter out any root that is contained within another root to avoid double-walking.
		var roots;
		try {
			roots = Array.prototype.slice.call(document.querySelectorAll(selectors));
		} catch (e) {
			// Invalid selector — fall back to the safe default, not document.body.
			try {
				roots = Array.prototype.slice.call(
					document.querySelectorAll('.entry-content, .entry-title, .entry-summary')
				);
			} catch (e2) {
				return;
			}
		}
		roots = roots.filter(function (el) {
			return !roots.some(function (other) {
				return other !== el && other.contains(el);
			});
		});
		for (var i = 0; i < roots.length; i++) {
			highlightInElement(roots[i], pattern, tag, cls, useBoundaries);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		// DOM already ready (script is deferred or in footer).
		init();
	}
})();
