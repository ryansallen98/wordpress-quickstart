import { readFileSync, existsSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

function startCase(slug) {
  return slug.replace(/-/g, ' ').replace(/\b\w/g, (m) => m.toUpperCase());
}

function extractColorTokens(cssSource) {
  const tokens = [];
  // Match: --color-foo-bar: <value>;
  const re = /--color-([a-z0-9-]+)\s*:\s*([^;]+);/gi;
  let m;
  while ((m = re.exec(cssSource))) {
    const slug = m[1].trim();
    const color = m[2].trim();
    tokens.push({ slug, color, name: startCase(slug) });
  }
  return tokens;
}

export default function paletteFromTheme(options = {}) {
  const {
    cssPath = 'resources/css/theme.css',
    themeJsonPath = 'public/build/assets/theme.json',
  } = options;

  return {
    name: 'palette-from-theme',
    apply: 'build',
    enforce: 'post',
    writeBundle() {
      const cssFile = resolve(process.cwd(), cssPath);
      const jsonFile = resolve(process.cwd(), themeJsonPath);

      if (!existsSync(cssFile) || !existsSync(jsonFile)) return;

      const css = readFileSync(cssFile, 'utf8');
      const palette = extractColorTokens(css).map((t) => ({
        slug: t.slug,
        name: t.name,
        color: t.color,
      }));

      if (!palette.length) return;

      const raw = readFileSync(jsonFile, 'utf8');
      const data = JSON.parse(raw);

      // Ensure the settings path exists
      data.settings ||= {};
      data.settings.color ||= {};

      // Lock it down and inject only your palette
      data.settings.color.custom = false;
      data.settings.color.defaultPalette = false;
      data.settings.color.palette = palette;

      writeFileSync(jsonFile, JSON.stringify(data, null, 2));
      // eslint-disable-next-line no-console
      console.log(`✔ Injected ${palette.length} colors into ${themeJsonPath}`);
    },
  };
}
