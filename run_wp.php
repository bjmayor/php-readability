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

$url = "http://go2live.cn/wp-admin/admin-ajax.php";
for($i=50000;$i<71400;$i++)
{
    echo getPlugin($url,$i);
}

function getPlugin($url,$postid)
{
    echo "postid:$postid, \n";
//    action=external_image_import_all_ajax&import_images_post=3616
    $data = array("action"=>"external_image_import_all_ajax","import_images_post"=>$postid);
    $post_str = http_build_query($data,"&");
    $cookie=<<<COOKIE
wordpress_1aa286195a3f69e7c905af65e1ae1d0e=bjmayor%7C1473647574%7ClltlrZWkPqpJBRuGICeMduBxvSBpIRuotNkoY6Cu8Tj%7C49ec90c7e99c6fd73262dfd9ea14c61aa21020b13d1230bb8fc0f84a0483979c; #menu-3=%23tab_menu-3-login; #menu-1=%23tab_menu-1-uninstall; connect-main-navigation=%23menu-2; #menu-2=%23tab_menu-2-settings; __utma=222073358.1486908345.1467124568.1471601904.1471604469.18; __utmz=222073358.1470852190.12.3.utmcsr=analytics_test|utmccn=(not%20set)|utmcmd=referral; comment_author_1aa286195a3f69e7c905af65e1ae1d0e=bjmayor; comment_author_email_1aa286195a3f69e7c905af65e1ae1d0e=415074476%40qq.com; comment_author_url_1aa286195a3f69e7c905af65e1ae1d0e=http%3A%2F%2Fgo2live.cn; bdshare_firstime=1471682389760; wp-settings-3284=mfold%3Df; wp-settings-time-3284=1471715593; sq_keyword_5798=fenng å¯å¤§è¾; sq_type=img; wordpress_test_cookie=WP+Cookie+check; wordpress_logged_in_1aa286195a3f69e7c905af65e1ae1d0e=bjmayor%7C1473647574%7ClltlrZWkPqpJBRuGICeMduBxvSBpIRuotNkoY6Cu8Tj%7Cc1497482095ef6208dae352851ee0efb5f5325a3da366bc4f4d878ffb6ea8b57; PHPSESSID=s1pmuqqojg8v1rvd7m41eth1k0; _ga=GA1.2.1486908345.1467124568; wp-settings-1=editor%3Dhtml%26imgsize%3Dfull%26align%3Dcenter%26hidetb%3D1%26m6%3Do%26m1%3Do%26m9%3Dc%26m5%3Do%26libraryContent%3Dbrowse%26mfold%3Do%26advImgDetails%3Dhide%26post_dfw%3Doff%26uploader%3D1;
COOKIE;
    $header = array("RA-Ver: 3.0.8","RA-Sid: 6A7837CF-20150807-043049-97899b-fe9d2e","Accept: application/json, text/javascript, */*; q=0.01","X-Requested-With: XMLHttpRequest","Accept-Language: zh-CN,zh;q=0.8");
    $handle = curl_init();
    curl_setopt_array($handle, array(
        CURLOPT_USERAGENT => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER  => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_URL => $url,
        CURLOPT_COOKIE => $cookie,
        CURLOPT_ENCODING => "gzip, deflate, sdch, br",
        CURLOPT_REFERER => "http://go2live.cn/wp-admin/upload.php?page=external_image",
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_POSTFIELDS => $post_str,
        CURLOPT_POST => 1,
    //    CURLOPT_VERBOSE => true
    ));

    $source = curl_exec($handle);
    curl_close($handle);
    return $source;
}
?>
