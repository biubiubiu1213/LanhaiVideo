<?php
declare (strict_types=1);

namespace LanhaiVideo\App\Tools;
/**
 * Created By 1
 * Author：smalls
 * Email：smalls0098@gmail.com
 * Date：2020/4/26 - 21:57
 **/

use Smalls\VideoTools\Exception\ErrorVideoException;
use Smalls\VideoTools\Interfaces\IVideo;

class MeiPai extends Base
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
            return ["{MeiPai} url cannot be empty"];
        }

        if (strpos($url, "www.meipai.com") == false) {
            return ["{MeiPai} the URL must contain one of the domain names www.meipai.com to continue execution"];
        }

        $contents = $this->http->get($url, [], [
            'User-Agent' => self::WIN_USER_AGENT,
        ]);
        preg_match('/<meta content="(.*?)" property="og:video:url" \/>/i', $contents, $videoMatches);
        preg_match('/<img src="(.*?)" width="74" height="74" class="avatar pa detail-avatar" alt="(.*?)">/i', $contents, $userInfoMatches);

        preg_match('/<meta content="(.*?)" property="og:image" \/>/i', $contents, $videoImageMatches);
        preg_match('/<title>(.*?)<\/title>/i', $contents, $titleMatches);

        if ($this->checkEmptyMatch($videoMatches) || $this->checkEmptyMatch($userInfoMatches) || $this->checkEmptyMatch($videoImageMatches) || $this->checkEmptyMatch($titleMatches)) {
            return ["{MeiPai} parsing failed"];
        }

        $hex = $this->getHex($videoMatches[1]);
        $arr = $this->getDec($hex[0]);
        $d = $this->subStr($arr[0], $hex[1]);
        $videoUrl = 'https:' . base64_decode($this->subStr(self::getPos($d, $arr[1]), $d));

        return $this->returnData(
            $url,
            $userInfoMatches[2],
            $userInfoMatches[1],
            $titleMatches[1],
            $videoImageMatches[1],
            $videoUrl,
            'video'
        );
    }

    private function getHex($base64)
    {
        return [
            strrev(substr($base64, 0, 4)),
            substr($base64, 4)
        ];
    }

    private function getDec($str)
    {
        $a_arr = [];
        $b_arr = [];
        foreach (str_split((string)hexdec($str)) as $id => $v) {
            if ($id >= 2) {
                $b_arr[] = $v;
            } else {
                $a_arr[] = $v;
            }
        }
        return array($a_arr, $b_arr);
    }

    private function subStr($arr, $hex)
    {
        $k = $arr[0];
        $c = substr($hex, 0, (int)$k);
        $temp = str_replace(substr($hex, (int)$k, (int)$arr[1]), "", substr($hex, (int)$arr[0]));
        return $c . $temp;
    }

    private function getPos($a, $b)
    {
        $b[0] = (string)(strlen($a) - $b[0] - $b[1]);
        return $b;
    }


}
