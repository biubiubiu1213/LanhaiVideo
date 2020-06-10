<?php
declare (strict_types=1);

namespace LanhaiVideo\App\Tools;

use Smalls\VideoTools\Exception\ErrorVideoException;
use Smalls\VideoTools\Interfaces\IVideo;

class HuoShan extends Base
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
            return ["{HuoShan} url cannot be empty"];
        }

        if (strpos($url, "huoshan.com") == false) {
            return ["{HuoShan} the URL must contain one of the domain names huoshan.com to continue execution"];
        }

        $originalUrl = $this->http->redirects($url, [], [
            'User-Agent' => self::ANDROID_USER_AGENT,
        ]);

        preg_match('/item_id=([0-9]+)&tag/i', $originalUrl, $match);
        if ($this->checkEmptyMatch($match)) {
            return ["{HuoShan} originalUrl parsing failed"];
        }

        $contents = $this->http->get('https://share.huoshan.com/api/item/info', [
            'item_id' => $match[1]
        ], [
            'User-Agent' => self::ANDROID_USER_AGENT,
        ]);

        if ((isset($contents['status_code']) && $contents['status_code'] != 0) || (isset($contents['data']) && $contents['data'] == null)) {
            return ["{HuoShan} contents parsing failed"];
        }

        $videoUrl = isset($contents['data']['item_info']['url']) ? $contents['data']['item_info']['url'] : '';

        $parseUrl = parse_url($videoUrl);
        if(empty($parseUrl['query'])) {
            return ["{HuoShan} parseUrl parsing failed"];
        }
        parse_str($parseUrl['query'], $parseArr);
        $parseArr['watermark'] = 0;

        $videoUrl = $this->http->redirects('https://api.huoshan.com/hotsoon/item/video/_source/', $parseArr, [
            'User-Agent' => self::ANDROID_USER_AGENT,
        ]);

        return $this->returnData(
            $url,
            '',
            '',
            '',
            $contents['data']['item_info']['cover'],
            $videoUrl,
            'video'
        );
    }
}
