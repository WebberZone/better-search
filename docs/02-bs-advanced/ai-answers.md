---
slug: better-search-ai-answers
title: "AI Answers in Better Search Pro"
products: [better-search]
sections: ["02-bs-advanced"]
tags: [ai, better-search, pro, search]
status: publish
order: 0
toc: true
---

[toc]

[Better Search Pro](https://webberzone.com/plugins/better-search/pro/) 4.5.0 can answer a visitor's question from your published posts. It finds relevant posts with Better Search, sends short excerpts to your AI provider, and shows a brief answer with source links. When your content has no answer, visitors get a link to the search results. AI answers are in beta and off by default.

## Requirements

- Better Search Pro 4.5.0 or later.
- WordPress 7.0 or later. AI answers use the AI Client built into WordPress 7.0. On older versions the feature does not load and search works as before.
- An AI provider connected under **Settings → Connectors** that supports text generation with structured JSON output.

Better Search never stores API keys; WordPress sends each request through your connected provider, which bills you per request.

## Set up AI answers

1. Connect a provider under **Settings → Connectors** and check that its credentials are valid.
2. Go to **Better Search → Settings → AI (Beta)**, turn on **Enable AI answers**, and save. The tab shows whether the provider is available.
3. Set the **Daily request cap** and **Questions per visitor per hour** to fit your provider budget.

## Settings

| Setting | Default | What it does |
| --- | --- | --- |
| **Enable AI answers** | Off | Lets visitors ask questions answered from your site. |
| **AI provider** | Automatic | The connected provider that answers. Automatic lets WordPress choose, and is used if the chosen provider is removed. |
| **Model** | Provider default | The model used with the chosen provider. Shown when a provider is chosen; save after changing the provider to list its models. Models without reasoning usually answer in a second or two; reasoning models can take ten seconds or more. |
| **Fallback provider** | No fallback | Tried when the main provider fails. Shown only when two or more providers are connected. |
| **Fallback model** | Provider default | The model used with the fallback provider. Shown when a fallback provider is chosen. |
| **Articles sent as context** | `4` | Matching posts sent with each question, from 1 to 8. More posts improve coverage but cost more. |
| **Maximum characters per article** | `3000` | Each post is converted to plain text and trimmed to this length, from 500 to 10,000. |
| **Answer length** | Short | **Short** gives two or three sentences. **Medium** gives one or two paragraphs. |
| **Show sources** | On | Lists links to the posts the answer is based on. |
| **Fallback message** | Built-in text | Shown with a search results link when your content does not answer the question. |
| **Unavailable message** | Built-in text | Shown with a search results link when the provider cannot be reached or is paused. |
| **Show Ask AI in search forms** | On | Adds an **Ask AI** button to Better Search forms, the Search block and theme search forms. |
| **Search form buttons** | Search and Ask AI | Show both buttons, or **Ask AI only**. Search results stay available from the answer. |
| **Ask AI button text** | Ask AI | Custom label for the button. |
| **Daily request cap** | `200` | Provider requests per day across the site. Once reached, visitors are sent to the search results until the next day in site time. |
| **Questions per visitor per hour** | `10` | Per-visitor limit, from 1 to 1,000. |
| **Record questions for Content gaps** | Off | Stores question text for the **Content gaps** report. |
| **Log retention (days)** | `90` | Recorded questions are deleted after this many days. |

**Compare models.** The **Compare models** button under **AI provider** opens a window where you ask one question with two models side by side and see each answer, its sources and how long it took. **Use this model** fills in the matching **Model** setting; close the window and save to keep it. Each model costs one provider request, counted towards the daily request cap. These answers are not cached or recorded.

## Where visitors ask

With **Show Ask AI in search forms** on, the **Ask AI** button appears on the `[[bsearch_form]]` shortcode, the **Search Form [Better Search]** widget, the core Search block and theme search forms built with `get_search_form()`, such as a classic theme's header or sidebar search. Only **Ask AI** sends a question to the provider; **Search** runs a normal search.

Override the setting for a single form:

- **Shortcode:** `[[bsearch_form ai_mode="replace" ai_text="Ask a question"]]`. `ai_mode` accepts `default`, `alongside`, `replace` or `off`.
- **Widget:** choose an **Ask AI button** option in the widget settings.
- **Theme search form:** pass `ai_mode` and `ai_text` in the arguments, for example `get_search_form( array( 'ai_mode' => 'off' ) )`.
- **Search block:** add the CSS class `bsearch-ai-alongside`, `bsearch-ai-replace` or `bsearch-ai-off` under **Advanced → Additional CSS class(es)**.

The button is not added to Knowledge Base search forms, or to forms marked `data-bsearch-live-search="off"`.

For a standalone question box, add the `[[bsearch_ai]]` shortcode to a page, or call `do_action( 'bsearch_ai_answer_panel' );` in a theme template. It uses the **Search form buttons** and **Ask AI button text** settings.

## What visitors see

Questions must be 3 to 300 characters; longer or shorter ones are rejected with a message. An answered question shows the answer, the source links when **Show sources** is on, and a reminder that AI answers can contain mistakes.

When your content does not answer the question, the visitor sees the fallback message, a link to the search results, and the closest matching post when there is one. When the provider is down or paused, the visitor sees the unavailable message. When a limit is reached, a message asks the visitor to use the search results or try again later.

Requests from bots, scripts and other sites are refused.

## Costs and limits

Each uncached question costs one provider request. Cached answers cost nothing and do not count towards either limit. Rephrasings of a question share a cached answer: "How does the cache work?" and "how do caches work please" get the same answer, while question words and negations are kept apart, so "Why does X not work?" is asked separately from "Why does X work?". When several visitors ask the same new question at once, only one provider request is made.

Cached answers are kept for a week. An answer is retired as soon as one of the posts it was built from is edited, unpublished or deleted, or its terms, indexed custom fields or (when comments are searched) approved comments change. Publishing or updating any searched post retires only cached "no answer" results, since the new content may answer them. Changing the AI or search settings, or a term in a searched taxonomy, retires every cached answer.

Each provider gets 20 seconds to answer before the fallback provider is tried. Some connectors allow reasoning models longer.

If the provider returns a qualifying error, such as a network, rate-limit, quota or server error, Better Search pauses it for 5 minutes, increasing up to an hour on repeated failures. The fallback provider is used during the pause, if one is set. A successful request clears the pause.

## Content and privacy

Only public, published, non-password-protected posts from the post types Better Search searches are used, and only from the current site on multisite. The question and a short excerpt of each post are sent to your provider, so review its data handling terms first.

The hourly limit identifies visitors by a salted hash of their IP address, kept for the hour and never logged. Better Search adds suggested text to the WordPress privacy policy guide.

## Content gaps

Turn on **Record questions for Content gaps** to see which questions your site could not answer. Only the question text is stored, without IP addresses or user details. Turning it off stops new records; existing ones expire after the retention period.

Open **Better Search → Content gaps** to review grouped unanswered questions. Filter by date, export the report as CSV, or use **Empty log** to permanently delete every recorded question, answered or not.

## Troubleshooting

- **No Ask AI button:** check that **Enable AI answers** is on, the site runs WordPress 7.0 or later, and the form is not set to `off` or marked `data-bsearch-live-search="off"`.
- **Every question gets the unavailable message:** the provider is failing, paused, or does not support structured JSON output. Check it under **Settings → Connectors**, or set a fallback provider.
- **Answers are outdated:** open **Better Search → Tools** and use **Clear cache**. This clears cached answers and the provider's paused state along with the search cache.

## See also

- [AI Answers Developer Reference](https://webberzone.com/support/knowledgebase/better-search-ai-answers-developer-reference/)
- [Better Search Pro CLI Overview](https://webberzone.com/support/knowledgebase/better-search-wp-cli/)
- [Better Search Shortcodes](https://webberzone.com/support/knowledgebase/better-search-shortcodes/)
