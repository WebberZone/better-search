---
slug: better-search-ai-answers-developer-reference
title: "AI Answers Developer Reference"
products: [better-search]
sections: ["03-bs-developer-docs"]
tags: [ai, better-search, developer, pro]
status: publish
order: 0
toc: true
---

[toc]

[Better Search Pro](https://webberzone.com/plugins/better-search/pro/) 4.5.0 exposes AI answers through `POST /wp-json/bsearch/v1/ask` and the `better-search/ask` WordPress ability. Both use the same retrieval, limits, cache, and answer validation. The module must be enabled and a compatible WordPress AI Client provider configured.

## REST request

Send a JSON body with `question`, a plain-language question of 3–300 characters. The frontend may also send its signed `context` token and a `post_types` array limited to the site's configured searchable types. The browser endpoint checks the request origin and client before handling it; it is intended for the site's visitor interface.

The response contains `answered`, `answer`, `sources`, `sources_title`, `related`, `related_title`, `cached`, `message`, `search_url`, and `reason`. An unanswered response has `answered: false` and a fallback or unavailable message. Source and related entries contain an ID, title, and URL. `reason` can be `daily_cap`, `provider_error`, `provider_paused`, `provider_unavailable`, or `invalid_response`. Clients should display linked sources and preserve ordinary search as a fallback.

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
| `bsearch_ai_provider_cooldown` | Adjust how long a provider is paused after a qualifying error. |
| `bsearch_ai_provider_options` | Adjust the providers offered in the primary and fallback settings. |
| `bsearch_ai_configured_providers` | Adjust the providers considered configured for fallback selection. |
| `bsearch_ai_answer` | Inspect or adjust the validated answer result. |
| `bsearch_ai_is_bot` | Adjust browser-client detection for the public REST route. |
| `bsearch_ai_allowed_origins` | Extend the allowed browser origins for the REST route. |

Use `bsearch_ai_pre_prompt` only for integrations that intentionally replace the provider response. It receives the provider ID after the prompt arguments. Returned answers still pass the module's source validation. Keep custom retrieval scoped to public, published content; the plugin rechecks article IDs, but custom integrations remain responsible for the content they send to a provider.

## Provider chain and cooldowns

The provider chain contains the selected primary provider followed by the configured fallback provider. When neither is explicitly selected, WordPress chooses from the configured providers. Better Search skips a provider while its cooldown transient is active.

Qualifying failures increase the cooldown from five minutes up to one hour. Network failures, rate limits, authentication or quota errors, and server errors trigger a cooldown; other client errors do not. A successful request clears the provider's failure count and cooldown. The `bsearch_ai_provider_cooldown` filter can adjust the calculated duration.

## Cache operations

AI answers use transients prefixed with `bsearch_ai_answer_`. Clearing the AI cache also removes keyword-count caches, the provider availability result, and every provider cooldown and failure counter.
