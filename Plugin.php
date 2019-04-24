<?php
/**
 * TleCollectForTypecho采集插件由<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=diamond0422@qq.com" target="_blank" style="background: #000;padding: 2px 4px;color: #ffeb00;font-size: 12px;" title="同乐儿">二呆</a>因兴趣将以前的功能开发成插件，仅供学习、娱乐之用，不确保所有网站都能采集，但填写好网站源代码中适合的选择器后可正常采集一般网站。
 * @package TleCollect For Typecho
 * @author 二呆
 * @version 1.0.1
 * @link http://www.tongleer.com/
 * @date 2019-04-24
 */
class TleCollect_Plugin implements Typecho_Plugin_Interface{
    // 激活插件
    public static function activate(){
		Helper::addPanel(3, 'TleCollect/collect.php', '采集', 'TleCollect采集', 'administrator');
		Helper::addAction('collect', 'TleCollect_Plugin');
        return _t('插件已经激活');
    }

    // 禁用插件
    public static function deactivate(){
		Helper::removeAction('collect');
		Helper::removePanel(3, 'TleCollect/collect.php');
        return _t('插件已被禁用');
    }

    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form){}

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    // 获得插件配置信息
    public static function getConfig(){
        return Typecho_Widget::widget('Widget_Options')->plugin('TleCollect');
    }
}