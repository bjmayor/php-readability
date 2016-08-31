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
    postWp($wpTitle,$wpContent);
}

    /*
    if($ret['title']!='' && $ret['content']!='')
    {
        var_dump($ret);
        //        postWp($ret['title'],$ret['content']);
    }
     */



function postWp($title, $content, $categories, $pubDate)
{

    if (!$title || !$content)
    {
        echo "param errer";
        var_dump(func_get_args());
        die();
    }
    $xmlrpcurl='http://go2live.cn/xmlrpc.php';

    $blogid='1';
    $users = array(
        array("name"=>"bjmayor","password"=>"blog951096"),
        array("name"=>"maynard","password"=>"wp123456"),
        array("name"=>"fenny","password"=>"Nl!zceEiiBV!51GwzMYNdL6c"),
        array("name"=>"stack","password"=>"Zfx0#0tX0cpIVotBfOoQ(yNr"),
        array("name"=>"shine","password"=>'TkMfjwI)NCf$UN5)kxuSOa1b'),
        array("name"=>"hellowo","password"=>"qO#rcv15I#xD5fHj(nHtj(1l"),
        array("name"=>"peace","password"=>"7nWmvvvqGsl#CnL^opav&Ck2"),
        array("name"=>"php","password"=>"EuwV!OVm%upmCEobPMBoTYIn"),
    );
    $user = $users[rand()%count($users)];
    $username=$user['name'];
    $password=$user['password'];
    $postTitle=$title;
    $postContent=$content;

    //$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';
    define ('DOMAIN', 'go2live.cn'); // 博客的域名 
    // 创建 xml-rpc client 
    $cl = new xmlrpc_client ( "/xmlrpc.php", DOMAIN, 80); 
    // 准备请求 
    $req = new xmlrpcmsg('metaWeblog.newPost'); 
    // 逐个列出请求的参数: 
    $req->addParam ( new xmlrpcval ( 1, 'int')); // 博客ID 
    $req->addParam ( new xmlrpcval ( $username, 'string' )); // 用户名 
    $req->addParam ( new xmlrpcval ( $password, 'string' )); // 密码 
    $struct = new xmlrpcval (
        array ( "title" => new xmlrpcval ( $postTitle, 'string' ), // 标题 
        "description" => new xmlrpcval ($postContent , 'string'), // 内容
        "post_type"=>new xmlrpcval("post",'string'),
        "post_status"=>new xmlrpcval("publish",'string'),
        "categories"=>new xmlrpcval(array(new xmlrpcval("杂文","string")),"array")//分类信息,分类信息是需要已经存在的分类。
    ),
    "struct" );
    $req->addParam ( $struct ); 
    $req->addParam ( new xmlrpcval (1, 'int')); // 立即发布
    // 发送请求 
    $ans = $cl->send($req); 
    var_dump ( $ans );

}





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

