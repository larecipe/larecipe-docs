import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// @larecipe/vite builds our LaRecipe extension (the React side of the Subscribe
// component) into public/build/larecipe/. Until the package ships its build,
// the local stand-in with the same contract does the work.
const plugin = '@larecipe/vite';
const larecipe = await import(plugin)
    .then((module) => module.default)
    .catch(() => import('./resources/larecipe/vite-plugin.js').then((module) => module.default));

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        larecipe({ input: 'resources/larecipe/extension.tsx' }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
