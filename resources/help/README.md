# Tenant Help Center content

Markdown source for the in-product Help Center (admin user menu → "Help",
also linkable via `<x-help-link to="..." />` from any admin page).

## Layout

```
resources/help/
├── {topic-slug}/
│   ├── {article-slug}.md
│   └── ...
└── ...
```

## File format

Each `.md` file is plain Markdown. The first `# Heading` becomes the article
title; everything below it becomes the body.

```markdown
# Setting Up Your Store

Welcome to KneadIt! ...
```

GitHub-flavored Markdown is supported (lists, fenced code, tables, links,
inline HTML). Embed screenshots from `public/images/help/` if needed.

## Adding a topic

1. Add the topic to `config/help.php` under `topics` with `title`, `icon`
   (Heroicon enum), `color` (Tailwind color token), and `sort` order.
2. `mkdir resources/help/{slug}` matching the config key.
3. Drop in `.md` files. They appear in alphabetical filename order.

## Adding an article

Create `resources/help/{topic-slug}/{article-slug}.md` and start it with
`# Article Title`. No code or config changes required.

## Featuring an article on the landing page

Add `{topic-slug}/{article-slug}` to the `popular` array in `config/help.php`.

## Linking to a specific article from an admin page

```blade
<x-help-link to="getting-started/setting-up-your-store" label="How to set up" />
```

Adds a small "?" icon that opens the Help Center scrolled to the article.
Pass `inline` to render as an inline link with the label visible.
