# Wordpress Theme Quickstart

It leverages Timber/Twig for template management, HTMX and Alpine.js for interactivity, and Tailwind CSS for styling.
The theme is designed to be lightweight, fast, and extendable.

**Required Plugins**

* [ACF Pro](https://www.advancedcustomfields.com/pro/) or [SCF](https://wordpress.org/plugins/secure-custom-fields/)
* [ACF Extended](https://wordpress.org/plugins/acf-extended/)

## 🚀 Features

* ⚡ Vite 7 for bundling
* 🎨 Tailwind CSS v4 + @tailwindcss/typography
* ✍️ TypeScript for scripts (assets/ts/)
* 🔄 HTMX and Alpine.js for modern frontend interactions
* 📦 Hashed, manifest-based asset loading for production
* 🪶 Twig templating via Timber

## Installation

**Clone the repository**

```bash
git clone https://github.com/ryansallen98/wordpress-quickstart
```

**Install both Node and PHP dependencies**

```bash
npm install
composer install
```

**Build the assets bundle**

```bash
npm run build
```

## Development

Watch for typescript and css changes without minification and rebuilding

```bash
npm run dev
```
