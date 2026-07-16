/**
 * Client-side search term highlighting for Better Search.
 *
 * When a page is served from a full-page cache (e.g. LiteSpeed Cache) the PHP
 * filters that normally inject <mark> wrappers never run.  This script reads
 * the search query from document.referrer (or the bsearch_highlight config
 * object injected by PHP), extracts the terms to highlight using the same
 * logic as the PHP extract_highlight_terms() function, and applies highlighting
 * directly in the DOM via a TreeWalker over text nodes.
 *
 * No third-party libraries are required.
 */
(function () {
	'use strict';

	/**
	 * Extract terms to highlight from a raw search query string.
	 *
	 * Mirrors the PHP Display::extract_highlight_terms() logic:
	 *  - Double-quoted phrases are kept intact.
	 *  - Terms prefixed with "-" are excluded (boolean NOT).
	 *  - Boolean mode operators (+ ~ < > ( ) * !) surrounding a token are stripped.
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
	function highlightTextNode(textNode, pattern, tag, cls) {
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
			// Text before the match.
			if (m.index > lastIndex) {
				frag.appendChild(document.createTextNode(text.slice(lastIndex, m.index)));
			}

			// Highlighted match.
			var mark = document.createElement(tag);
			mark.className = cls;
			mark.appendChild(document.createTextNode(m[0]));
			frag.appendChild(mark);

			lastIndex = m.index + m[0].length;

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
	function highlightInElement(root, pattern, tag, cls) {
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
			highlightTextNode(textNodes[i], pattern, tag, cls);
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
			pattern = new RegExp(escapedTerms.join('|'), 'gi');
		} catch (e) {
			return;
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
			highlightInElement(roots[i], pattern, tag, cls);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		// DOM already ready (script is deferred or in footer).
		init();
	}
})();
