<?php
include 'common.php';
include 'header.php';
include 'menu.php';

define('TLECOLLECT_VERSION', 1);
$options = Typecho_Widget::widget('Widget_Options');
$plug_url = $options->pluginUrl;
?>

<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main manage-metas">
                <div class="col-mb-12">
                    <ul class="typecho-option-tabs clearfix">
                        <li class="current"><a href="<?php $options->adminUrl('extending.php?panel=TleCollect%2Fcollect.php'); ?>"><?php _e('采集'); ?></a></li>
						<li><a href="https://www.tongleer.com" title="如果你喜欢，可以点击快速添加寒泥的博客。"><?php _e('官网'); ?></a></li>
                        <li><a href="javascript:getHelp();" title="" target="_blank"><?php _e('帮助'); ?></a></li>
						<li><a href="javascript:getUpdate();" title="" target="_blank"><?php _e('检查'); ?></a></li>
                    </ul>
                </div>

                <div class="col-mb-12 col-tb-6" role="main">                  
                    <?php include "collectsingle.php";?>
				</div>
                <div class="col-mb-12 col-tb-6" role="form">
                    <?php //include "collectmulti.php";?>
					<h3>关于插件：</h3>
					<p>本插件由<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=diamond0422@qq.com" target="_blank">二呆</a>因兴趣将以前的功能开发成插件，仅供娱乐之用，不确保所有网站都能采集，但填写好网站源代码中适合的选择器后可正常采集一般网站。</p>
					<h3>关于多篇采集：</h3>
					<p>
						因以前这些采集功能代码不知为何多篇也只能采集一篇，未做研究，现已屏蔽多篇采集，如果您有心修改，请打开插件目录下collect.php中将&lt;?php //include "collectmulti.php";?>的注释打开即可开始修改。
					</p>
					<h3>选择器推荐（仅供参考）：</h3>
					<table border="1">
						<tr>
							<th>平台</th><th>标题选择器</th><th>标题属性</th><th>内容选择器</th><th>缩略图选择器</th><th>缩略图属性</th><th>列表容器选择器</th><th>列表容器过滤选择器</th><th>标题过滤用空格分隔</th>
						</tr>
						<tr>
							<td>优酷</td><td>#subtitle</td><td>title</td><td>#link4</td><td>.item-cover.current .cover img</td><td>_src</td><td>.box-video a</td><td>.info-list</td><td>综艺 电影 剧集 动漫</td>
						</tr>
						<tr>
							<td colspan="9" style="border-left:1px dashed #000;border-bottom:1px dashed #000;border-right:1px dashed #000;">
							选择器推荐示例仅供参考，如果对应网站有所更新，需要适当修改。<br />
							特别注意：<br />
							1、采集优酷视频频率不能过快，否则会被优酷限制采集，不过过一会即可重新采集。<br />
							2、采集文章和视频同理，不过依然要求自己到网页源代码寻找选择器，进行采集。<br />
							3、此采集功能需少量技术，而且不太人性化，如果不能满足需求，可选择其他采集插件，谢谢使用。
							</td>
						</tr>
					</table>
                </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<script type="text/javascript">
function getHelp(){
	$.post("<?=$plug_url;?>/TleCollect/ajax/update.php",{action:"help"},function(data){
		alert(data);
	});
}
function getUpdate(){
	$.post("<?=$plug_url;?>/TleCollect/ajax/update.php",{action:"update",version:<?=TLECOLLECT_VERSION;?>},function(data){
		alert(data);
	});
}
</script>
<?php include 'footer.php'; ?>
