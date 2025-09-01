# Wordpress Theme Quickstart

It leverages Sage/Blade for structure/template management, HTMX and Alpine.js for interactivity, and Tailwind CSS for styling. 
The theme is designed to be lightweight, fast, and extendable.

> "Sage brings proper PHP templating and modern JavaScript tooling to WordPress themes. Write organized, component-based code using Laravel Blade, enjoy instant builds and CSS hot-reloading with Vite, and leverage Laravel's robust feature set through Acorn." - Roots

[Read the sage docs to get started](https://roots.io/sage/docs/installation/)

**Required Plugins**

- [ACF Pro](https://www.advancedcustomfields.com/pro/) or [SCF](https://wordpress.org/plugins/secure-custom-fields/)
- [ACF Extended](https://wordpress.org/plugins/acf-extended/)

## 🚀 Features

- 🔧 Clean, efficient theme templating with Laravel Blade
- ⚡️ Modern front-end development workflow powered by Vite with HMR
- 🎨 Tailwind CSS v4
- 🚀 Harness the power of Laravel with [Acorn integration](https://github.com/roots/acorn)
- 📦 Block editor support built-in
- 🔄 HTMX and Alpine.js for modern frontend interactions
- ✍️ TypeScript support by default
- 🧱 Easily customizable Shadcn inspired blade components built with alpine.js


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

To use vite HMR whilst developing

```bash
npm run dev
```
