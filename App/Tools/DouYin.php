<?php
declare (strict_types=1);

namespace LanhaiVideo\App\Tools;


class DouYin extends Base
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
            return ["{DouYin} url cannot be empty"];
        }

        if (strpos($url, "douyin.com") == false && strpos($url, "iesdouyin.com") == false) {
            return ["{DouYin} the URL must contain one of the domain names douyin.com or iesdouyin.com to continue execution"];
        }

        $contents = $this->http->get($url, [], [
            'User-Agent' => self::ANDROID_USER_AGENT,
        ]);

        preg_match('/dytk:[^+]"(.*?)"[^+]}\);/i', $contents, $dyTks);
        preg_match('/itemId:[^+]"(.*?)",/i', $contents, $itemIds);

        if ($dyTks == null || $itemIds == null) {
            return ["{DouYin} dyTk or itemId is empty, there may be a problem with the website"];
        }

        $contents = $this->http->get('https://www.iesdouyin.com/web/api/v2/aweme/iteminfo', [
            'item_ids' => $itemIds[1],
            'dytk' => $dyTks[1],
        ], [
            'User-Agent' => self::ANDROID_USER_AGENT,
            'Referer' => "https://www.iesdouyin.com",
            'Host' => "www.iesdouyin.com",
        ]);

        if ((isset($contents['status_code']) && $contents['status_code'] != 0) || empty($contents['item_list'][0]['video']['play_addr']['uri'])) {
            return ["{DouYin} parsing failed"];
        }

        $videoUrl = $this->http->redirects('https://aweme.snssdk.com/aweme/v1/play/', [
            'video_id' => $contents['item_list'][0]['video']['play_addr']['uri'],
            'ratio' => '1080',
            'line' => '0',
        ], [
            'User-Agent' => self::ANDROID_USER_AGENT,
        ]);

        return $this->returnData(
            $url,
            isset($contents['item_list'][0]['author']['nickname']) ? $contents['item_list'][0]['author']['nickname'] : '',
            isset($contents['item_list'][0]['author']['avatar_larger']['url_list'][0]) ? $contents['item_list'][0]['author']['avatar_larger']['url_list'][0] : '',
            isset($contents['item_list'][0]['desc']) ? $contents['item_list'][0]['desc'] : '',
            isset($contents['item_list'][0]['video']['cover']['url_list'][0]) ? $contents['item_list'][0]['video']['cover']['url_list'][0] : '',
            $videoUrl,
            'video'
        );
    }
}
