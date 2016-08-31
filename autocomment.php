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
$targetSites = array("http://www.diyearn.net/");
$base_url = 'http://mp.weixin.qq.com/s?__biz=MjM5NzU0MzU0Nw==&mid=2651371524&idx=1&sn=13a99712e983cdef76170fff24fc11a6&scene=0#wechat_redirect';
$request_url = $base_url ;
try {
    $ret = get_content($request_url);
    if($ret['title']!='' && $ret['content']!='')
    {
        var_dump($ret);
        postWp($ret['title'],$ret['content']);
    }

}
catch(Exception $e)
{
}

function get_content($request_url)
{

    //$request_url = getRequestParam("url",  "");
    $output_type = strtolower(getRequestParam("type", "html"));

    // 如果 URL 参数不正确，则跳转到首页
    if (!preg_match('/^http:\/\//i', $request_url) ||
        !filter_var($request_url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            include 'template/index.html';
            exit;
        }

    $request_url_hash = md5($request_url);
    $request_url_cache_file = sprintf(DIR_CACHE."/%s.url", $request_url_hash);

    // 缓存请求数据，避免重复请求
    if (file_exists($request_url_cache_file) && 
        (time() - filemtime($request_url_cache_file) < CACHE_TIME)) {

            $source = file_get_contents($request_url_cache_file);
        } else {

            $handle = curl_init();
            curl_setopt_array($handle, array(
                CURLOPT_USERAGENT => USER_AGENT,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER  => false,
                CURLOPT_HTTPGET => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_URL => $request_url
            ));

            $source = curl_exec($handle);
            curl_close($handle);

            // Write request data into cache file.
            @file_put_contents($request_url_cache_file, $source);
        }

    // 判断编码
    //if (!$charset = mb_detect_encoding($source)) {
    //}
    preg_match("/charset=([\w|\-]+);?/", $source, $match);
    $charset = isset($match[1]) ? $match[1] : 'utf-8';

    /**
     * 获取 HTML 内容后，解析主体内容
     */
    $Readability = new Readability($source, $charset);
    $Data = $Readability->getContent();

    switch($output_type) {
    case 'json':
        header("Content-type: text/json;charset=utf-8");
        $Data['url'] = $request_url;
        echo json_encode($Data);
        break;

    case 'html': default:
        header("Content-type: text/html;charset=utf-8");
        $title   = $Data['title'];
        $content = $Data['content'];

        return array("title"=>$title,"content"=>$content);
        //        include 'template/reader.html';
    }
}

function postWp($title, $content, $categories, $pubDate)
{
    $xmlrpcurl='http://go2live.cn/xmlrpc.php';

    $blogid='1';
    $username='bjmayor';
    $password='blog951096';
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
        "categories"=>new xmlrpcval(array(new xmlrpcval("美文赏析","string")),"array")//分类信息,分类信息是需要已经存在的分类。
    ),
    "struct" );
    $req->addParam ( $struct ); 
    $req->addParam ( new xmlrpcval (1, 'int')); // 立即发布
    // 发送请求 
    $ans = $cl->send($req); 
    var_dump ( $ans );

}


function test()
{
    function getStaticPageContent($url,$key)
    {
        $header = array("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8","X-Requested-With: XMLHttpRequest","Accept-Language: zh-CN,zh;q=0.8");
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
            CURLOPT_REFERER => "https://wenzhang.baidu.com/page/view?key=$key",
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_SSL_VERIFYHOST => true,
            CURLOPT_VERBOSE => true
        ));

        $source = curl_exec($handle);
        curl_close($handle);
        return $source;

    }
}
