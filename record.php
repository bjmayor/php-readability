<?php
global $localdbpath;
$localdbpath = "/home/work/php-readability/record.db";
function recordUrl($url)
{
	global $localdbpath;
    $db = new SQLite3($localdbpath);
    $result = $db->exec("insert into urlrecord(`url`,`datetime`) values('$url','".date('Y-m-d H:i:s',time())."')");
    return $result;
}

function updateValue($key,$value)
{ 
	global $localdbpath;
    $db = new SQLite3($localdbpath);
    $result = $db->exec("update kvstore set `value`='$value' where `key`='$key'");
    return $result;

}

function addValue($key,$value)
{
	global $localdbpath;
    $db = new SQLite3($localdbpath);
    $result = $db->exec("insert into kvstore(`key`,`value`) values('$key','$value')");
    return $result;
}

function getValue($key)
{
	global $localdbpath;
    $db = new SQLite3($localdbpath);
    $result = $db->querySingle("select * from kvstore where `key`='$key'",true);
    return $result['value'];
}

?>
