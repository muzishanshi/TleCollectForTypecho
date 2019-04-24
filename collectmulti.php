<form id="collectMultiForm" action="" method="post" enctype="application/x-www-form-urlencoded">
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">采集到分类</label>
			<?php
			$queryCate= $db->select()->from('table.metas')->where("type=?","category")->where("parent=?",0);
			$rowCate = $db->fetchAll($queryCate);
			?>
			<select class="text" name="pid">
				<?php
				if(count($rowCate)>0){
					foreach($rowCate as $val){
						$queryCateSub= $db->select()->from('table.metas')->where("type=?","category")->where("parent=?",$val["mid"]);
						$rowCateSub = $db->fetchAll($queryCateSub);
						?>
						<option value="<?=$val["mid"];?>"<?php if($val["mid"]==@$_SESSION["collectMultiForm_pid"]){?> selected<?php }?><?php if(count($rowCateSub)>0){?> disabled<?php }?>><?=$val["name"];?></option>
						<?php
						if(count($rowCateSub)>0){
							foreach($rowCateSub as $valSub){
								?>
								<option value="<?=$valSub["mid"];?>"<?php if($valSub["mid"]==@$_SESSION["collectMultiForm_pid"]){?> selected<?php }?>><?=$valSub["name"];?></option>
								<?php
							}
						}
					}
				}else{
					?>
					<option value="0">暂无分类</option>
					<?php
				}
				?>
			</select>
		</li>
	</ul>
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">采集网址（必填）</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_address"];?>" name="address" />
		</li>
	</ul>
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">字符集</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_character"]?$_SESSION["collectMultiForm_character"]:"utf-8";?>" name="character" placeholder="可填utf-8、gbk等，默认为utf-8" />
		</li>
	</ul>
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">缩略图选择器</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_coverimg"];?>" name="coverimg" />
		</li>
	</ul>
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">缩略图属性</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_coverimgattr"];?>" name="coverimgattr" placeholder="可填src、_src、data-src等，默认为src"/>
		</li>
	</ul>
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">列表容器选择器（必填）</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_container"];?>" name="container" />
		</li>
	</ul>
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">列表容器过滤选择器</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_filter"];?>" name="filter" />
		</li>
	</ul>
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">标题选择器（必填）</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_title"];?>" name="title" />
		</li>
	</ul>
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">标题属性</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_titleattr"];?>" name="titleattr" placeholder="可填html、src、title、data-src等，默认为html" />
		</li>
	</ul>
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">标题前缀</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_titleprefix"];?>" name="titleprefix" placeholder="可填想要在标题前加的前缀词" />
		</li>
	</ul>
	
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">标题过滤用空格分隔</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_filterTitle"]?$_SESSION["collectMultiForm_filterTitle"]:"综艺 电影 剧集 动漫";?>" name="filterTitle" placeholder="综艺 电影 剧集 动漫" />
		</li>
	</ul>
	
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">内容选择器（必填）</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_content"];?>" name="content" />
		</li>
	</ul>
	<ul class="typecho-option">
		<li>
			<label class="typecho-label">内容过滤选择器</label>
			<input class="text" maxlength="255" type="text" value="<?=@$_SESSION["collectMultiForm_filterContent"];?>" name="filterContent" />
		</li>
	</ul>
	<ul class="typecho-option typecho-option-submit">
		<li>
			<input class="text" name="action" value="collectmulti" type="hidden" />
			<input class="text" name="uid" value="<?=Typecho_Cookie::get('__typecho_uid');?>" type="hidden" />
			<button type="submit" class="btn primary">开始多篇采集</button>
		</li>
	</ul>
</form>
<script type="text/javascript">
$(function(){
	$("#collectMultiForm").submit(function(){
		$.post("<?=$plug_url;?>/TleCollect/ajax/collectmulti.php",$("#collectMultiForm").serializeArray(),function(data){
			if(data!=""){
				alert(data);
			}else{
				alert("采集完成");
			}
		});
		return false;
	});
});
</script>