<?php
// vim: set et sw=4 ts=4 sts=4 ft=php fdm=marker ff=unix fenc=utf8 nobomb:
/**
 * PHP Readability
 *
 * @author mingcheng<i.feelinglucky#gmail.com>
 * @date   2011-02-17
 * @link   http://www.gracecode.com/
 */




function postWp($title, $content, $categories, $pubDate)
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
    $postContent=$content."<p>转载自演道,想查看更及时的互联网产品技术热点文章请点击<a href=\"http://go2live.cn\">http://go2live.cn</a></p>";

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
        "post_status"=>new xmlrpcval("publish",'string'),//publish为发布,draft为草稿
        "dateCreated"=>new xmlrpcval(strtotime($pubDate)-8*3600,"dateTime.iso8601"),//发布时间，可不填，默认为当前时间。
        "categories"=>new xmlrpcval(array(new xmlrpcval($categories,"string")),"array")//分类信息,分类信息是需要已经存在的分类。
    ),
    "struct" );
    $req->addParam ( $struct ); 
    $req->addParam ( new xmlrpcval (1, 'int')); // 立即发布
    // 发送请求 
    $ans = $cl->send($req); 
    var_dump ( $ans );

}


