<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

function randomFloat($max)
{
    return mt_rand() / mt_getrandmax() * $max;
}

class VideoService
{
    public function allChannelIds(String $streamer)
    {
        $channels = DB::table('videos')
            ->select('channelId')
            ->where('streamer', $streamer)
            ->distinct()
            ->get();
        print_r("allChannelIds: " . $channels);
        return $channels;
    }

    public function randomVideos($streamer, $channels, $strategy, $count, $dateLow, $dateHigh)
    {
        if (!$channels || count($channels) === 0) {
            $channels = $this->allChannelIds($streamer);
        }

        $column = null;
        $key = null;
        switch ($strategy) {
            case 'by_duration':
                $column = $key = 'duration';
                break;
            case 'greatest_hits':
                $column = $key = 'viewCount';
                break;
            case 'hidden_gems':
                $column = '1.0 / viewCount AS viewCountInverse';
                $key = 'viewCountInverse';
                break;
            case 'by_video':
            default:
                break;
        }

        $select = $column ? "id, $column" : 'id';

        $videos = DB::table('videos')
            ->select(DB::raw($select))
            ->whereIn('channelId', $channels)
            ->whereBetween('publishedAt', [$dateLow, $dateHigh])
            ->get();

        $selectedVideos = $key ? $this->randByWeight($videos, $key, $count) : $this->randUniform($videos, $count);

        // get the full rows for the selected videos
        $selectedIds = array_map(function ($video) {
            return $video['id'];
        }, $selectedVideos);

        $selectedVideos = DB::table('videos')
            ->whereIn('id', $selectedIds)
            ->get()
            ->toArray();

        // for each selected video, add a `timestamp` field that is a random int between 0 and video.duration
        $selectedVideos = array_map(function ($video) {
            $video->timestamp = rand(0, $video->duration);
            return $video;
        }, $selectedVideos);

        return $selectedVideos;
    }

    // $videos is a model collection
    public function randByWeight($videos, $key, $count = 1)
    {
        $totalWeight = $videos->sum($key);

        $selected = [];
        $tempItems = $videos->toArray();

        for ($i = 0; $i < $count && count($tempItems) > 0; $i++) {
            $rand = randomFloat($totalWeight);
            $cumulativeWeight = 0;
            $selectedItem = null;
            foreach ($tempItems as $index => $item) {
                $itemArray = (array)$item;
                $cumulativeWeight += $itemArray[$key];
                if ($cumulativeWeight >= $rand) {
                    $selectedItem = $itemArray;
                    break;
                }
            }
            $selected[] = $selectedItem;
            $totalWeight -= $selectedItem[$key];
            unset($tempItems[$index]);
        }

        return $selected;
    }

    public function randUniform($videos, $count = 1)
    {
        $total = count($videos);
        $selected = [];
        $tempItems = $videos->toArray();

        for ($i = 0; $i < $count && count($tempItems) > 0; $i++) {
            $index = rand(0, $total - 1);
            $selected[] = (array)$tempItems[$index];
            unset($tempItems[$index]);
            $tempItems = array_values($tempItems);
            $total--;
        }

        return $selected;
    }
}
