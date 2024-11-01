<?php
/*
Plugin Name: Truncate Comments
Plugin URI: https://wordpress.org/plugins/truncate-comments/
Description: The plugin uses Javascript to hide long comments (Amazon-style comments).
Version: 2.00
Author: Flector
Author URI: https://profiles.wordpress.org/flector#content-plugins
Text Domain: truncate-comments
*/ 

//проверка версии плагина (запуск функции установки новых опций) begin
function tc_check_version() {
    $tc_options = get_option('tc_options');
    if (!isset($tc_options['version'])) {$tc_options['version']='';update_option('tc_options',$tc_options);}
    if ( $tc_options['version'] != '2.00' ) {
        tc_set_new_options();
    }    
}
add_action('plugins_loaded', 'tc_check_version');
//проверка версии плагина (запуск функции установки новых опций) end 

//функция установки новых опций при обновлении плагина у пользователей begin
function tc_set_new_options() { 
    $tc_options = get_option('tc_options');

    //если нет опции при обновлении плагина - записываем ее
    //if (!isset($tc_options['new_option'])) {$tc_options['new_option']='value';}
    
    //если необходимо переписать уже записанную опцию при обновлении плагина
    //$tc_options['old_option'] = 'new_value';
    
    if ($tc_options['dont_load_jquery'] == '1') $tc_options['dont_load_jquery'] = 'enabled';
    if ($tc_options['only_single'] == '1') $tc_options['only_single'] = 'enabled';
    
    if (!isset($tc_options['hideText'])) {$tc_options['hideText']='';}
    
    $tc_options['version'] = '2.00';
    update_option('tc_options', $tc_options);
}
//функция установки новых опций при обновлении плагина у пользователей end

//функция установки значений по умолчанию при активации плагина begin
function tc_init() {
    
    $tc_options = array(); tc_setup();
    
    $tc_options['version'] = '2.00';
    $tc_options['cutby'] = 'words';
    $tc_options['length'] = '40';
    $tc_options['ellipsis'] = '…';
    $tc_options['showText'] = __('Read more','truncate-comments');
    $tc_options['speed'] = '1000';
    $tc_options['dont_load_jquery'] = 'disabled';
    $tc_options['only_single'] = 'enabled';
    $tc_options['hideText'] = '';
   
    add_option('tc_options', $tc_options);
}
add_action('activate_truncate-comments/truncate-comments.php', 'tc_init');
//функция установки значений по умолчанию при активации плагина end

//функция при деактивации плагина begin
function tc_on_deactivation() {
	if ( ! current_user_can('activate_plugins') ) return;
}
register_deactivation_hook( __FILE__, 'tc_on_deactivation' );
//функция при деактивации плагина end

//функция при удалении плагина begin
function tc_on_uninstall() {
	if ( ! current_user_can('activate_plugins') ) return;
    delete_option('tc_options');
}
register_uninstall_hook( __FILE__, 'tc_on_uninstall' );
//функция при удалении плагина end

//загрузка файла локализации плагина begin
function tc_setup(){
    load_plugin_textdomain('truncate-comments');
}
add_action('init', 'tc_setup');
//загрузка файла локализации плагина end

//добавление ссылки "Настройки" на странице со списком плагинов begin
function tc_actions($links) {
	return array_merge(array('settings' => '<a href="options-general.php?page=truncate-comments.php">' . __('Settings', 'truncate-comments') . '</a>'), $links);
}
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ),'tc_actions');
//добавление ссылки "Настройки" на странице со списком плагинов end

//функция загрузки скриптов и стилей плагина только в админке и только на странице настроек плагина begin
function tc_files_admin($hook_suffix) {
	$purl = plugins_url('', __FILE__);
    if ( $hook_suffix == 'settings_page_truncate-comments' ) {
        if(!wp_script_is('jquery')) {wp_enqueue_script('jquery');}    
        wp_register_script('tc-lettering', $purl . '/inc/jquery.lettering.js');  
        wp_enqueue_script('tc-lettering');
        wp_register_script('tc-textillate', $purl . '/inc/jquery.textillate.js');
        wp_enqueue_script('tc-textillate');
        wp_register_style('tc-animate', $purl . '/inc/animate.min.css');
        wp_enqueue_style('tc-animate');
        wp_register_script('tc-script', $purl . '/inc/tc-script.js', array(), '2.00');  
        wp_enqueue_script('tc-script');
        wp_register_style('tc-css', $purl . '/inc/tc-css.css', array(), '2.00');
        wp_enqueue_style('tc-css');
    }
}
add_action('admin_enqueue_scripts', 'tc_files_admin');
//функция загрузки скриптов и стилей плагина только в админке и только на странице настроек плагина end

//функция вывода страницы настроек плагина begin
function tc_options_page() {
$purl = plugins_url('', __FILE__);

if (isset($_POST['submit'])) {
     
//проверка безопасности при сохранении настроек плагина begin       
if ( ! wp_verify_nonce( $_POST['tc_nonce'], plugin_basename(__FILE__) ) || ! current_user_can('edit_posts') ) {
   wp_die(__( 'Cheatin&#8217; uh?', 'truncate-comments' ));
}
//проверка безопасности при сохранении настроек плагина end
        
    //проверяем и сохраняем введенные пользователем данные begin    
    $tc_options = get_option('tc_options');
    
    $tc_options['cutby'] = sanitize_text_field($_POST['cutby']);
    if (is_numeric($_POST['length'])) {$tc_options['length'] = sanitize_text_field($_POST['length']);}
    $tc_options['ellipsis'] = esc_attr($_POST['ellipsis']);
    $tc_options['showText'] = esc_attr($_POST['showText']);
    if (is_numeric($_POST['speed'])) {$tc_options['speed'] = sanitize_text_field($_POST['speed']);}
    
    if(isset($_POST['dont_load_jquery'])){$tc_options['dont_load_jquery'] = sanitize_text_field($_POST['dont_load_jquery']);}else{$tc_options['dont_load_jquery'] = 'disable';}
    if(isset($_POST['only_single'])){$tc_options['only_single'] = sanitize_text_field($_POST['only_single']);}else{$tc_options['only_single'] = 'disable';}
    
    $tc_options['hideText'] = esc_attr($_POST['hideText']);
    
    update_option('tc_options', $tc_options);
    //проверяем и сохраняем введенные пользователем данные end
}
$tc_options = get_option('tc_options');
?>
<?php   if (!empty($_POST) ) :
if ( ! wp_verify_nonce( $_POST['tc_nonce'], plugin_basename(__FILE__) ) || ! current_user_can('edit_posts') ) {
   wp_die(__( 'Cheatin&#8217; uh?', 'truncate-comments' ));
}
?>
<div id="message" class="updated fade"><p><strong><?php _e('Options saved.', 'truncate-comments'); ?></strong></p></div>
<?php endif; ?>

<div class="wrap">
<h2><?php _e('&#171;Truncate Comments&#187; Settings', 'truncate-comments'); ?></h2>

<div class="metabox-holder" id="poststuff">
<div class="meta-box-sortables">

<?php $lang = get_locale(); ?>
<?php if ($lang == 'ru_RU') { ?>
<div class="postbox">
    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode">Вам нравится этот плагин ?</span></h3>
    <div class="inside" style="display: block;margin-right: 12px;">
        <img src="<?php echo $purl . '/img/icon_coffee.png'; ?>" title="Купить мне чашку кофе :)" style=" margin: 5px; float:left;" />
        <p>Привет, меня зовут <strong>Flector</strong>.</p>
        <p>Я потратил много времени на разработку этого плагина.<br />
		Поэтому не откажусь от небольшого пожертвования :)</p>
        <a target="_blank" id="yadonate" href="https://money.yandex.ru/to/41001443750704/200">Подарить</a> 
        <p>Или вы можете заказать у меня услуги по WordPress, от мелких правок до создания полноценного сайта.<br />
        Быстро, качественно и дешево. Прайс-лист смотрите по адресу <a target="new" href="https://www.wpuslugi.ru/?from=tc-plugin">https://www.wpuslugi.ru/</a>.</p>
        <div style="clear:both;"></div>
    </div>
</div>
<?php } else { ?>
<div class="postbox">
    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('Do you like this plugin ?', 'truncate-comments'); ?></span></h3>
    <div class="inside" style="display: block;margin-right: 12px;">
        <img src="<?php echo $purl . '/img/icon_coffee.png'; ?>" title="<?php _e('buy me a coffee', 'truncate-comments'); ?>" style=" margin: 5px; float:left;" />
        <p><?php _e('Hi! I\'m <strong>Flector</strong>, developer of this plugin.', 'truncate-comments'); ?></p>
        <p><?php _e('I\'ve been spending many hours to develop this plugin.', 'truncate-comments'); ?> <br />
		<?php _e('If you like and use this plugin, you can <strong>buy me a cup of coffee</strong>.', 'truncate-comments'); ?></p>
        <a target="new" href="https://www.paypal.me/flector"><img alt="" src="<?php echo $purl . '/img/donate.gif'; ?>" title="<?php _e('Donate with PayPal', 'truncate-comments'); ?>" /></a>
        <div style="clear:both;"></div>
    </div>
</div>
<?php } ?>

<form action="" method="post">

<div class="postbox">

    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('Options', 'truncate-comments'); ?></span></h3>
    <div class="inside" style="display: block;">

        <table class="form-table">
            
            <tr>
                <th><?php _e('Collapse comments:', 'truncate-comments') ?></th>
                <td>
                    <select name="cutby">
                        <option value="chars" <?php if ($tc_options['cutby'] == 'chars') echo 'selected="selected"'; ?>><?php _e('Characters', 'truncate-comments'); ?></option>
                        <option value="words" <?php if ($tc_options['cutby'] == 'words') echo 'selected="selected"'; ?>><?php _e('Words', 'truncate-comments'); ?></option>
                        <option value="lines" <?php if ($tc_options['cutby'] == 'lines') echo 'selected="selected"'; ?>><?php _e('Lines', 'truncate-comments'); ?></option>
                    </select>
                    <small>
                        <ul style="margin-bottom:0px;">
                            <li><?php _e('<strong>Characters</strong>: to truncate characters.', 'truncate-comments'); ?></li>
                            <li><?php _e('<strong>Words</strong>: to truncate words.', 'truncate-comments'); ?></li>
                            <li style="margin-bottom:0px;"><?php _e('<strong>Lines</strong>: to truncate lines.', 'truncate-comments'); ?></li>
                        </ul>
                    </small>
                </td>
            </tr>
        
            <tr>
                <th><?php _e('Comment length:', 'truncate-comments') ?></th>
                <td>
                    <input type="text" name="length" size="3" value="<?php echo stripslashes($tc_options['length']); ?>" /><br /><small><?php _e('Length in characters, words or lines.', 'truncate-comments'); ?> </small>
                </td>
            </tr>
            
            <tr>
                <th><?php _e('Ellipsis:', 'truncate-comments') ?></th>
                <td>
                    <input type="text" name="ellipsis" size="20" value="<?php echo stripslashes($tc_options['ellipsis']); ?>" />  <br /><small><?php _e('The text displayed next to the hidden comment to indicate the presence of more content.', 'truncate-comments'); ?> </small>
                </td>
            </tr>
            
            <tr>
                <th><?php _e('"Read more" text:', 'truncate-comments') ?></th>
                <td>
                    <input type="text" name="showText" size="20" value="<?php echo stripslashes($tc_options['showText']); ?>" />  <br /><small><?php _e('The link that expands a collapsed comment.', 'truncate-comments'); ?></small>
                </td>
            </tr>
           
           <tr>
                <th><?php _e('Speed:', 'truncate-comments') ?></th>
                <td>
                    <input type="text" name="speed" size="3" value="<?php echo stripslashes($tc_options['speed']); ?>" /><br /><small><?php _e('The speed (duration) of a comment\'s vertical collapse (in milliseconds).', 'truncate-comments'); ?></small>
                </td>
           </tr>
            
           <tr>
                <th><?php _e('"Hide" text:', 'truncate-comments') ?></th>
                <td>
                    <input type="text" name="hideText" size="20" value="<?php echo stripslashes($tc_options['hideText']); ?>" />  <br /><small><?php _e('The link that collapse a expanded comment.', 'truncate-comments'); ?><br />
                    <?php _e('Leave this field blank if you don\'t want to use "Hide" button.', 'truncate-comments'); ?><br />
                    </small>
                </td>
           </tr>

           <tr>
                <th></th>
                <td>
                    <input type="submit" name="submit" class="button button-primary" value="<?php _e('Update options &raquo;', 'truncate-comments'); ?>" />
                </td>
           </tr>
            
        </table>
    </div>
</div>


<div class="postbox">

    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('Advanced Options', 'truncate-comments'); ?></span></h3>
	  <div class="inside" style="display: block;">
      
         <table class="form-table">   

            <tr>
                <td><input type="checkbox" value="enabled" <?php if ($tc_options['dont_load_jquery'] == 'enabled') echo 'checked="checked"'; ?> name="dont_load_jquery"  id="dont_load_jquery" /> <label for="dont_load_jquery"><?php _e("Don't load jQuery", 'truncate-comments'); ?></label><br /><small><?php _e('Don\'t load jQuery if it\'s included in your theme.', 'truncate-comments'); ?> </small></td>
            </tr>
            
            <tr>
                <td><input type="checkbox" value="enabled" <?php if ($tc_options['only_single'] == 'enabled') echo 'checked="checked"'; ?> name="only_single" id="only_single" /> <label for="only_single"><?php _e('Load the plugin script on single pages only', 'truncate-comments'); ?></label><br /><small><?php _e('Load the plugin script only on single pages (to avoid loading the script on pages with no comments on them).', 'truncate-comments'); ?></small></td>
            </tr>  
            <tr>
                <td>
                    <input type="submit" name="submit" class="button button-primary" value="<?php _e('Update options &raquo;', 'truncate-comments'); ?>" />
                </td>
            </tr>  
            
        </table>
    
    </div>
</div>

<div class="postbox" style="margin-bottom:0;">
    <h3 style="border-bottom: 1px solid #EEE;background: #f7f7f7;"><span class="tcode"><?php _e('About', 'truncate-comments'); ?></span></h3>
	  <div class="inside" style="padding-bottom:15px;display: block;">
     
      <p><?php _e('If you liked my plugin, please <a target="new" href="https://wordpress.org/plugins/truncate-comments/"><strong>rate</strong></a> it.', 'truncate-comments'); ?></p>
      <p style="margin-top:20px;margin-bottom:10px;"><?php _e('You may also like my other plugins:', 'truncate-comments'); ?></p>
      
      <div class="about">
        <ul>
            <?php if ($lang == 'ru_RU') : ?>
            <li><a target="new" href="https://ru.wordpress.org/plugins/rss-for-yandex-zen/">RSS for Yandex Zen</a> - создание RSS-ленты для сервиса Яндекс.Дзен.</li>
            <li><a target="new" href="https://ru.wordpress.org/plugins/rss-for-yandex-turbo/">RSS for Yandex Turbo</a> - создание RSS-ленты для сервиса Яндекс.Турбо.</li>
            <?php endif; ?>
            <li><a target="new" href="https://wordpress.org/plugins/bbspoiler/">BBSpoiler</a> - <?php _e('this plugin allows you to hide text under the tags [spoiler]your text[/spoiler].', 'truncate-comments'); ?></li>
            <li><a target="new" href="https://wordpress.org/plugins/easy-textillate/">Easy Textillate</a> - <?php _e('very beautiful text animations (shortcodes in posts and widgets or PHP code in theme files).', 'truncate-comments'); ?> </li>
            <li><a target="new" href="https://wordpress.org/plugins/cool-image-share/">Cool Image Share</a> - <?php _e('this plugin adds social sharing icons to each image in your posts.', 'truncate-comments'); ?> </li>
            <li><a target="new" href="https://wordpress.org/plugins/today-yesterday-dates/">Today-Yesterday Dates</a> - <?php _e('this plugin changes the creation dates of posts to relative dates.', 'truncate-comments'); ?> </li>
            <li><a target="new" href="https://wordpress.org/plugins/easy-yandex-share/">Easy Yandex Share</a> - <?php _e('share buttons for WordPress from Yandex. ', 'truncate-comments'); ?> </li>
            </ul>
      </div>     
    </div>
</div>
<?php wp_nonce_field( plugin_basename(__FILE__), 'tc_nonce'); ?>
</form>
</div>
</div>
<?php 
}
//функция вывода страницы настроек плагина end

//функция добавления ссылки на страницу настроек плагина в раздел "Настройки" begin
function tc_menu() {
	add_options_page('Truncate Comments', 'Truncate Comments', 'manage_options', 'truncate-comments.php', 'tc_options_page');
}
add_action('admin_menu', 'tc_menu');
//функция добавления ссылки на страницу настроек плагина в раздел "Настройки" end

//загрузка jquery, если не загружена и если разрешено в настройках плагина begin
function tc_add_jquery() {
    $tc_options = get_option('tc_options');
    if(!wp_script_is('jquery') && $tc_options['dont_load_jquery'] != 'enabled'){wp_enqueue_script('jquery');}
}
add_action('wp_enqueue_scripts', 'tc_add_jquery');
//загрузка jquery, если не загружена и если разрешено в настройках плагина end

//загрузка скрипта jquery.collapser.min.js в соответствии с настройками begin
function tc_add_collapser_script() {
    $tc_options = get_option('tc_options');
    $purl = plugins_url('', __FILE__);
    if ($tc_options['only_single'] == 'enabled' && is_singular()) {
        wp_register_script('jcollapser', $purl.'/inc/jquery.collapser.min.js');  
        wp_enqueue_script('jcollapser');
    }
    if ($tc_options['only_single'] != 'enabled') {
        wp_register_script('jcollapser', $purl.'/inc/jquery.collapser.min.js');  
        wp_enqueue_script('jcollapser');
    }
}
add_action('wp_enqueue_scripts', 'tc_add_collapser_script');
//загрузка скрипта jquery.collapser.min.js в соответствии с настройками end

//оборачиваем контент комментария своим дивом begin
function tc_collapser_comment($content) {
    return '<div class="tc-collapser-comment">' . $content . '</div>';
}
add_filter('comment_text', 'tc_collapser_comment', 999);
//оборачиваем контент комментария своим дивом end

//выводим скрипт согласно настройкам в футере begin
function tc_print_script() { 
$tc_options = get_option('tc_options'); ?>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('.tc-collapser-comment').collapser({
        mode: '<?php echo $tc_options['cutby']; ?>',
        truncate: <?php echo $tc_options['length']; ?>,
        ellipsis: '<?php echo $tc_options['ellipsis']; ?>',
        showText: '<?php echo $tc_options['showText']; ?>',
        speed: <?php echo $tc_options['speed']; ?>,
        controlBtn: 'commentMoreLink',
        hideText: '<?php echo $tc_options['hideText']; ?>',
        <?php if ( !$tc_options['hideText'] ) echo 'lockHide: true,'; ?>
    });
    
});     
</script>
<?php }
function tc_collapser() {
    $tc_options = get_option('tc_options');
    if ($tc_options['only_single'] == 'enabled' && is_singular()) {tc_print_script();}
    if ($tc_options['only_single'] != 'enabled') {tc_print_script();}
}
add_action('wp_footer', 'tc_collapser');
//выводим скрипт согласно настройкам в футере end