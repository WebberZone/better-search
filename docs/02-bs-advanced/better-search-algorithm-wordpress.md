---
slug: better-search-algorithm-wordpress
title: "How the Better Search Algorithm Works"
products: [better-search]
sections: ["02-bs-advanced"]
tags: [better-search, search]
status: publish
order: 0
featured_image: "https://webberzone.com/wp-content/uploads/2024/11/Better-Search-Pro-Search-everything.webp"
---

[Better Search](https://webberzone.com/plugins/better-search/) uses <a href="https://dev.mysql.com/doc/refman/8.4/en/fulltext-search.html" target="_blank" rel="noreferrer noopener">MySQL Full-Text search</a> to rank results by relevance. Here is how Better Search processes search queries, ranks posts, and calculates relevance.

## Overview of the Search Algorithm

1. **Fulltext Search**:
Better Search uses MySQL’s FULLTEXT search capabilities to identify matching content. This allows for features like:
    - **Phrase Search**: Exact matches for terms enclosed in quotes (e.g., `"custom templates"`).
    - **Boolean Operators**: Support for advanced queries using `+` (must include), `-` (must exclude), and wildcard `*` operators when [“Boolean mode” is activated](https://webberzone.com/support/knowledgebase/better-search-settings-search/#activate-boolean-mode).
2. **Result Scoring**:
The MySQL engine calculates a **score** for each post based on how closely the content matches the search terms. Posts with higher scores are more relevant to the query.
3. **Ranking by Relevance**:
Posts are sorted in descending order of score, ensuring the most relevant results appear at the top of the list.

## Recency weighting *(Pro only)*

When **Recency boost (%)** is above 0 and results are ordered by relevance or relatedness, Better Search multiplies each raw relevance score by an age-decay multiplier. Newer results receive a larger multiplier, while older results move closer to their original score. The default boost is 0, so existing relevance ordering stays unchanged until you enable it.

**Recency half-life (days)** controls the decay. At the half-life, a result receives half of the available recency boost. Values below 7 days use hourly age calculations; larger values use daily calculations. Better Search uses the UTC publish date in `post_date_gmt`. A missing or zero date receives no boost, and a future date receives no more than the maximum boost.

The boost changes `ORDER BY` only. Better Search keeps the raw score for relevance percentages and **Minimum relevance percentage** filtering. Date, title, and random ordering skip the boost. SQLite does not run the date calculation.

When **Use Custom Tables** is enabled, run **Recency Ranking Backfill** from the Tools page after upgrading an existing index. The backfill copies UTC publish dates into older index rows, and custom-table recency ordering remains disabled until it finishes. On multisite, the shared index requires a network administrator to run the backfill.

## Calculating Relevance

When **Seamless Mode** is disabled (i.e., the advanced search template is used), Better Search displays a **relevance percentage** to each result:

- The post with the **highest score** is assigned **100% relevance**.
- Other posts are assigned a percentage based on their score relative to the highest score.

For example, if the highest score is 50 and another post scores 25, its relevance will be **50%**.

This percentage is displayed in the search results when using the advanced template.

## Additional Features of the Algorithm

- **Customizable Templates**: The search results template can be tailored to show relevance scores, excerpts, and other metadata.
- **Compatibility with WordPress Themes**: Works with both classic and block themes.
- **Dynamic Switching**: When search terms have fewer characters than the configurable **Minimum characters** setting (default: 4), the algorithm dynamically switches between full-text search and LIKE-based fallback.
