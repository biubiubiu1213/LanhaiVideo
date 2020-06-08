<?php

namespace LanhaiVideo\App;

include 'vendor/autoload.php';

$url = [
    'https://v.douyin.com/JeHmpxv/',
    'https://v.kuaishou.com/78fmkt',
    'https://share.huoshan.com/hotsoon/s/BBc31YnVU88/',
    'https://h5.pipix.com/s/JeHuQmP/',
    'https://m.toutiaoimg.cn/a6833270728943469059/?app=news_article&is_hit_share_recommend=0',
    'https://share.izuiyou.com/detail/174681128?zy_to=applink&to=applink',
    'http://www.meipai.com/media/1204538229?client_id=1089857302&utm_media_id=1204538229&utm_source=meipai_share&utm_term=meipai_android&gid=2254440015',
];


$proxy = file_get_contents('http://dynamic.goubanjia.com/dynamic/get/86d29e1c02af8acea38baba02b2a277f.html?sep=3');
$proxy = trim($proxy,"\r");
$proxy = trim($proxy,"\n");
$proxy = explode(':',$proxy);
$proxy = ['ip' => $proxy[0], 'port' => $proxy[1]];
$proxy = [];
foreach ($url as $item) {
//    $res = (new Video($item,'CurlHttp',$proxy))->check();
    $res = (new Video($item,'GuzzleHttp',$proxy))->check();
    var_dump($res);
}

function dump($param){
    var_dump($param);die;
}
