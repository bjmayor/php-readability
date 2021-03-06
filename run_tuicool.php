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
$listpage= "http://www.tuicool.com/ah/20?lang=1";
while (true)
{
    echo "listpage $listpage \n";
    $listcontent = file_get_contents($listpage);
echo $listcontent;
    if(preg_match('~<li class="next">\s*<a href="([^*]*)">下一页</a>~',$listcontent,$matches))
    {
        $listpage = "http://www.tuicool.com".$matches[1];
        if(preg_match_all('~<a href="/articles/([^"]*)" target="_blank" title="[^"]*">([^<]*)</a>~',$listcontent,$matches))
        {
            $j=0;
            foreach($matches[0] as $item)
            {
                $link = "http://www.tuicool.com/articles/".$matches[1][$j];
                $title= $matches[2][$j];
                $j++;
                do_spider_to_wp($link,"",$title);
            }
        }
        else
        {
            echo "list no match\n";
            break;
        }
    }
    else
    {
        echo "no match\n";
        break;
    }
}


die("done");
function do_spider_to_wp($url,$date,$title)
{
    echo "do post url $url \n";
    $request_url = $url;
    if ($date=="")
    {
        $date = date('Y-m-d H:i:s',mt_rand(strtotime("2008-8-5 00:00:00"),strtotime("2016-08-29 12:00:00")));
    }
    try {

        if (recordUrl($request_url))
        {
            $ret = get_content($request_url);
            $ret['title'] = $title;
            if($ret['title']!='' && $ret['content']!='')
            {
                echo "postWp url\n";
                postWp($ret['title'],$ret['content'],"互联网研发",$date);
                sleep(1);
            }
        }
        else
        {
            echo "duplicated url\n";
        }

    }
    catch(Exception $e)
    {
        echo "parse error";
    }

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
            //header("Content-type: text/html;charset=utf-8");
            $title   = $Data['title'];
            $content = $Data['content'];
            $title = substr($title,0,strpos($title,"_凤凰科技"));
            //        $content = str_replace('src="../../','src="http://www.linuxidc.com/',$Data['content']);
            //       $content = substr($content, 0,-290); 

            return array("title"=>$title,"content"=>$content);
            //        include 'template/reader.html';
    }
}

function postWp($title, $content, $categorie, $pubDate)
{
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
                "post_status"=>new xmlrpcval("post",'string'),//publish为发布,draft为草稿
                "dateCreated"=>new xmlrpcval(strtotime($pubDate),"dateTime.iso8601"),//发布时间，可不填，默认为当前时间。
                "categories"=>new xmlrpcval(array(new xmlrpcval($categorie,"string")),"array")//分类信息,分类信息是需要已经存在的分类。
                ),
            "struct" );
    $req->addParam ( $struct ); 
    $req->addParam ( new xmlrpcval (1, 'int')); // 立即发布
    // 发送请求 
    $ans = $cl->send($req); 
    //    var_dump ( $ans );

}

