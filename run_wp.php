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
$start = $argv[1];
$end = $argv[2];
for($i=$start;$i<$end;$i++)
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
#menu-1=%23tab_menu-1-uninstall; #menu-2=%23tab_menu-2-other; connect-main-navigation=%23menu-5; #menu-5=%23tab_menu-5-blog; wordpress_1aa286195a3f69e7c905af65e1ae1d0e=bjmayor%7C1474477255%7CXjkpV9x410bEh45SNp08RYf0KKxcfVDQeysiuTS2JKL%7C1c0aac553cea05708773b47c527463dbd84201f99930163e90689558347b5a07; __utma=222073358.1486908345.1467124568.1471601904.1471604469.18; __utmz=222073358.1470852190.12.3.utmcsr=analytics_test|utmccn=(not%20set)|utmcmd=referral; comment_author_1aa286195a3f69e7c905af65e1ae1d0e=bjmayor; comment_author_email_1aa286195a3f69e7c905af65e1ae1d0e=415074476%40qq.com; comment_author_url_1aa286195a3f69e7c905af65e1ae1d0e=http%3A%2F%2Fgo2live.cn; bdshare_firstime=1471682389760; wp-settings-3284=mfold%3Df; wp-settings-time-3284=1471715593; PHPSESSID=hpds4q6hr4bn3kss98nbmpff16; CNZZDATA30071149=cnzz_eid%3D259127962-1472836790-%26ntime%3D1472836790; Hm_lvt_c6606387d2c50c3e48bfaddf39ea2abe=1472837273; Hm_lpvt_c6606387d2c50c3e48bfaddf39ea2abe=1472837273; _ga=GA1.2.1486908345.1467124568; jiathis_rdc=%7B%22http%3A//go2live.cn/%22%3A0%7C1472918320892%2C%22http%3A//go2live.cn/wp-admin/options-general.php%3Fpage%3Dwpsupercache%22%3A%220%7C1472918332867%22%7D; wp-settings-1=editor%3Dtinymce%26imgsize%3Dfull%26align%3Dcenter%26hidetb%3D1%26m6%3Do%26m1%3Do%26m9%3Dc%26m5%3Do%26libraryContent%3Dbrowse%26mfold%3Do%26advImgDetails%3Dshow%26post_dfw%3Doff; wp-settings-time-1=1473120018; wordpress_test_cookie=WP+Cookie+check; wordpress_logged_in_1aa286195a3f69e7c905af65e1ae1d0e=bjmayor%7C1474477255%7CXjkpV9x410bEh45SNp08RYf0KKxcfVDQeysiuTS2JKL%7C8ad0a31c32994e1ad1d8694499805f6316ea0b04fa2ea94bca3d85e9f2458e2c
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
