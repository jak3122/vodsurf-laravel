<?php

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Facades\Streamers;
use Illuminate\Support\Facades\Log;
use App\Services\VideoService;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/random', function (Request $request, VideoService $videoService) {
    $channels = $request->input('channels', []);
    $strategy = $request->input('strategy', 'by_duration');
    $count = $request->input('count');
    $dateLow = $request->input('dateLow', '1970-01-01');
    $dateHigh = $request->input('dateHigh', Carbon::now()->format('Y-m-d'));

    $streamers = app('streamers');

    // find the object in the streamers that matches the route
    // streamers is an array of objects
    $streamerConfig = collect($streamers)->firstWhere('route', $request->input('streamer'));

    if (!$streamerConfig) {
        $errMessage = "'streamer' is required. Supported streamers: " . implode(', ', app('streamers')::pluck('route')->toArray());
        return response()->json(['error' => $errMessage], 400);
    }

    if (empty($channels)) {
        $channels = $streamerConfig->channels->pluck('channelId')->toArray();
    }
    $channels = array_unique($channels);
    $channels = array_filter($channels, function ($channel) use ($streamerConfig) {
        return collect($streamerConfig['channels'])->contains('channelId', $channel);
    });

    $count = $count ? max(1, min(10, (int)$count)) : 1;

    $timerLabel = "[$dateLow - $dateHigh] $strategy, $count";

    Log::debug("");
    $startTime = microtime(true);

    $streamerRoute = $streamerConfig['route'];

    $videos = $videoService->randomVideos($streamerRoute, $channels, $strategy, $count, $dateLow, $dateHigh);

    $responseFields = [
        'channelId',
        'channelTitle',
        'duration',
        'publishedAt',
        'startSeconds',
        'videoId',
        'videoTitle',
        'viewCount',
    ];

    $response = array_map(function ($video) use ($responseFields) {
        return Arr::only((array)$video, $responseFields);
    }, (array)$videos);

    $endTime = microtime(true);
    Log::debug("$timerLabel: " . ($endTime - $startTime) . ' seconds');

    return response()->json($response);
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
