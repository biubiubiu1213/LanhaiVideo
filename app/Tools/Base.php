<?php


namespace LanhaiVideo\App\Tools;


class Base
{

    const WIN_USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36";
    const IOS_USER_AGENT = "Mozilla/5.0 (iPhone; CPU iPhone OS 10_3 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) CriOS/56.0.2924.75 Mobile/14E5239e Safari/602.1";
    const ANDROID_USER_AGENT = "Mozilla/5.0 (Linux; Android 8.0.0; Pixel 2 XL Build/OPD1.170816.004) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Mobile Safari/537.36";

    public $app = "LanhaiVideo\App";

    protected function returnData(string $url, string $userName, string $userHeadPic, string $desc, string $videoImage, string $videoUrl, string $type, string $itemIds): array
    {
        return [
            'md5' => md5($url),
            'message' => $url,
            'user_name' => $userName,
            'user_head_img' => $userHeadPic,
            'desc' => $desc,
            'img_url' => $videoImage,
            'video_url' => $videoUrl,
            'type' => $type,
            'itemIds'  =>  $itemIds
        ];
    }

    protected function checkEmptyMatch($match)
    {
        return $match == null || empty($match[1]) || empty($match);
    }
}
