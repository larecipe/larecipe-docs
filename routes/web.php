<?php

use App\Http\Controllers\SchemaController;
use App\Http\Controllers\SubscribeController;
use Illuminate\Support\Facades\Route;

// The docs own the domain root (config/larecipe.php mounts the set at ''),
// so this file only holds what the docs site adds around them.

Route::post('/newsletter/subscribe', SubscribeController::class)
    ->middleware('throttle:5,1')
    ->name('newsletter.subscribe');

// The same form as a plain page, for agents and readers without JavaScript.
Route::view('/newsletter', 'newsletter')->name('newsletter');

// LaRecipe 2 served its docs at /docs/{version}/{page}. Those pages now live
// in the 2.x version at the root, so old links and search results still land.
Route::permanentRedirect('/docs', '/');
Route::permanentRedirect('/docs/{version}', '/2.x');
Route::permanentRedirect('/docs/{version}/{page}', '/2.x/{page}');

// Every LaRecipe 3 manifest points its "$schema" at this domain
// (https://larecipe.saleem.dev/schemas/3.x/larecipe.json and friends).
Route::get('/schemas/3.x/{name}.json', SchemaController::class)
    ->whereIn('name', SchemaController::SCHEMAS)
    ->name('schemas');
