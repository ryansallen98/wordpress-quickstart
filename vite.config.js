import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';
import paletteFromTheme from './vite.plugins/palette-from-theme';

export default defineConfig({
  base: '/app/themes/sage/public/build/',
  plugins: [
    tailwindcss(),
    laravel({
      input: [
        'resources/css/app.css',
        'resources/ts/app.ts',
        'resources/css/editor.css',
        'resources/ts/editor.ts',
      ],
      refresh: true,
    }),

    wordpressPlugin(),

    // Generate the theme.json file in the public/build/assets directory
    // based on the Tailwind config and the theme.json file from base theme folder
    wordpressThemeJson({
      disableTailwindColors: true,
      disableTailwindFonts: false,
      disableTailwindFontSizes: false,
    }),

    // Custom Plugins 
    paletteFromTheme({
      cssPath: 'resources/css/theme.css',
      themeJsonPath: 'public/build/assets/theme.json',
    }),
  ],
  resolve: {
    alias: {
      '@scripts': '/resources/ts',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },
  server: {
    host: '127.0.0.1',
    port: 5173,
    hmr: { host: '127.0.0.1', protocol: 'ws', port: 5173 },
  },
});
