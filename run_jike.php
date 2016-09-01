<?php
// vim: set et sw=4 ts=4 sts=4 ft=php fdm=marker ff=unix fenc=utf8 nobomb:
/**
 * PHP Readability
 *
 * @author mingcheng<i.feelinglucky#gmail.com>
 * @date   2011-02-17
 * @link   http://www.gracecode.com/
 */

require 'config.inc.php';
require 'common.inc.php';
require 'lib/Readability.inc.php';
require 'lib/class-IXR.php';
require 'lib/plugin.php';
require 'lib/xmlrpc.inc';
require 'record.php';
require 'postWp.php';
//每天8点自动运行
$ret =  getSubscribed();
foreach($ret['data'] as $item)
{
    $topicId = $item['id'];
    $topicName = $item['content'];
    $topicLastPostTime = $item['lastMessagePostTime'];
    $topicPic = $item['pictureUrl'];
    $wpTitle = $topicName."【".date("Y-m-d",time())."汇总】";
    $wpContent = "<img src=\"$topicPic\" />";
    $wpContent .= "<ul>";
    //判断更新时间是否大于当天0点,以及条数是否够20。
    if(!recordUrl($topicId."_".$topicLastPostTime))
    {
        echo "$topicName not update, continue\n";
        continue;
    }
    $list = getHistory($topicId);
    foreach($list['data'] as $page)
    {
        $title = $page['content'];
        $link= $page['linkUrl'];
        $wpContent .= "<li><a href=\"$link\" target=\"_blank\">$title</a></li>";
    }
    $wpContent .="</ul>";
    $wpContent .="<p>查看更多好文，请点击<a href=\"http://go2live.cn\">http://go2live.cn</a></p>";
    echo "do post url, topic $topicName \n";
    postWp($wpTitle,$wpContent,'杂文',date('Y-m-d H:i:s',time()));
}

    /*
    if($ret['title']!='' && $ret['content']!='')
    {
        var_dump($ret);
        //        postWp($ret['title'],$ret['content']);
    }
     */








function getJike($url)
{
    $cookie=<<<COOKIE
jike:sess=eyJfdWlkIjoiNTc5ZjNiZTJmMjE3MmYxMzAwY2EwMGIwIiwiX3Nlc3Npb25Ub2tlbiI6Ik14YkVYMGsxRWpHdGcwcTNVS0UwdjFHdHoifQ==; jike:sess.sig=yrZ78nE-Z1XQ1I_p5xM5nGnX8I8
COOKIE;
    $header = array("Accept: application/json, text/javascript, */*; q=0.01","X-Requested-With: XMLHttpRequest","Accept-Language: zh-CN,zh;q=0.8");
    $handle = curl_init();
    curl_setopt_array($handle, array(
        CURLOPT_USERAGENT => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER  => false,
        CURLOPT_HTTPGET => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_URL => $url,
        CURLOPT_COOKIE => $cookie,
        CURLOPT_ENCODING => "gzip, deflate, sdch, br",
        //        CURLOPT_REFERER => "https://wenzhang.baidu.com/",
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_SSL_VERIFYHOST => true,
 //       CURLOPT_VERBOSE => true
    ));

    $source = curl_exec($handle);
    curl_close($handle);
    return $source;
}

function postJike($url,$data=array())
{
    $json_data = json_encode($data); 
    $cookie=<<<COOKIE
jike:sess=eyJfdWlkIjoiNTc5ZjNiZTJmMjE3MmYxMzAwY2EwMGIwIiwiX3Nlc3Npb25Ub2tlbiI6Ik14YkVYMGsxRWpHdGcwcTNVS0UwdjFHdHoifQ==; jike:sess.sig=yrZ78nE-Z1XQ1I_p5xM5nGnX8I8
COOKIE;
    $header = array("Accept: application/json, text/javascript, */*; q=0.01","X-Requested-With: XMLHttpRequest","Accept-Language: zh-CN,zh;q=0.8","Content-Type: application/json",'Content-Length: ' . strlen($json_data));
    $handle = curl_init();
    curl_setopt_array($handle, array(
        CURLOPT_USERAGENT => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER  => false,
        CURLOPT_HTTPGET => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_URL => $url,
        CURLOPT_COOKIE => $cookie,
        CURLOPT_ENCODING => "gzip, deflate, sdch, br",
        //        CURLOPT_REFERER => "https://wenzhang.baidu.com/",
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_SSL_VERIFYHOST => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS=>$json_data,
//        CURLOPT_VERBOSE => true
    ));

    $source = curl_exec($handle);
    curl_close($handle);
    return $source;
}



function getSubscribed()
{
    return json_decode(getJike("https://app.jike.ruguoapp.com/1.0/users/topics/listSubscribed?t=".time()."&limit=200&skip=0&sortBy=SUBSCRIBE_TIME"),true);
}

function getHistory($topicId)
{
    $data  = array("topic"=>$topicId,"limit"=>50);
    return json_decode(postJike("https://app.jike.ruguoapp.com/1.0/users/messages/history?t=".time(),$data),true);
}

