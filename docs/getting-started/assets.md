---
title: Assets
weight: 3
---

## Compiling assets

We use [Tailwind CSS](https://tailwindcss.com/) and custom Filament themes.
If you are using Tailwind CSS v4, class scanning is configured from your CSS entry file using `@source` (instead of the old `tailwind.config.js` `content` array).

### Custom Classes

Add these source paths to your app stylesheet (for example `resources/css/app.css`):

```css
@source "../../vendor/lara-zeus/echoo/resources/views/**/*.blade.php";
```
