<?php
declare (strict_types=1);

namespace LanhaiVideo\app\Tools;
/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/4/26 - 21:57
 **/

use Smalls\VideoTools\Exception\ErrorVideoException;
use Smalls\VideoTools\Interfaces\IVideo;

class PiPiXia extends Base
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
            return ["{PiPiXia} url cannot be empty"];
        }

        if (strpos($url, "pipix.com") == false) {
            return ["{PiPiXia} the URL must contain one of the domain names pipix.com to continue execution"];
        }

        $originalUrl = $this->http->redirects($url, [], [
            'User-Agent' => self::ANDROID_USER_AGENT,
        ]);

        preg_match('/item\/([0-9]+)\?/i', $originalUrl, $match);
        if ($this->checkEmptyMatch($match)) {
            return ["{PiPiXia} originalUrl parsing failed"];
        }
        $newGetContentsUrl = 'https://h5.pipix.com/bds/webapi/item/detail/';

        $contents = $this->http->get($newGetContentsUrl, [
            'item_id' => $match[1],
        ], [
            'Referer' => $newGetContentsUrl,
            'User-Agent' => self::ANDROID_USER_AGENT
        ]);

        if (empty($contents['data']['item'])) {
            return ["{TouTiao} contents parsing failed"];
        }
        $videoArr = $contents['data']['item'];
        return $this->returnData(
            $url,
            isset($videoArr['author']['name']) ? $videoArr['author']['name'] : '',
            isset($videoArr['author']['avatar']['url_list'][0]['url']) ? $videoArr['author']['avatar']['url_list'][0]['url'] : '',
            isset($videoArr['share']['title']) ? $videoArr['share']['title'] : '',
            isset($videoArr['video']['cover_image']['url_list'][0]['url']) ? $videoArr['video']['cover_image']['url_list'][0]['url'] : '',
            isset($videoArr['video']['video_fallback']['url_list'][0]['url']) ? $videoArr['video']['video_fallback']['url_list'][0]['url'] : '',
            'video'
        );
    }
}
