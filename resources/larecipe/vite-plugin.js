import { resolve } from 'node:path'
import { build } from 'vite'

/**
 * A stand-in for @larecipe/vite while the package's own build of it is not
 * released: the same contract, so switching is a one-line change.
 *
 * It builds resources/larecipe/extension.tsx into public/build/larecipe/ as an
 * ES module with a manifest. react, react-dom and @larecipe/ui stay external:
 * the docs page's import map resolves them to LaRecipe's own copies, so there
 * is exactly one React on the page.
 */
const SHARED = ['react', 'react/jsx-runtime', 'react/jsx-dev-runtime', 'react-dom', 'react-dom/client', '@larecipe/ui']

export function buildExtension({ root = process.cwd(), input = 'resources/larecipe/extension.tsx', outDir = 'public/build/larecipe', mode = 'production', watch = false, logLevel = 'info' } = {}) {
  const production = mode === 'production'

  return build({
    configFile: false,
    root,
    mode,
    logLevel,
    clearScreen: false,
    define: { 'process.env.NODE_ENV': JSON.stringify(production ? 'production' : 'development') },
    build: {
      outDir: resolve(root, outDir),
      emptyOutDir: true,
      manifest: 'manifest.json',
      target: 'es2022',
      minify: production,
      copyPublicDir: false,
      watch: watch ? {} : null,
      rollupOptions: {
        input: { extension: resolve(root, input) },
        external: (id) => SHARED.includes(id),
        preserveEntrySignatures: 'allow-extension',
        output: {
          format: 'es',
          entryFileNames: '[name]-[hash].js',
          chunkFileNames: '[name]-[hash].js',
          assetFileNames: '[name]-[hash][extname]',
        },
      },
    },
  })
}

export default function larecipe(options = {}) {
  let config

  return {
    name: 'larecipe',
    enforce: 'post',
    configResolved(resolved) {
      config = resolved
    },
    async closeBundle() {
      if (!config || config.command !== 'build' || config.build.ssr) return
      await buildExtension({ ...options, root: options.root ?? config.root, mode: config.mode, logLevel: config.logLevel })
    },
  }
}
