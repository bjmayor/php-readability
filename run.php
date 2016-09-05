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
$base_url = 'http://www.meiwendays.com/abc';
if(($start=getValue($base_url))==Null)
{
    $start= 3328;
}
$end = $start;
$homepage = file_get_contents("http://www.meiwendays.com");
if(preg_match_all('~<a href="/abc(\d+)">~',$homepage,$matches))
{
    rsort($matches[1]); 
    $end = $matches[1][0]; 
}

for ($pageid=$start;$pageid<=$end;$pageid++)
{
    $request_url = $base_url . $pageid;
    echo "deal with url : $request_url \n";
    try {
        $ret = get_content($request_url);
        if($ret['title']!='' && $ret['content']!='')
        {
            if(recordUrl($request_url))
            {
                postWp($ret['title'],$ret['content'],'美文赏析',date('Y-m-d H:i:s',time()));
            }
            else
            {
                echo "duplicated url\n";
            }
        }

    }
    catch(Exception $e)
    {
        echo "parse error\n";
    }
    sleep(1);
}
updateValue($base_url,$end+1);

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


