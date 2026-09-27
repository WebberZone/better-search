---
slug: better-search-ai-answers-developer-reference
title: "AI Answers Developer Reference"
products: [better-search]
sections: ["03-bs-developer-docs"]
tags: [better-search, pro, ai, developer]
status: publish
order: 0
toc: true
---

[toc]

Better Search Pro 4.5.0 exposes AI answers through `POST /wp-json/bsearch/v1/ask` and the `better-search/ask` WordPress ability. Both use the same retrieval, limits, cache, and answer validation. The module must be enabled and a compatible WordPress AI Client provider configured.

## REST request

Send a JSON body with `question`, a plain-language question of 3–300 characters. The frontend may also send its signed `context` token and a `post_types` array limited to the site's configured searchable types. The browser endpoint checks the request origin and client before handling it; it is intended for the site's visitor interface.

The response contains `answered`, `answer`, `sources`, `related`, and `cached`. An unanswered response has `answered: false` and a fallback message. Source and related entries contain an ID, title, and URL. Clients should display linked sources and preserve ordinary search as a fallback.

## WordPress ability

`better-search/ask` accepts an object with a required `question` string and returns the same answer structure. It requires a logged-in user with the `read` capability. It does not accept the browser context token or post-type override. Use it through the WordPress Abilities API when an authenticated integration needs a grounded answer.

## Extension hooks

The module provides these filters:

| Filter | Purpose |
| --- | --- |
| `bsearch_ai_retriever` | Replace the retrieval implementation with an object implementing the AI retriever interface. |
| `bsearch_ai_retrieved_articles` | Adjust retrieved article selection; article IDs are revalidated before sending context. |
| `bsearch_ai_system_instruction` | Adjust the system instruction sent with the question and excerpts. |
| `bsearch_ai_prompt_builder` | Adjust the WordPress AI Client prompt builder. |
| `bsearch_ai_answer` | Inspect or adjust the validated answer result. |
| `bsearch_ai_is_bot` | Adjust browser-client detection for the public REST route. |
| `bsearch_ai_allowed_origins` | Extend the allowed browser origins for the REST route. |

Use `bsearch_ai_pre_prompt` only for integrations that intentionally replace the provider response. Returned answers still pass the module's source validation. Keep custom retrieval scoped to public, published content; the plugin rechecks article IDs, but custom integrations remain responsible for the content they send to a provider.
