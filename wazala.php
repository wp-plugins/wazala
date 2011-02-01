<?php
/*
Plugin Name: Wazala Ecommerce Store & Shopping Cart
Plugin URI: http://www.wazala.com/
Description: Add a "store" to your blog, your store will simply pop-up over your content, no more sending your customers elsewhere to make a purchase. Configure from <a href="plugins.php?page=wazala.php">Wazala Configuration</a> page.
Version: 1.0.3
Author: Wazala
Author URI: http://www.wazala.com/

	Copyright (c) 2010 Wazala (http://www.wazala.com)
	Wazala Ecommerce Store is released under the GNU General Public
	License (GPL) http://www.gnu.org/licenses/gpl.txt

	This is a WordPress plugin (http://wordpress.org).

*/

function wp_wazala_selectOptions($name, $options) {
	$currentV = get_option($name);
	foreach ($options as $key=>$txt) {
		echo '<option value="'.$key.'"'.($currentV == $key ? ' selected ':'').'>';
		_e($txt);
		echo '</option>';
	}
}

function wp_wazala_phpArrayToJS($phpArray, $escape = true) {
	$s = "";
	if(is_array($phpArray))
	foreach($phpArray as $key=>$val) {
		if($escape)
			$s .= '"'.$key.'":';
		else 
			$s .= $key.':';
			
		if(is_array($val)) {
			$s .= wp_wazala_phpArrayToJS($val, $escape);
		}
		else {
			$val = str_replace('"','\"', $val );
			$val = str_replace(chr(10),' ', $val );
			$val = str_replace(chr(13),' ', $val );
			
			if($escape)
				$s .= '"'.$val.'"';
			else 
				$s .= $val;
		}
		$s .=",";
	}

	$s = substr($s,0,-1);
	$s = '{'.$s.'}';			
	
	return $s;		
}


function wp_wazala_init() {
	//$url = get_bloginfo('wpurl');

	$wazalaEnabled = get_option('wazala_enabled', false);
	if(!$wazalaEnabled) return ;

	$nickName = get_option('wazala_widget_nickname');	

	
	$confType = get_option('wazala_custom_code_ch', 0);	
	
	if($confType == 1) {
		$widgetCode = get_option('wazala_widget_code');
		if($widgetCode) echo $widgetCode;
		return;
	}
	else {
		$storeWidgetOptions = array();
		$storeWidgetOptions['ver'] = 1;
		$storeWidgetOptions['display'] = 'overlay';
		$storeWidgetOptions['lang'] = get_option('wazala_widget_lg');
		$storeWidgetOptions['nickname'] = $nickName;
		$placement = get_option('wazala_widget_placement');
		$storeWidgetOptions['placement'] = $placement;
		if($placement != 'hidden') {
			$storeWidgetOptions['touch_button'] = get_option('wazala_widget_touch_button');
			$storeWidgetOptions['label'] = get_option('wazala_widget_label');
			$fixedWidth = get_option('wazala_widget_fixed_width');
			if($fixedWidth) {
				$storeWidgetOptions['btn_container_width'] = $fixedWidth;
			}
		}
		
		?>
<script type="text/javascript" charset="utf-8">
  var is_ssl = ("https:" == document.location.protocol);
  var setsHost = is_ssl ? "https://wazala.com/widget/" : "http://wazala.com/widget/";
  var d = new Date(); var now=d.getTime();
  document.write(unescape("%3Cscript src='" + setsHost + "js/widget_over.js?v=1&rnd="+now+"' type='text/javascript'%3E%3C/script%3E"));
</script>

<script type="text/javascript" charset="utf-8">
  var store_widget_options = {};
  store_widget_options.wazalaURL = setsHost; 
  
  <?php
  	foreach ($storeWidgetOptions as $k=>$v) {
  		echo '	store_widget_options.'.$k.' = "'.$v.'";'."\n"; 
  	}
  ?>
  
  var store_widget = new WazalaWidget.widget(store_widget_options);
</script>

<?php 	
		
	}
}

function wazala_loadAdminStyles() {	
	wp_enqueue_style('load_admin_css', WP_PLUGIN_URL . '/wazala/css-admin.css', array(), CN_CURRENT_VERSION); 
}

function wazala_init_plugin() {
	add_action('admin_print_styles', 'wazala_loadAdminStyles'); 
}

function wazala_admin_mainform() {
	
	$availableLanguages = array (
		'en' => "English",
		'es' => 'Spanish',
		'de' => 'German',
		'gr' => 'Greek',
		'fr' => 'French',
		'it' => 'Italian',
		'nl' => 'Dutch',
		'ru' => 'Russian',
		'pl' => 'Polish',
		'pt' => 'Portuguese',
		'ro' => 'Romanian',
		'tr' => 'Turkish',
		'in' => 'Hindi',
		'jp' => 'Japanese',
		'cn' => 'Chinese',
		);	
		
	?>
						
			<fieldset class='options'>
											
				<div id="wazala_conf_type_section_1"  class="form-field wazalaform">					
					
					<div id="wazala_manual_conf">
						<div style="float:left;width:45%;margin-right:5%;">
							<label for="wazala_widget_nickname"><?php _e('Wazala nick name:'); ?></label>
							<input type="text" style="width:120px;" name="wazala_widget_nickname" id="wazala_widget_nickname" value="<?php echo get_option('wazala_widget_nickname'); ?>" />.wazala.com
						</div>
						<div style="float:left;width:50%;">
							<label for="wazala_widget_lg"><?php _e('Language:'); ?></label>
							<select id="wazala_widget_lg" name="wazala_widget_lg" onchange="wazala_languageOnchange();">
								<?php wp_wazala_selectOptions('wazala_widget_lg', $availableLanguages); ?>
							</select>
						</div>					
						<div class="clear" style="height:5px;"><!-- --></div>
						
						<div style="float:left;width:45%;margin-right:5%;">
							<label for="wazala_widget_placement"><?php _e('Location:'); ?></label>
							<select id="wazala_widget_placement" name="wazala_widget_placement" onchange="wazala_widget_placement_onChange();">
								<?php wp_wazala_selectOptions('wazala_widget_placement', array('right'=>'Right', 'left'=>'Left', 'center'=>'Middle', 'hidden'=>'Hidden')); ?>
							</select>


							<?php
								$wazalaWidgetFixedWidth = get_option('wazala_widget_fixed_width') + 0;
								if(!$wazalaWidgetFixedWidth) $wazalaWidgetFixedWidth = "";
							?>
							<table width="100%" border="0" cellpadding="0" cellspacing="0" style="height:28px;" id="fixedWidthField">
							<tr>
								<td width="10%"><label><input name="wazala_widget_fixed_width_ch" id="wazala_widget_fixed_width_ch" value="1" type="checkbox" onclick="wazala_widget_fixed_width_onChange();" <?php if ( $wazalaWidgetFixedWidth ) echo ' checked="checked" '; ?> /></label></td>
								<td width="40%"><?php _e('Fixed width'); ?></td>
								<td width="60%"><input type="text" size="5" name="wazala_widget_fixed_width" id="wazala_widget_fixed_width" value="<?php echo $wazalaWidgetFixedWidth; ?>" /></td>
							</tr>
							</table>

							<div id="touch_button_c">
								<label for="wazala_widget_touch_button"><?php _e('Wazala Touch Button:'); ?></label>
								<select id="wazala_widget_touch_button" name="wazala_widget_touch_button" onchange="wazala_widget_placement_onChange();">
									<?php wp_wazala_selectOptions('wazala_widget_touch_button', array('floating'=>'Floating', 'minimized'=>'Minimized')); ?>
								</select>
							</div>

						</div>
						
						<div style="float:left;width:50%;">
							<div id="wazalaLabelField">
								<label for="wazala_widget_label"><?php _e('Label:'); ?></label>
								<select id="wazala_widget_label" name="wazala_widget_label" onchange="wazala_widget_label_onChange();">								
								</select>
							</div>

							<div style="float:left;width:50%;">
								<input type="text" size="15" name="wazala_widget_custom_label" id="wazala_widget_custom_label" value="" />
							</div>
						</div>
						<div class="clear"><!-- --></div>
						
						
						
					</div>
					
					<br />
					<label for="wazala_custom_code_ch">
						<input type="checkbox" style="float:left;width:16px;" value="1" name="wazala_custom_code_ch" id="wazala_custom_code_ch" value="wazala_custom_code_ch" <?php echo (get_option('wazala_custom_code_ch') == 1 ? 'checked' : ''); ?> onclick="wazala_conf_type_onChange();" />
						Insert custom widget code (only use if you know what you are doing)</label>
						<br />

					<div id="wazala_conf_type_section_0" class="form-field wazalaform">
						<label for="wazala_widget_code"><?php _e('Wazala widget code:'); ?></label>
						<textarea style="width:100%;" rows="6" name="wazala_widget_code" id="wazala_widget_code"><?php echo get_option('wazala_widget_code'); ?></textarea>
					</div>
				</div>
			</fieldset>
	<?php
}

function wazala_conf() {	
	
	$savedNotif = '';
	
 	if ( isset($_POST['submit']) ) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));
					
			
		update_option('wazala_enabled', $_POST['wazala_enabled'] + 0);
			
		$wdgType = $_POST['wazala_custom_code_ch'] + 0;		
		
		update_option('wazala_custom_code_ch', $wdgType);
		
		if($wdgType == 1) { //copy paste widget
			update_option('wazala_widget_code', stripslashes($_POST['wazala_widget_code']));	
		}
		else {
			update_option('wazala_widget_nickname', strip_tags(stripslashes($_POST['wazala_widget_nickname'])));
			update_option('wazala_widget_lg', strip_tags(stripslashes($_POST['wazala_widget_lg'])));
			update_option('wazala_widget_placement', strip_tags(stripslashes($_POST['wazala_widget_placement'])));			
			update_option('wazala_widget_touch_button', strip_tags(stripslashes($_POST['wazala_widget_touch_button'])));			
			
						
			if ( isset($_POST['wazala_widget_fixed_width_ch']) && ($_POST['wazala_widget_fixed_width'] + 0) ) {
				update_option('wazala_widget_fixed_width', $_POST['wazala_widget_fixed_width'] + 0);	
			}
			else {
				update_option('wazala_widget_fixed_width', 0);	
			}
			
			$label = strip_tags(stripslashes($_POST['wazala_widget_label']));
			if($label == '[[custom]]') {
				$label = strip_tags(stripslashes($_POST['wazala_widget_custom_label']));
			}			
			update_option('wazala_widget_label', $label);
		}
				
		$savedNotif = '<div id="message" class="updated fade"><p><strong>' . __('Options saved.') . '</strong></p></div>';
	}
	
	$wazalaEnabled = get_option('wazala_enabled', false);
	
	?>	
	
	<div class="wrap">			
		<div id="wazala_logo" style="float:right;margin:10px 0 0 0;"><!-- --></div>		
		<h2><?php _e('Wazala Widget Configuration'); ?></h2>	
		<?php echo $savedNotif;?>
		<div class="clear"><!-- --></div>		

		<ul>
			<li><strong>Step 1.</strong> Signup for your Wazala account at <a href="http://www.wazala.com/" target="_blank">http://www.wazala.com/</a>, signing up is easy and only takes you a few minutes. Setting up a Wazala store will allow you to publish your store to your WordPress blog, facebook business pages, your own website or social profile. </li>

			<li><strong>Step 2.</strong> Once you have signed up for your account and configured your store, enter your Wazala store nickname in the edit box below. Your store nick name is the first part of your wazala store url, nickname.wazala.com. It is alphanumeric and can not have spaces or punctuations. </li>

			<li><strong>Step 3.</strong> Select the default language you want your store to start with. Your customers can optionally change the store language from the shopping cart interface. You may also select the location of the store button or just hide it and call a simple <a href="http://wazala.zendesk.com/entries/243667-how-can-i-add-a-custom-button-to-open-the-store-to-a-specific-page" target="_blank">JavaScript</a> to open it any time from any object , link or image on your website.</li>
		</ul>

		<div class="form-wrap" style="width:800px;margin:0 auto;">		
		<div class="has-right-sidebar metabox-holder">
			<form action="" method="post">			
			<div class="inner-sidebar" id="side-info-column">
				<div class="postbox" id="submitdiv">
					<h3>Widget Enabled</h3>
					<div class="inside">
						<div id="minor-publishing">
							<label for="wazala_enabled_1"><input type="radio" <?php echo ($wazalaEnabled? 'checked="checked"' :'');?> value="1" name="wazala_enabled" id="wazala_enabled_1"><?php echo _e('Yes');?></label>
							<label for="wazala_enabled_0"><input type="radio" <?php echo ($wazalaEnabled? '' : 'checked="checked"');?> value="0" name="wazala_enabled" id="wazala_enabled_0"><?php echo _e('No');?></label>
						</div>
						<div id="major-publishing-actions"><div id="publishing-action">
							<input type="submit" value="<?php _e('Update options &raquo;'); ?>" name="submit" class="button-primary">
						</div><div class="clear"><!-- --></div>
						</div>
					</div>
				</div>
			</div>
			<div id="post-body-content">
				<?php wazala_admin_mainform(); ?>				
			</div>
			</form>
		</div>
		</div>
	</div>
		
		<?php
			$labelsArr = unserialize(file_get_contents(WP_PLUGIN_DIR . '/wazala/lang_texts.data'));
			
			$jsLabels = wp_wazala_phpArrayToJS($labelsArr);

			
		?>
	
		<script type="text/javascript">
			var wazalaLabels = <?php echo $jsLabels;?>;
			
			function wazala_conf_getID(field) {
				return document.getElementById(field);
			}
			
			function wazala_languageOnchange() {
				var wdgLabel = wazala_conf_getID("wazala_widget_label");
				var lg = wazala_conf_getID("wazala_widget_lg").value
				if(wazalaLabels[lg]) {
					var selIndex = wdgLabel.selectedIndex;
					var s = "";
					for(var lbOpt in wazalaLabels[lg]) {
						s += '<option value="' + wazalaLabels[lg][lbOpt] + '">' + wazalaLabels[lg][lbOpt] + '</option>';
					}
					s += '<option value="-">----------</option><option value="[[custom]]">Custom</option>';
					wdgLabel.innerHTML = s;
					wdgLabel.selectedIndex = selIndex;
				}
			}
			
			function wazala_conf_type_onChange() {
				var v = wazala_conf_getID("wazala_custom_code_ch").checked;				
				if(v) {
					wazala_conf_getID("wazala_manual_conf").style.display = "none";
					wazala_conf_getID("wazala_conf_type_section_0").style.display = "";
					
				}
				else {
					wazala_conf_getID("wazala_manual_conf").style.display = "";
					wazala_conf_getID("wazala_conf_type_section_0").style.display = "none";
				}
				//wazala_conf_getID("wazala_conf_type_section_" + (v)).style.display = "";
				//wazala_conf_getID("wazala_conf_type_section_" + (1-v)).style.display = "none";
			}
			
			function wazala_widget_fixed_width_onChange() {
				var v = wazala_conf_getID("wazala_widget_fixed_width_ch").checked;
				if(v) {
					wazala_conf_getID("wazala_widget_fixed_width").style.display = "";					
				}
				else wazala_conf_getID("wazala_widget_fixed_width").style.display = "none";
			}
			
			function wazala_widget_label_onChange() {
				var v = wazala_conf_getID("wazala_widget_label").value;
				if(v == "[[custom]]") {
					wazala_conf_getID("wazala_widget_custom_label").style.display = "";
				}
				else {
					wazala_conf_getID("wazala_widget_custom_label").style.display = "none";
				}
			}
			
			function wazala_widget_placement_onChange() {
				var v = wazala_conf_getID("wazala_widget_placement").value;
				if(v == "hidden") {
					wazala_conf_getID("wazalaLabelField").style.display = "none";
					wazala_conf_getID("wazala_widget_custom_label").style.display = "none";
					wazala_conf_getID("fixedWidthField").style.display = "none";
					wazala_conf_getID("touch_button_c").style.display = "none";
				}
				else {
					wazala_conf_getID("wazalaLabelField").style.display = "";
					wazala_conf_getID("wazala_widget_custom_label").style.display = "";
					wazala_conf_getID("touch_button_c").style.display = "";
					
					if(v == "center") {
						wazala_conf_getID("fixedWidthField").style.display = "none";
					}
					else {
						wazala_conf_getID("fixedWidthField").style.display = "";	
					}
					
					wazala_widget_label_onChange();
				}
			}
			
			function wazala_setLabelValue(value) {
				var wdgLabel = wazala_conf_getID("wazala_widget_label");
				var optFound = false;
				for(var i in wdgLabel.options) {
					if(wdgLabel.options[i].value == value) {
						wdgLabel.selectedIndex = i;
						optFound = true;
						break;
					}
				}
				if(!optFound) {
					wdgLabel.value = "[[custom]]";
					wazala_conf_getID("wazala_widget_custom_label").value = value;
					wazala_widget_label_onChange();
				}
			}
			
			wazala_conf_type_onChange();
			wazala_widget_fixed_width_onChange();
			wazala_widget_label_onChange();
			wazala_widget_placement_onChange();
			wazala_languageOnchange();
			wazala_setLabelValue("<?php echo get_option('wazala_widget_label');?>");			
		</script>	
		
<?php
}

function wazala_config_page() {
	if ( function_exists('add_submenu_page') ) {
		add_submenu_page('plugins.php', __('Wazala Configuration'), __('Wazala Configuration'), 'manage_options', basename(__FILE__), 'wazala_conf');
	}
}



add_action('wp_head', 'wp_wazala_init');
add_action('admin_menu', 'wazala_config_page'); 
add_action('plugins_loaded', 'wazala_init_plugin' );

?>
