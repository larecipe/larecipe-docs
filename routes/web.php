<?php

use App\Http\Controllers\SubscribeController;
use Illuminate\Support\Facades\Route;

// The docs own the domain root (config/larecipe.php mounts the set at ''),
// so this file only holds what the docs site adds around them.

Route::post('/newsletter/subscribe', SubscribeController::class)
    ->middleware('throttle:5,1')
    ->name('newsletter.subscribe');

// The same form as a plain page, for agents and readers without JavaScript.
Route::view('/newsletter', 'newsletter')->name('newsletter');
