<?php

namespace App\Http\Controllers;

use LaRecipe\Support\Paths;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * GET /schemas/3.x/{name}.json: the JSON Schemas every LaRecipe 3 doc set
 * points at in its "$schema" (https://larecipe.saleem.dev/schemas/3.x/...).
 *
 * This site is that domain, so editors and agents that follow the pointer
 * land here. The files come from the installed package, so they always match
 * what larecipe:validate checks.
 */
final class SchemaController
{
    public const SCHEMAS = ['larecipe', 'navigation', 'frontmatter', 'validate'];

    public function __invoke(string $name): BinaryFileResponse
    {
        abort_unless(in_array($name, self::SCHEMAS, true), 404);

        return response()->file(Paths::resources("schemas/{$name}.schema.json"), [
            'Content-Type' => 'application/schema+json',
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
