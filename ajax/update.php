<?php
$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=="update"){
	//版本检测
	$version = isset($_POST['version']) ? addslashes($_POST['version']) : '';
	$version=file_get_contents('https://www.tongleer.com/api/interface/TleCollect.php?action=updateTypecho&version='.$version);
	echo $version;
}else if($action=="help"){
	$version=file_get_contents('https://www.tongleer.com/api/interface/TleCollect.php?action=help&version=1');
	echo $version;
}
?>