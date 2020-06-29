<?php
declare (strict_types=1);

namespace LanhaiVideo\App\Tools;


/**
 * Class DouYin
 * @package LanhaiVideo\App\Tools
 *
 */
class DouYin extends Base
{

    private $http;
    private $httpName;

    public function __construct($http,$proxy)
    {
        $class = $this->app . '\\Http\\' . $http;
        $this->http = new $class($proxy);
        $this->httpName = $http;
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

        $itemIds = null;
        if (strpos($url, "iesdouyin.com") === false) {
            $contents = $this->http->dyGet($url, [], [
                'User-Agent' => self::ANDROID_USER_AGENT,
            ]);
            preg_match('/\/video\/(.*?)\//', $contents, $itemIds);
        }else{
            preg_match('/\/video\/(.*?)\//', $url, $itemIds);
        }

        if ($itemIds == null) {
            return ["{DouYin} dyTk or itemId is empty, there may be a problem with the website"];
        }

        $contents = $this->http->get('https://www.iesdouyin.com/web/api/v2/aweme/iteminfo', [
            'item_ids' => $itemIds[1],
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
            'host' => 'aweme.snssdk.com'
        ]);
        return [
            'md5' => md5($url),
            'message' => $url,
            'user_name' => isset($contents['item_list'][0]['author']['nickname']) ? $contents['item_list'][0]['author']['nickname'] : '',
            'user_head_img' => isset($contents['item_list'][0]['author']['avatar_larger']['url_list'][0]) ? $contents['item_list'][0]['author']['avatar_larger']['url_list'][0] : '',
            'desc' => isset($contents['item_list'][0]['desc']) ? $contents['item_list'][0]['desc'] : '',
            'img_url' => isset($contents['item_list'][0]['video']['cover']['url_list'][0]) ? $contents['item_list'][0]['video']['cover']['url_list'][0] : '',
            'video_url' => $videoUrl,
            'type' => 'video',
            'itemIds'  =>  $itemIds[1],
            'music' =>  $contents['item_list'][0]['music']['play_url']['uri'] ?? '',
            'music_author'  =>  $contents['item_list'][0]['music']['title'] ?? '',
            'music_bg'  =>  $contents['item_list'][0]['music']['cover_thumb']['url_list'][0] ?? ''
        ];
    }

    public function dyCurl($url) {
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
            'Host'  =>  'aweme.snssdk.com'
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

    public function dyGuzzle($url) {
        if (empty($url)) {
            return ["{DouYin} url cannot be empty"];
        }

        if (!strpos($url, "iesdouyin.com")) { // 短链多处理一步
            $sortToLong = $this->http->dy_get($url);
            dump($sortToLong);
            preg_match('/href="(.*?)">Found/', $sortToLong, $newUrl);
            if (!$newUrl) {
                preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $sortToLong, $newUrl1);
                if (!$newUrl1) {
                    return ['error url'];
                }
                $url= $newUrl1[0][0];
            } else {
                $url = $newUrl[1];
            }
        }
        dump($url);
        $item_id = $this->zz_item_id($this->get_content_url($url));
        $contents = $this->http->get('https://www.iesdouyin.com/web/api/v2/aweme/iteminfo', [
            'item_ids' => $item_id,
        ]);
        $url = $contents['item_list'][0]['video']['play_addr']['url_list'][0];
        $url = str_replace('playwm','play',$url);
        $getUrl = $this->http->dy_get($url, [], [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4',
        ]);
        preg_match('/href="(.*?)">Found/', $getUrl, $matches);
        return $this->returnData(
            $url,
            isset($contents['item_list'][0]['author']['nickname']) ? $contents['item_list'][0]['author']['nickname'] : '',
            isset($contents['item_list'][0]['author']['avatar_larger']['url_list'][0]) ? $contents['item_list'][0]['author']['avatar_larger']['url_list'][0] : '',
            isset($contents['item_list'][0]['desc']) ? $contents['item_list'][0]['desc'] : '',
            isset($contents['item_list'][0]['video']['cover']['url_list'][0]) ? $contents['item_list'][0]['video']['cover']['url_list'][0] : '',
            $matches[1],
            'video'
        );
    }
    function zz_item_id($content){
        preg_match('/itemId: "(.*?)",/', $content, $matches);
        $res = $matches[1];
        return $res;
    }
    function get_content_url($url) {
        $ch = curl_init();
        //设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'
        ));
        curl_setopt($ch, CURLOPT_URL, $url);
        //启用时会将头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_HEADER, False);
        //设置为FALSE 禁止 cURL 验证对等证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, False);
        //获取页面内容 不直接输出到页面
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //设置最大超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
        $result = curl_exec($ch);
        return $result;
    }
}
