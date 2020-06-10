<?php

namespace LanhaiVideo\app;

class Video
{
    private $url = '';
    private $http = '';
    private $proxy = '';

    public function __construct($url, $http, $proxy)
    {
        $this->url = $url;
        $this->http = $http;
        $this->proxy = $proxy;
    }

    public function check() {
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $this->url, $match);
        $url= $match[0][0];
        switch ($url) {
            case !!strpos($url, "douyin.com") || !!strpos($url, "iesdouyin.com") :
                $class = 'DouYin';
                break;
            case !!strpos($url, "ziyang.m.kspkg.com") || !!strpos($url, "kuaishou.com") ||
                !!strpos($url, "gifshow.com") || !!strpos($url, "chenzhongtech.com"):
                $class = 'KuaiShou';
                break;
            case !!strpos($url, "izuiyou.com"):
                $class = 'ZuiYou';
                break;
            case !!strpos($url, "huoshan.com"):
                $class = 'HuoShan';
                break;
            case !!strpos($url, "pipix.com"):
                $class = 'PiPiXia';
                break;
            case !!strpos($url, "toutiaoimg.com") || !!strpos($url, "toutiaoimg.cn"):
                $class = 'TouTiao';
                break;
            case !!strpos($url, "www.meipai.com"):
                $class = 'MeiPai';
                break;
            default:
                return '未检测出是哪个平台的链接';
                break;
        }
        $class = __NAMESPACE__ . '\\Tools\\' . $class;
        if (!class_exists($class)){
            return "类不存在$class";
        }
        $tools = new $class($this->http,$this->proxy);
        return $tools->start($url);
    }
}
