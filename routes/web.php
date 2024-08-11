<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/{streamer}', function ($streamer) {
    return Inertia::render('Streamer', [
        'stats' => [[
            'views' => 1000,
            'duration' => 1000,
            'videos' => 3,
            'channelId' => 'UCK3kaNXbB57CLcyhtccV_yw',
        ]]
    ]);
});
