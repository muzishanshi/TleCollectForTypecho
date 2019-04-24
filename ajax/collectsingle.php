<?php
session_start();
require '../collect/phpQuery/phpQuery.php';
require '../collect/QueryList/QueryList.php';
require '../collect/QueryList/Ext/AQuery.php';
require '../collect/QueryList/Ext/Request.php';
require '../collect/QueryList/Ext/Http.php';
require '../collect/QueryList/Ext/Multi.php';
require '../collect/QueryList/Ext/CurlMulti.php';
include '../../../../config.inc.php';

use QL\QueryList;

$db = Typecho_Db::get();

$action = !empty($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=="collectsingle"){
	$pid = !empty($_POST['pid']) ? $_POST['pid'] : '';$_SESSION["collectsingle_pid"]=$pid;
	$uid = !empty($_POST['uid']) ? $_POST['uid'] : '';$_SESSION["collectsingle_uid"]=$uid;
	$address = !empty($_POST['address']) ? $_POST['address'] : '';$_SESSION["collectsingle_address"]=$address;
	$character = !empty($_POST['character']) ? $_POST['character'] : 'utf-8';$_SESSION["collectsingle_character"]=$character;
	$coverimg = !empty($_POST['coverimg']) ? $_POST['coverimg'] : '';$_SESSION["collectsingle_coverimg"]=$coverimg;
	$coverimgattr = !empty($_POST['coverimgattr']) ? $_POST['coverimgattr'] : 'src';$_SESSION["collectsingle_coverimgattr"]=$coverimgattr;
	$title = !empty($_POST['title']) ? $_POST['title'] : '';$_SESSION["collectsingle_title"]=$title;
	$titleattr = !empty($_POST['titleattr']) ? $_POST['titleattr'] : 'html';$_SESSION["collectsingle_titleattr"]=$titleattr;
	$titleprefix = !empty($_POST['titleprefix']) ? $_POST['titleprefix'] : '';$_SESSION["collectsingle_titleprefix"]=$titleprefix;
	$content = !empty($_POST['content']) ? $_POST['content'] : '';$_SESSION["collectsingle_content"]=$content;
	$filterContent = !empty($_POST['filterContent']) ? $_POST['filterContent'] : '';$_SESSION["collectsingle_filterContent"]=$filterContent;
	
	if(empty($pid)||empty($address)||empty($title)||empty($content)){
		echo "请填写分类、采集网址、标题选择器、内容选择器，四者缺一不可";
		exit;
	}
	
	if(strpos($address,'youku.com')){
		$html='value';$iframe='';
	}else{
		$html='html';$iframe='iframe';
	}
	//采集某页面所有的图片
	$data = QueryList::Query($address,array(
		//采集规则库
		//'规则名' => array('jQuery选择器','要采集的属性'),
		'coverimg' => array($coverimg,$coverimgattr),
		'title' => array($title,$titleattr),
		'content' => array($content,$html,$filterContent),
		'embed' => array('embed','src'),
		'iframe' => array($iframe,'src')
		),'','utf-8',$character)->data;
	//echo "<pre>";var_dump($data);echo "</pre>";return;
	//优酷
	$content=str_replace('\'', '"', $data[0]["content"]);
	if(strpos($content,'client_id: "')){
		$temp=substr($content,strpos($content,'client_id: "')+12);
		$client_id=substr($temp,0,strpos($temp,'"'));
		$content=str_replace($client_id, $client_id, $content);
		if(strpos($content,'onPlayEnd')){
			$content=preg_replace("/onPlayEnd:\sfunction\(\)\{[\w\W]*;[^\}]*\}{1}/", 'onPlayEnd: function(){}', $content);
		}
		if(strpos($content,'youkuplayer')){
			$content=preg_replace("/\<div\sid=\"youkuplayer\"[\w\W]*\>{1}(\<\/div\>){1}/", '<div id="youkuplayer" style="width:50%;height:300px;margin:0 auto;"></div>', $content);
		}
	}else if(strpos($content,'<embed')!== false){
		$temp=str_replace('/v.swf', '', $data[0]["embed"]);
		$client_id=substr($temp,strrpos($temp,'/')+1);
		$content=str_replace($client_id, $client_id, $content);
	}else if(strpos($content,'<iframe')!== false){
		if(strpos($address,'youku.com')){
			$temp=str_replace('<iframe height=498 width=510 src="http://player.youku.com/embed/', '', $content);
			$video_id=substr($temp,0,strpos($temp,'"'));
			$content='<iframe height=498 width=100% src="http://player.youku.com/embed/'.$video_id.'" frameborder=0 allowfullscreen></iframe>';
		}else{
			$temp=strpos($data[0]["iframe"],'client_id=')+10;
			$client_id=substr($data[0]["iframe"],$temp);
			$content=str_replace($client_id, $client_id, $content);
		}
	}
	
	if(strpos($address,'youku.com')===false){
		$titleSql=$titleprefix.$data[0]["title"];
	}else{
		$titleSql=$data[0]["title"];
	}
	if($data[0]['coverimg']!=''){
		$content='<img src="'.$data[0]['coverimg'].'.jpg" alt="'.$titleSql.'" style="display:none;" />'.$content;
	}
	$content.='';
	
	$logData = array(
		'title' => $titleSql,
		'text' => "<!--markdown-->!!!\r\n".$content."\r\n!!!",
		'created' => time(),
		'authorId' => $uid,
		'type' => "post",
		'status' => "publish",
		'allowComment'=>1
	);
	$insert = $db->insert('table.contents')->rows($logData);
	$logId = $db->query($insert);
	
	$cateData = array(
		'cid' => $logId,
		'mid' => $pid
	);
	$insert = $db->insert('table.relationships')->rows($cateData);
	$db->query($insert);
	
	$db->query("UPDATE ".$db->getPrefix()."metas SET count=count+1 WHERE mid=".$pid);
}
?>