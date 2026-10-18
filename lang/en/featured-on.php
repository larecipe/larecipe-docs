<?php

// The FeaturedOn strip on the Introduction: its HTML and its Markdown form for
// agents. The outlets and their links live in AppServiceProvider; their names
// stay as they are in every language.

return [
    'label' => 'Featured on',
    'count' => '3 million+',
    'downloads' => ':count downloads',
    'markdown' => 'LaRecipe has been downloaded more than 3 million times. Featured on :outlets.',
    'separator' => ', ',
    'last_separator' => ' and ',
];
