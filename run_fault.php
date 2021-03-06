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
//$start=6;
//$end=357;
//for ($i=$end;$i>=$start;$i--)
//{
//   if ($i==1)
//  {
// }
//else
// {
//   $listpage = "http://www.linuxidc.com/RedLinux/indexp$i.htm";
//}

main();
function main()
{
    $listpage = 'https://segmentfault.com/news/newest';
    $listcontent = file_get_contents($listpage);
    if(preg_match_all('~<a class="mr10" target="_blank"\s*?href="([^"]*)">([^<]*)</a>~',$listcontent,$matches)){
        $wxpages = array();
        for($i=0;$i<count($matches[0]);$i++)
        {
            $link = "https://segmentfault.com". $matches[1][$i];
            $title = $matches[2][$i];
            $request_url = getRealUrl($link);

            try {
                if(!recordUrl($request_url))
                {
                    continue;
                }
                $ret = get_content($request_url);
                $ret['title'] = $title;
                if($ret['title']!='' && $ret['content']!='')
                {
                    postWp($ret['title'],$ret['content'],"互联网研发",date("Y-m-d H:i:s"));
                }

            }
            catch(Exception $e)
            {
                echo "parse error". print_r($e,true);
            }


        }
    }
    else
    {
        echo "list no match\n";
    }
}

function getRealUrl($fault_url)
{
    $content = file_get_contents($fault_url);
    preg_match('~"([^"]*)"~',$content,$matches);
    return stripslashes($matches[1]);
}
//}


function get_content($request_url)
{

    //$request_url = getRequestParam("url",  "");
    $output_type = strtolower(getRequestParam("type", "html"));

    // 如果 URL 参数不正确，则跳转到首页
    if (!preg_match('/^https?:\/\//i', $request_url) ||
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

        return array("title"=>$title,"content"=>$content);
        //        include 'template/reader.html';
    }
}

?>
