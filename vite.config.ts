import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ command, mode }) => {
  const isDevBuild = command === "build" && mode === "development";

  return {
    appType: "custom",
    base: "",
    plugins: [tailwindcss()],
    build: {
      outDir: "dist",
      emptyOutDir: true,
      sourcemap: true,
      manifest: true,
      modulePreload: false,
      cssCodeSplit: true,
      minify: isDevBuild ? false : "esbuild",
      cssMinify: isDevBuild ? false : "esbuild",
      rollupOptions: {
        input: {
          index: "assets/ts/index.ts",
        },
        output: {
          entryFileNames: "js/[name]-[hash].js",
          chunkFileNames: "js/[name]-[hash].js",
          assetFileNames: (assetInfo) => {
            const candidates =
              assetInfo.names ?? assetInfo.originalFileNames ?? [];
            const first = candidates[0] ?? "";
            const base = first.split("?")[0];
            const ext = base.slice(base.lastIndexOf(".")).toLowerCase();

            if (ext === ".css") return "css/[name]-[hash][extname]";
            return "assets/[name]-[hash][extname]";
          },
        },
      },
    },
  };
});
