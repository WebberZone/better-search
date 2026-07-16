---
slug: multisite-search
title: "Multisite Search"
products: [better-search]
sections: [02-bs-advanced]
tags: [better-search,multisite,search]
status: publish
order: 0
---

[Better Search Pro](https://webberzone.com/plugins/better-search/pro/) supports multisite search, enabling you to search across multiple WordPress sites within your network. This feature enhances search functionality for multisite installations, making it easier for users to find content distributed across different sites.

Whether you’re managing a network of blogs, a portfolio of corporate websites, or a learning platform with multiple subsites, multisite search brings all your content together under one roof.

## **What Is Multisite Search?**

Multisite search allows you to query posts, pages, and custom post types across multiple sites in a WordPress network. With this feature, users can:

- Search for content across selected sites or the entire network.
- View the same results across the selected sites, allowing users to browse the network.
- Access results faster with optimized database queries.

**Use Case Examples**:

- A company managing a multisite WordPress installation for various departments can enable network-wide content search.
- Educational institutions with multiple campuses or courses can allow students to find content from all subsites.

## **How to Enable Multisite Search in Better Search Pro**

Multisite search is a **Pro Multi** feature. Ensure you have the **Pro version of Better Search** installed and activated on your network and have purchased a multiple-site plan for at least the number of sites you need to search.

### 1. Enable Multisite Search

<figure class="wp-block-image size-large">
<img src="https://webberzone.com/wp-content/uploads/2024/11/Better-Search-Pro-Multisite-Settings-1024x586.webp" class="wp-image-8444" loading="lazy" decoding="async" srcset="https://webberzone.com/wp-content/uploads/2024/11/Better-Search-Pro-Multisite-Settings-1024x586.webp 1024w, https://webberzone.com/wp-content/uploads/2024/11/Better-Search-Pro-Multisite-Settings-300x172.webp 300w, https://webberzone.com/wp-content/uploads/2024/11/Better-Search-Pro-Multisite-Settings-768x440.webp 768w, https://webberzone.com/wp-content/uploads/2024/11/Better-Search-Pro-Multisite-Settings-1536x879.webp 1536w, https://webberzone.com/wp-content/uploads/2024/11/Better-Search-Pro-Multisite-Settings.webp 1544w" sizes="auto, (max-width: 1024px) 100vw, 1024px" width="1024" height="586" alt="Better Search Pro - Multisite Settings" />
</figure>

Follow these steps to enable and configure multisite search:

1. Go to **Network Admin \> Plugins** and activate Better Search Pro.
2. Navigate to **Network Admin \> Better Search \> Settings**.
3. You will see a list of all subsites in the network.
4. Check the boxes for the sites you want to include in the search or select **All Sites** for a global search.
5. Save your changes.

### 2. Enable Caching

Multisite search can query large datasets, which may impact performance on large networks. It’s highly recommended to enable caching:

- Enable caching in the Better Search settings page for each site.
- Use a caching plugin like **WP Rocket** or **W3 Total Cache**.
- Enable **Object Cache** if your host supports it.
- Consider using **server-level caching** (e.g., Redis or Memcached).
