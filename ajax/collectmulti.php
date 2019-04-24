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
if($action=="collectmulti"){
	$pid = !empty($_POST['pid']) ? $_POST['pid'] : '';$_SESSION["collectMultiForm_pid"]=$pid;
	$uid = !empty($_POST['uid']) ? $_POST['uid'] : '';$_SESSION["collectMultiForm_uid"]=$uid;
	$address = !empty($_POST['address']) ? $_POST['address'] : '';$_SESSION["collectMultiForm_address"]=$address;
	$coverimg = !empty($_POST['coverimg']) ? $_POST['coverimg'] : '';$_SESSION["collectMultiForm_coverimg"]=$coverimg;
	$coverimgattr = !empty($_POST['coverimgattr']) ? $_POST['coverimgattr'] : 'src';$_SESSION["collectMultiForm_coverimgattr"]=$coverimgattr;
	$character = !empty($_POST['character']) ? $_POST['character'] : 'utf-8';$_SESSION["collectMultiForm_character"]=$character;
	$container = !empty($_POST['container']) ? $_POST['container'] : '';$_SESSION["collectMultiForm_container"]=$container;
	$filter = !empty($_POST['filter']) ? $_POST['filter'] : '';$_SESSION["collectMultiForm_filter"]=$filter;
	$filterContent = !empty($_POST['filterContent']) ? $_POST['filterContent'] : '';$_SESSION["collectMultiForm_filterContent"]=$filterContent;
	$title = !empty($_POST['title']) ? $_POST['title'] : '';$_SESSION["collectMultiForm_title"]=$title;
	$titleattr = !empty($_POST['titleattr']) ? $_POST['titleattr'] : 'html';$_SESSION["collectMultiForm_titleattr"]=$titleattr;
	$titleprefix = !empty($_POST['titleprefix']) ? $_POST['titleprefix'] : '';$_SESSION["collectMultiForm_titleprefix"]=$titleprefix;
	$filterTitle = !empty($_POST['filterTitle']) ? $_POST['filterTitle'] : '';$_SESSION["collectMultiForm_filterTitle"]=$filterTitle;
	$content = !empty($_POST['content']) ? $_POST['content'] : '';$_SESSION["collectMultiForm_content"]=$content;
	
	if(empty($pid)||empty($address)||empty($container)||empty($title)||empty($content)){
		echo "请填写分类、采集网址、标题选择器、内容选择器、列表容器选择器，五者缺一不可";
		exit;
	}
	
	//采集某页面所有的图片
	$domainStart=strpos($address,'https://')+8;
	$domainEnd=strpos($address,'/',$domainStart);
	$domain=substr($address,0,$domainEnd);
	$params=array('var1' => 'testvalue', 'var2' => 'somevalue');
	$urls = QueryList::run('Request',array(
			'target' => $address,
			'referrer'=>$domain,
			'method' => 'GET',
			'params' => $params,
			'user_agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:21.0) Gecko/20100101 Firefox/21.0',
			'cookiePath' => './cookie.txt',
			'timeout' =>'60'
		))->setQuery(array('link' => array($container,'href',$filter,function($body){
		//利用回调函数补全相对链接
		global $domain;
		if(strpos($body,'http')===false){
			$link=$domain.$body;
		}else{
			$link=$body;
		}
		return $link;
	})),'','utf-8',$character)->getData(function($item){
		return $item['link'];
	});
	
	if(strpos($address,'youku.com')){
		$urls_youku=array();
		for($i=0;$i<count($urls);$i++){
			if($i%2==0){
				$urls_youku[$i]="http:".substr($urls[$i],21);
			}
		}
		$urls=$urls_youku;
	}
	//多线程扩展
	QueryList::run('Multi',array(
		'list' => $urls,
		'curl' => array(
			'opt' => array(
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_SSL_VERIFYHOST => false,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_AUTOREFERER => true,
					),
			//设置线程数
			'maxThread' => 100,
			//设置最大尝试数
			'maxTry' => 3
		),
		'success' => function($a){
			global $db,$address,$title,$titleattr,$titleprefix,$content,$pid,$uid,$filterContent,$filterTitle,$coverimg,$coverimgattr;
			if(strpos($address,'youku.com')){
				$html='value';$iframe='';
			}else{
				$html='html';$iframe='iframe';
			}
			//采集规则
			$reg = array(
				'coverimg' => array($coverimg,$coverimgattr),
				//采集文章标题
				'title' => array($title,$titleattr),
				//采集文章正文内容,利用过滤功能去掉文章中的超链接，但保留超链接的文字，并去掉版权、JS代码等无用信息
				'content' => array($content,$html,$filterContent),
				'embed' => array('embed','src'),
				'iframe' => array($iframe,'src')
				);
			$ql = QueryList::Query($a['content'],$reg,'');
			$data = $ql->getData();
			//处理内容
			if(empty($data[0]["title"])||empty($data[0]["content"])){
				return;
			}
			$filterTitleArr=explode(' ',$filterTitle);
			foreach($filterTitleArr as $key => $value){
				if(strpos($data[0]["title"],$value)){
					return;
				}
			}
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
					$content=preg_replace("/\<div\sid=\"youkuplayer\"[\w\W]*\>{1}(\<\/div\>){1}/", '<div id="youkuplayer" style="width:300px;height:300px;"></div>', $content);
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
			if(!empty($data[0]['coverimg'])){
				$content='<img src="'.$data[0]['coverimg'].'.jpg" alt="'.$titleSql.'" style="display:none;" />'.$content;
			}
			
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
	));
}
?>