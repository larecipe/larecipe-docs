import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import larecipe from '@larecipe/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        // Builds our LaRecipe extension (the React side of the Subscribe component)
        // into public/build/larecipe/, which LaRecipe loads on every docs page.
        larecipe({ input: 'resources/larecipe/extension.tsx' }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
