<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use LaRecipe\Domains\Delivery\Support\Assets;

/**
 * The tags that load our LaRecipe extension (resources/larecipe/extension.tsx,
 * built into public/build/larecipe/ by `npm run build`).
 *
 * The page's import map, which LaRecipe prints before this, resolves react,
 * react-dom and @larecipe/ui to LaRecipe's own copies, so the extension shares
 * the one React on the page. Newer LaRecipe builds find public/build/larecipe/
 * themselves; when they do, this prints nothing.
 */
final class LaRecipeExtension
{
    public const DIRECTORY = 'build/larecipe';

    public static function tags(): HtmlString
    {
        if (defined(Assets::class.'::EXTENSION_DIRECTORY')) {
            return new HtmlString('');
        }

        $manifest = public_path(self::DIRECTORY.'/manifest.json');
        $chunks = is_file($manifest) ? json_decode((string) file_get_contents($manifest), true) : null;

        foreach ((array) $chunks as $chunk) {
            if (($chunk['isEntry'] ?? false) && ($chunk['name'] ?? null) === 'extension') {
                $url = fn (string $file) => e(asset(self::DIRECTORY.'/'.$file));
                $tags = array_map(fn (string $css) => '<link rel="stylesheet" href="'.$url($css).'">', $chunk['css'] ?? []);
                $tags[] = '<script type="module" src="'.$url($chunk['file']).'" data-lr-extension></script>';

                return new HtmlString(implode("\n", $tags));
            }
        }

        return new HtmlString('');
    }
}
