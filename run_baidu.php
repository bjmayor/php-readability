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
$start = 0;
$limit = 200;
$ret = getWenKuList($start,$limit);
while(true)
{
    if ($ret['status'] != 0)
    {
        die("error: status not 0");
    }
    $total = $ret['data']['totalCount'];
    $start += $limit;
    foreach($ret['data']['unitList']['records'] as $key=>$item)
    {
        $post_title = $item['title'];
        $post_date = $item['date'];
        $wrapContent = getPage($item['_key']);
        if(preg_match("/iframe src=\"([^\"]*)\"/i",$wrapContent,$match))
        {
            $page_content = getStaticPageContent(str_replace(";","&",$match[1]),$item['_key']);
            try {
                $parseContent = get_content($page_content);
            }
            catch(Exception $e)
            {
                echo "error:content can't parse";
                echo $post_title;
                continue;
            }
            $post_content = $parseContent['content'];
            postWp($post_title,$post_content,array(),$post_date);
        }
        else
        {
            echo "error: iframe no match";
            echo $post_title;
        }
        // $post_content = getPage
    }
    if ($start < $total)
    {
        $ret = getWenKuList($start,$limit);
    }
    else
    {
        echo "finish, all pages have done";
        break;
    }
}

    /*
    if($ret['title']!='' && $ret['content']!='')
    {
        var_dump($ret);
        //        postWp($ret['title'],$ret['content']);
    }
     */


function get_content($source)
{
    preg_match("/charset=([\w|\-]+);?/", $source, $match);
    $charset = isset($match[1]) ? $match[1] : 'utf-8';

    /**
     * 获取 HTML 内容后，解析主体内容
     */
    $Readability = new Readability($source, $charset);
    $Data = $Readability->getContent();
    return $Data;
}

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
        "dateCreated"=>new xmlrpcval(strtotime($pubDate),"dateTime.iso8601"),//发布时间，可不填，默认为当前时间。
        "categories"=>new xmlrpcval(array(new xmlrpcval("服务端开发","string")),"array")//分类信息,分类信息是需要已经存在的分类。
    ),
    "struct" );
    $req->addParam ( $struct ); 
    $req->addParam ( new xmlrpcval (1, 'int')); // 立即发布
    // 发送请求 
    $ans = $cl->send($req); 
    var_dump ( $ans );

}

function getWenKuList($start, $limit)
{
    //$url = "https://wenzhang.baidu.com/fav/list?start=$start&limit=$limit&wd=&type=&getListHtml=1&_=1471689227406";
    $url = "https://wenzhang.baidu.com/fav/list?start=$start&limit=$limit&wd=&type=&_=1471689227406";
    return json_decode(getWenKu($url),true);
}


function getPage($key)
{
    $url = "https://wenzhang.baidu.com/page/view?key=$key";
    return getWenKu($url);
}


function getWenKu($url)
{
    $cookie=<<<COOKIE
BAIDUID=D636A07F3707835E720636340A8A6D6B:FG=1; PSTM=1445339070; BIDUPSID=19EF1AE951B1A5A6F7BEB16C5508BF03; SIGNIN_UC=70a2711cf1d3d9b1a82d2f87d633bd8a02206141622; MCITY=-%3A; BDSFRCVID=igCsJeC626x3uFjR4eVpIGU_UmK5cV7TH6aoHftI7uNOIwO1hhWsEG0Pf3lQpYDbeqAmogKKLmOTHp5P; H_BDCLCKID_SF=tJ4q_K0bfIP3fP36qR6DMCCShUFs0xrT-2Q-5KL-MpFMqJOv04FW3MFPQPb20TD8Jm3wWMbdJJjohRDC2b6kKxFVDb5w2JtOyeTxoUJDbJRGqMTe-x6AeMCebPRiWPr9Qgbj_xtLtDt-MI0Re5A35n-Wql6aaD62aKDsBqro-hcqEIL42MCMKtte04JrLnjA3aTy0RCXJlvFDUbSj4QzD4oB2h6ZbfcatNrwWt8yyq5nhMJI54vGKhFv-46zqU6y523i2n6vQpn2MftuDjAKe5JBjaAs-bbfHjRHBRr2fIt2KROvhjRHXTkyyxomtjDJMJb4a4o43hr5Dq6DyJJ8DxnQ2t6ILUkqKm5dW43O5h-hjlc23h64-ttwQttjQP5qaN6jhlbtLhQWoJ7TyURdhf47yh0tQTIDfRCt_D0KJCvhDRTvhCTjh-FSMgTBKI62aKDsBR3n-hcqEIL4jRA55fKq0GO-LJOA3aTd-R6cJRO4MUbSj4Qoy-K3yHJtWfRAb28DBnn6-h5nhMJIDPvGKhFv-xoD34ry523i2n6vQpn2Mftu-n5jHjJLjNbP; SCMOBLE=00-00-00MF3Ohqe8zK; SCPA_IMAGEPLACE=ok2583851351IMAGEPLACE; H_PS_PSSID=1446_19033_13550_17001_12379_20857_20836_20884; cflag=15%3A3; BDUSS=Z2Z0ZxeX5SZkpPOXZLS2E2aX43cG1idEVLTkQwdDJadzFXSTNCQXNHTmwxTjlYQVFBQUFBJCQAAAAAAAAAAAEAAAA~mogGYmptYXlvcgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGVHuFdlR7hXOH; Hm_lvt_19f7e3b89626f41825e5c15696da95c5=1471666182,1471694695,1471694706; Hm_lpvt_19f7e3b89626f41825e5c15696da95c5=1471694706
COOKIE;
    $header = array("RA-Ver: 3.0.8","RA-Sid: 6A7837CF-20150807-043049-97899b-fe9d2e","Accept: application/json, text/javascript, */*; q=0.01","X-Requested-With: XMLHttpRequest","Accept-Language: zh-CN,zh;q=0.8");
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
        CURLOPT_REFERER => "https://wenzhang.baidu.com/",
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_SSL_VERIFYHOST => true,
        CURLOPT_VERBOSE => true
    ));

    $source = curl_exec($handle);
    curl_close($handle);
    return $source;
}


function getStaticPageContent($url,$key)
{ 
    $cookie=<<<COOKIE
Hm_lvt_19f7e3b89626f41825e5c15696da95c5=1471693997,1471694696,1471694706,1471694728; Hm_lpvt_19f7e3b89626f41825e5c15696da95c5=1471695400
COOKIE;
    $header = array("RA-Ver: 3.0.8","RA-Sid: 6A7837CF-20150807-043049-97899b-fe9d2e","Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8","X-Requested-With: XMLHttpRequest","Accept-Language: zh-CN,zh;q=0.8");
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


