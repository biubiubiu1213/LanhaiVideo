<?php
declare (strict_types=1);

namespace LanhaiVideo\app\Tools;

use Smalls\VideoTools\Exception\ErrorVideoException;
use Smalls\VideoTools\Interfaces\IVideo;

/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/4/27 - 14:32
 **/
class TouTiao extends Base
{

    private $http;

    public function __construct($http,$proxy)
    {
        $class = $this->app . '\\Http\\' . $http;
        $this->http = new $class($proxy);
    }

    /**
     * @param string $url
     * @return array
     */
    public function start(string $url): array
    {
        if (empty($url)) {
            return ["{TouTiao} url cannot be empty"];
        }
        if (strpos($url, "toutiaoimg.com") == false && strpos($url, "toutiaoimg.cn") == false) {
            return ["{TouTiao} the URL must contain one of the domain names toutiaoimg.com or toutiaoimg.cn to continue execution"];
        }
        preg_match('/a([0-9]+)\/?/i', $url, $match);
        if ($this->checkEmptyMatch($match)) {
            return ["{TouTiao} url parsing failed"];
        }

        return $this->getContents($url, $match[1]);
    }


    protected function getContents(string $url, $videoId)
    {
        $getContentUrl = 'https://m.365yg.com/i' . $videoId . '/info/';

        $contents = $this->http->get($getContentUrl, ['i' => $videoId], [
            'Referer' => $getContentUrl,
            'User-Agent' => self::ANDROID_USER_AGENT
        ]);

        if (empty($contents['data']['video_id'])) {
            return ["contents parsing failed"];
        }

        $videoUrl = $this->http->redirects('http://hotsoon.snssdk.com/hotsoon/item/video/_playback/', [
            'video_id' => $contents['data']['video_id'],
        ], [
            'User-Agent' => self::ANDROID_USER_AGENT
        ]);

        return $this->returnData(
            $url,
            isset($contents['data']['media_user']['screen_name']) ? $contents['data']['media_user']['screen_name'] : '',
            isset($contents['data']['media_user']['avatar_url']) ? $contents['data']['media_user']['avatar_url'] : '',
            isset($contents['data']['title']) ? $contents['data']['title'] : '',
            isset($contents['data']['poster_url']) ? $contents['data']['poster_url'] : '',
            $videoUrl,
            'video'
        );

    }

}
