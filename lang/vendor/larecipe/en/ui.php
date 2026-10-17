<?php

// LaRecipe's own site may use the kitchen word for a missing page
// (ART_DIRECTION.md): it is 86'd, off the menu. Customer docs keep the
// package's plain "Page not found". Only these lines change; every other
// UI string comes from the package.

return [
    'not_found' => [
        'title' => "86'd",
        'body' => 'That page is off the menu. There is no page at :path.',
        'suggestions' => 'Try one of these',
    ],
];
