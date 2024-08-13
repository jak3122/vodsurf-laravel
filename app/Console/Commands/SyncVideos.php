<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\YouTubeService;
use App\Models\Video;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class SyncVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(YouTubeService $youtubeService)
    {
        $streamers = app('streamers');
        foreach ($streamers as $streamer) {
            $this->syncStreamer($youtubeService, $streamer);
        }
    }

    protected function syncStreamer(YouTubeService $youtubeService, $streamer)
    {
        print_r($streamer['route'] . "\n");
        $channels = $streamer['channels'];
        foreach ($channels as $channel) {
            $this->syncChannel($youtubeService, $channel, $streamer);
        }
    }

    protected function syncChannel(YouTubeService $youtubeService, $channel, $streamer)
    {
        $username = $channel['username'];
        $channelId = $channel['channelId'];
        // get the video title filter function, defaulting to a function that returns true
        $videoTitleFilter = $channel['videoTitleFilter'] ?? function ($title) {
            return true;
        };

        $channelDetails = $youtubeService->getChannelDetails($username, $channelId);
        // get the date of the most recent video in the videos table
        $mostRecentVideo = Video::where('channelId', $channelId)->orderBy('publishedAt', 'desc')->first();
        print_r("processing $username (most recent: $mostRecentVideo?->publishedAt)\n");

        $pageToken = null;
        $addedVideos = 0;

        while (true) {
            [$videos, $nextPageToken] = array_values($youtubeService->getChannelVideos($channelDetails, $pageToken));
            print_r($videos);

            // filter out videos with a zero or non-numeric viewCount,
            // and based on videoTitleFilter
            $filteredVideos = array_filter($videos, function ($video) use ($videoTitleFilter) {
                return $video['viewCount'] && is_numeric($video['viewCount']) && $videoTitleFilter($video['videoTitle']);
            });
            // attach streamer to each video
            foreach ($filteredVideos as &$video) {
                $video['streamer'] = $streamer['route'];
            }

            // Update database with the filtered videos and get number of rows added
            $rowsAdded = DB::table('videos')->upsert(
                $filteredVideos,
                ['videoId'],
                ['channelId', 'videoTitle', 'publishedAt', 'viewCount', 'duration']
            );

            $addedVideos += $rowsAdded;
            $pageToken = $nextPageToken;

            // Stop criteria
            if ($addedVideos < 50) {
                break;
            }
        }
    }
}
