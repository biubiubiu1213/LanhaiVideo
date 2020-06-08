<?php
declare (strict_types=1);

namespace LanhaiVideo\App\Tools;

use Smalls\VideoTools\Exception\ErrorVideoException;
use Smalls\VideoTools\Interfaces\IVideo;
use function LanhaiVideo\App\dump;

/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/4/27 - 0:46
 **/
class KuaiShou extends Base
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
            return ["{KuaiShou} url cannot be empty"];
        }

        if (strpos($url, "ziyang.m.kspkg.com") == false && strpos($url, "kuaishou.com") == false && strpos($url, "gifshow.com") == false && strpos($url, "chenzhongtech.com") == false) {
            return ["{KuaiShou} the URL must contain one of the domain names ziyang.m.kspkg.com or kuaishou.com or gifshow.com or chenzhongtech.com to continue execution"];
        }

        $contents = $this->http->get($url, [], [
            'User-Agent' => self::ANDROID_USER_AGENT,
            'cookie' => 'did=web_00536bb16309421a93a09c3e4998aa04; didv=' . time() . '000; clientid=3; client_key=6589' . rand(1000, 9999),
        ]);
//        dump($contents);
        preg_match('/data-pagedata="(.*?)"/i', $contents, $match);
        if ($this->checkEmptyMatch($match)) {
            return ["{KuaiShou} contents parsing failed"];
        }
        $contents = htmlspecialchars_decode($match[1]);
        $data = json_decode($contents, true);

        return $this->returnData(
            $url,
            isset($data['user']['name']) ? $data['user']['name'] : '',
            isset($data['user']['avatar']) ? $data['user']['avatar'] : '',
            isset($data['video']['caption']) ? $data['video']['caption'] : '',
            isset($data['video']['poster']) ? $data['video']['poster'] : '',
            isset($data['video']['srcNoMark']) ? $data['video']['srcNoMark'] : '',
            isset($data['video']['type']) ? $data['video']['type'] : 'video'
        );
    }
}
