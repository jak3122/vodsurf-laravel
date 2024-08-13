<?php

namespace App\Services;

use Alaouy\Youtube\Facades\Youtube;
use Carbon\Carbon;
use DateInterval;

class YoutubeService
{
    public function formatDateForSql($ytDate)
    {
        return Carbon::parse($ytDate)->format('Y-m-d H:i:s');
    }

    public function getChannelDetails($username = null, $channelId = null)
    {
        if ($channelId) {
            $channel = Youtube::getChannelById($channelId);
        } elseif ($username) {
            $channel = Youtube::getChannelByName($username);
        } else {
            throw new \Exception("Need username or channelId to get channel details");
        }

        return [
            'id' => $channel->id,
            'title' => $channel->snippet->title,
            'uploadsPlaylist' => $channel->contentDetails->relatedPlaylists->uploads,
            'viewCount' => $channel->statistics->viewCount,
        ];
    }

    public function getVideos($channel, $pageToken = null)
    {
        print_r($channel);
        $params = [
            'type' => 'video',
            'part' => 'id,snippet',
            'channelId' => $channel['id'],
            'maxResults' => 50,
        ];

        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $response = Youtube::getPlaylistItemsByPlaylistId($channel['uploadsPlaylist'], $params);
        print_r($response);

        return [
            'nextPageToken' => $response['info']['nextPageToken'] ?? null,
            'items' => collect($response['results'])->map(function ($item) {
                return [
                    'videoId' => $item->contentDetails->videoId,
                    'videoPublishedAt' => $item->snippet->publishedAt,
                ];
            })->toArray(),
        ];
    }

    public function getVideoData($videoIds)
    {
        $videos = Youtube::getVideoInfo($videoIds);

        return collect($videos)->map(function ($video) {
            $duration = new DateInterval($video->contentDetails->duration);
            $seconds = $duration->days * 86400 + $duration->h * 3600 + $duration->i * 60 + $duration->s;

            return [
                'videoId' => $video->id,
                'videoTitle' => $video->snippet->title,
                'viewCount' => $video->statistics->viewCount,
                'duration' => $seconds,
                'publishedAt' => $this->formatDateForSql($video->snippet->publishedAt),
            ];
        })->toArray();
    }

    public function getChannelVideos($channel, $pageToken = null)
    {
        $videos = $this->getVideos($channel, $pageToken);
        $videoIds = collect($videos['items'])->pluck('videoId')->toArray();
        $videoData = $this->getVideoData($videoIds);

        $videoData = collect($videoData)->map(function ($video) use ($channel) {
            return array_merge($video, [
                'channelId' => $channel['id'],
                'channelTitle' => $channel['title'],
            ]);
        })->toArray();

        return [
            'videos' => $videoData,
            'nextPageToken' => $videos['nextPageToken'],
        ];
    }
}
