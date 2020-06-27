<?php

namespace LanhaiVideo\App;

include 'vendor/autoload.php';

$url = [
//    'https://v.douyin.com/JeHmpxv/',
    'https://www.iesdouyin.com/share/video/6835887275902078221/?region=CN&mid=6835887294767958791&u_code=0&titleType=title',
//    'https://v.kuaishou.com/78fmkt',
//    'https://share.huoshan.com/hotsoon/s/BBc31YnVU88/',
//    'https://h5.pipix.com/s/JeHuQmP/',
//    'https://m.toutiaoimg.cn/a6833270728943469059/?app=news_article&is_hit_share_recommend=0',
//    'https://share.izuiyou.com/detail/174681128?zy_to=applink&to=applink',
//    'http://www.meipai.com/media/1204538229?client_id=1089857302&utm_media_id=1204538229&utm_source=meipai_share&utm_term=meipai_android&gid=2254440015',
];

foreach ($url as $item) {
    $proxy = file_get_contents('http://t.11jsq.com/index.php/api/entry?method=proxyServer.generate_api_url&packid=0&fa=0&fetch_key=&groupid=0&qty=1&time=1&pro=&city=&port=1&format=txt&ss=1&css=&dt=1&specialTxt=3&specialJson=&usertype=15');
    $proxy = trim($proxy,"\r");
    $proxy = trim($proxy,"\n");
    $proxy = explode(':',$proxy);
    $proxy = ['ip' => $proxy[0], 'port' => $proxy[1]];
    $proxy = [];


//    echo "GuzzleHttp：$item";
//    $res = (new Video($item,'GuzzleHttp',$proxy))->check();
//    var_dump($res);

    echo "CurlHttp：$item";
    $res = (new Video($item,'CurlHttp',$proxy))->check();
    var_dump($res);
}
