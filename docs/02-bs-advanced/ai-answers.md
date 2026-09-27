---
slug: better-search-ai-answers
title: "AI Answers in Better Search Pro"
products: [better-search]
sections: ["02-bs-advanced"]
tags: [better-search, pro, ai, search]
status: publish
order: 0
toc: true
---

[toc]

Better Search Pro 4.5.0 can answer a visitor's question using published content from the current site. It finds relevant posts with Better Search, sends limited excerpts to a configured WordPress AI Client provider, and shows an answer with source links. If the content does not support an answer, visitors get a link to ordinary search results. AI answers are off by default.

## Set up AI answers

1. Install and configure a provider that supports text generation through the WordPress AI Client. Check that the provider is connected and its credentials are valid.
2. Go to **Better Search → Settings → AI (Beta)**. Select the **AI provider**, turn on **Enable AI answers**, and save.
3. Choose how many articles and characters per article can be sent as context. Set a daily request cap and per-visitor hourly limit that fit your provider budget.
4. Leave **Show Ask AI in search forms** enabled to add an explicit **Ask AI** button to Better Search forms. Visitors can still run a normal search at any time.

The provider status appears on the AI settings tab. AI controls only appear when the module is enabled and the WordPress AI Client supports the selected provider.

## Place the question panel

Use the `[bsearch_ai]` shortcode on a page, or call `do_action( 'bsearch_ai_answer_panel' )` in a theme template. The panel and search-form button submit questions only when a visitor chooses **Ask AI**. They do not turn regular searches into AI requests.

Answers include links to the articles used when **Show sources** is enabled. A reminder tells visitors to check those sources because AI answers can contain mistakes. The fallback message can be customized on the AI settings tab.

## Content and privacy

The module searches public, published, non-password-protected posts from the post types enabled for Better Search. It uses the current site on multisite and does not include protected or cross-site content. A limited excerpt of each selected article and the visitor's question are sent to the configured provider. Review the provider's data handling terms before enabling the feature.

Question recording is optional and off by default. Enable **Record questions for Content gaps** to see frequently unanswered questions in the admin report. Set **Log retention (days)** to control how long those records remain. Disabling recording stops new records; existing records expire according to the retention setting. Better Search's privacy policy text describes this processing.

Answers are cached and invalidated when relevant content or settings change. The daily cap and per-visitor limit include provider requests, so cached answers do not use another provider request.
