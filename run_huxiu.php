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
$listpage= "https://www.huxiu.com/startups.html";
$listcontent = file_get_contents($listpage);
echo $listcontent;
if(preg_match_all('~<div class="mob-ctt">\s*<h2>\s*<a href="([^"]*)" class="transition msubstr-row2" target="_blank">([^<]*)</a>\s*</h2>~',$listcontent,$matches)) {
    $j=0;
    foreach($matches[0] as $item)
    {
        $date = date("Y-m-d H:i:s");
        $link= "https://www.huxiu.com".$matches[1][$j];
        $title = $matches[2][$j];
        $j++;
        if(recordUrl($link))
        {
            do_spider_to_wp($link,$title,$date);
        }
        else
        {
            echo "duplicated url\n";
        }
    }
}
else
{
    echo "list no match\n";
}


die("done");
function do_spider_to_wp($url,$title,$date)
{
    echo "do post url $url \n";
    return;
    $request_url = $url;
    try {
        $ret = get_content($request_url);
        if(preg_match('~<p label="大标题">(.*?)</p>(.*?)<span>~sm',$ret['content'],$matches))
        {
            $ret['title'] = $matches[1];
            $ret['content'] = $matches[2];
        }
        $ret['title'] = $title;
        if($ret['title']!='' && $ret['content']!='')
        {
            postWp($ret['title'],$ret['content'],"互联网观点热点",$date);
            sleep(1);
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
    if (!preg_match('/^https??:\/\//i', $request_url) ||
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

?>
