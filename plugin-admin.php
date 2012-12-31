<?php

class Simple_Backup_Admin extends Simple_Backup {
	/**
	 * Error messages to diplay
	 *
	 * @var array
	 */
	private $_messages = array();

	
	public $backup_manager;
	
	
	
	/**
	 * Class constructor
	 *
	 */
	public function __construct() {
		$this->_plugin_dir   = DIRECTORY_SEPARATOR . str_replace(basename(__FILE__), null, plugin_basename(__FILE__));
		$this->_settings_url = 'options-general.php?page=' . plugin_basename(__FILE__);
		
		$allowed_options = array(
			
		);
		
		// set watermark options
		if(array_key_exists('delete_backup_file', $_GET)){
		
			$this->deleteBackupFile($_GET['delete_backup_file']);
			
			header("Location: ".admin_url()."tools.php?page=backup_files" );
			die();	
			
		} else {
			// register installer function
			register_activation_hook(SB_LOADER, array(&$this, 'activate_simple_backup'));
		
			// add plugin "Settings" action on plugin list
			add_action('plugin_action_links_' . plugin_basename(SB_LOADER), array(&$this, 'add_plugin_actions'));
			
			// add links for plugin help, donations,...
			add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);
			
			// push options page link, when generating admin menu
			add_action('admin_menu', array(&$this, 'admin_menu'));
	
			//add help menu
			add_filter('contextual_help', array(&$this,'admin_help'), 10, 3);
			
			add_action('admin_notices', array($this, 'activation_notice_settings'));
			add_action('admin_init', array($this, 'nag_ignore'));
			
			
			$backup_manager = new Simple_Backup_Manager();
			$this->backup_manager = $backup_manager;

			add_action( 'admin_head', array($backup_manager, 'screen_options') );
			add_action( 'admin_menu', array($backup_manager, 'simple_cron_admin_menu') );


		}
	}
	
	
	

	
	
	

	

		
	/**
	 * Add "Settings" action on installed plugin list
	 */
	public function add_plugin_actions($links) {
		array_unshift($links, '<a href="options-general.php?page=' . plugin_basename(__FILE__) . '">' . __('Settings') . '</a>');
		
		return $links;
	}
	
	/**
	 * Add links on installed plugin list
	 */
	public function add_plugin_links($links, $file) {
		if($file == plugin_basename(SB_LOADER)) {
			$upgrade_url = 'http://mywebsiteadvisor.com/tools/wordpress-plugins/simple-backup/';
			$links[] = '<a href="'.$upgrade_url.'" target="_blank" title="Click Here to Upgrade this Plugin!">Upgrade Plugin</a>';
		
			$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
			$links[] = '<a href="'.$rate_url.'" target="_blank" title="Click Here to Rate and Review this Plugin on WordPress.org">Rate This Plugin</a>';
			
		}
		
		return $links;
	}
	
	
	/**
	 * Add menu entry for Simple Backup settings and attach style and script include methods
	 */
	public function admin_menu() {		
		// add option in admin menu, for setting details on watermarking
		global $simple_backup_admin_page;
		$simple_backup_admin_page = add_options_page('Simple Backup Plugin Options', 'Simple Backup', 'manage_options', __FILE__, array(&$this, 'optionsPage'));

		add_action('admin_print_styles-' . $simple_backup_admin_page,     array(&$this, 'installStyles'));
				
	}
	
	

	

	
	
	
	function activation_notice_settings(){
		global $current_user ;
		global $pagenow;
		if(isset($_GET['page'])){
			if ( $pagenow == 'options-general.php' ){
				if ( $_GET['page'] == 'simple-backup/plugin-admin.php'  ) {
					$user_id = $current_user->ID;
					if ( false === ( $simple_security_nag = get_transient( 'simple_backup_nag' ) ) ) {
						echo '<div class="updated">';
						echo $this->display_support_us();
						echo "<br>";
						echo '<p><a href="'.$_SERVER['REQUEST_URI'].'&simple_backup_nag_ignore=0" >Click Here to Dismiss this Message.</a></p>';
						echo "</div>";
					}
				}
			}
		}
	}
	

	function nag_ignore() {
		if ( isset($_GET['simple_backup_nag_ignore']) && '0' == $_GET['simple_backup_nag_ignore'] ) {
			 $expiration = 60 * 60 * 24 * 30;
			 $simple_backup_nag = "true";
			 set_transient( 'simple_backup_nag', $simple_backup_nag, $expiration );
		}
	}
	
	
	
	
		
	public function display_support_us(){
				
		$string = '<p><b>Thank You for using the Simple Backup Plugin for WordPress!</b></p>';
		$string .= "<p>Please take a moment to <b>Support the Developer</b> by doing some of the following items:</p>";
		
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
		$string .= "<li><a href='$rate_url' target='_blank' title='Click Here to Rate and Review this Plugin on WordPress.org'>Click Here</a> to Rate and Review this Plugin on WordPress.org!</li>";
		
		$string .= "<li><a href='http://facebook.com/MyWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Facebook'>Click Here</a> to Follow MyWebsiteAdvisor on Facebook!</li>";
		$string .= "<li><a href='http://twitter.com/MWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Twitter'>Click Here</a> to Follow MyWebsiteAdvisor on Twitter!</li>";
		$string .= "<li><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/' target='_blank' title='Click Here to Purchase one of our Premium WordPress Plugins'>Click Here</a> to Purchase Premium WordPress Plugins!</li>";
	
		return $string;
	}



	public function admin_help($contextual_help, $screen_id, $screen){
	
		global $simple_backup_admin_page, $simple_backup_file_manager_page;
		
		if ($screen_id == $simple_backup_admin_page || $screen_id == $simple_backup_file_manager_page) {
			
			
			$support_the_dev = $this->display_support_us();
			$screen->add_help_tab(array(
				'id' => 'developer-support',
				'title' => "Support the Developer",
				'content' => "<h2>Support the Developer</h2><p>".$support_the_dev."</p>"
			));
			
			
			$screen->add_help_tab(array(
				'id' => 'plugin-support',
				'title' => "Plugin Support",
				'content' => "<h2>Support</h2><p>For Plugin Support please visit <a href='http://mywebsiteadvisor.com/support/' target='_blank'>MyWebsiteAdvisor.com</a></p>"
			));
			
			
			
			
			$faqs = "<p><b>Question: How do I restore a backup created by simple-backup plugin?</b><br>Answer: This plugin can create backup files in many standard formats and they can be restored using commonly available tools.  The MySQL Database backups could be restored using any MySQL tools, such as phpMyAdmin or MySQL Workbench.  The File Backups could be restored using FTP.</p>";
			
			
			$faqs .= "<p><b>Question: What are Transient Options and what happens if they are removed?</b><br>Answer: Transient Options are used by WordPress like a basic cache system.  Rather than performing a query every time a page is loaded, the results of that query could be saved as a WordPress Transient Option.  Clearing the Transient Options before a backup will help to save space in your backup files and should not effect the functionality of your website.  The only side-effect may be a minor slowdown as all of the necessary transient options would be re-queried and saved again. </p>";
			
			$screen->add_help_tab(array(
				'id' => 'plugin-faq',
				'title' => "Plugin FAQ's",
				'content' => "<h2>Frequently Asked Questions</h2>".$faqs
			));
			
			
			$screen->add_help_tab(array(
				'id' => 'plugin-upgrades',
				'title' => "Plugin Upgrades",
				'content' => "<h2>Plugin Upgrades</h2><p>Upgrade to Simple Backup Ultra for Scheduled, Automatic Optimizations and Backups: <a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/simple-backup/' target='_blank'>MyWebsiteAdvisor.com</a></p><p>Learn about all of our free plugins for WordPress here: <a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/' target='_blank'>MyWebsiteAdvisor.com</a></p>"
			));
	
	
			$screen->set_help_sidebar("<p>Please Visit us online for more Free WordPress Plugins!</p><p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/' target='_blank'>MyWebsiteAdvisor.com</a></p><br>");
			//$contextual_help = 'HELP!';
		}
			
		//return $contextual_help;

	}

	
	
	
	/**
	 * Include styles used by Simple Backup Plugin
	 */
	public function installStyles() {
		//wp_enqueue_style('simple-backup', WP_PLUGIN_URL . $this->_plugin_dir . 'style.css');
	}
	




	function HtmlPrintBoxHeader($id, $title, $right = false) {
		
		?>
		<div id="<?php echo $id; ?>" class="postbox">
			<h3 class="hndle"><span><?php echo $title ?></span></h3>
			<div class="inside">
		<?php
		
		
	}
	
	function HtmlPrintBoxFooter( $right = false) {
		?>
			</div>
		</div>
		<?php
		
	}
	
	

	
	public function deleteBackupFile($filename){
	
		$bk_dir = ABSPATH."simple-backup/";
		//echo $bk_dir . $filename;
		unlink($bk_dir . $filename);
	
	}

	
	
	function listFiles($dir){
		$file_list_output = array();
		$dir_list_output = array();
		
		$upload_dir   = wp_upload_dir();
		$base_dir = $upload_dir['basedir'];
		
		$dir_list_output[] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $base_dir);
						
		$iterator = new RecursiveDirectoryIterator($base_dir);
		foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as  $file) {
			$file_info = pathinfo($file->getFilename());
			if ( !$file->isFile() && is_numeric($file->getFilename()) ) { //create list of directories
			
				$dirPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file->getPathname());
				
				$dir_list_output[] =  $dirPath;
				
			}
		}
			
		
		sort($dir_list_output);
		//sort($file_list_output);
	
		
		$output = array();
		//$output['files'] = $file_list_output;
		$output['dirs'] = $dir_list_output;
		
		return $output;
	}
	




		public function display_social_media(){
	
	$social = '<style>

.fb_edge_widget_with_comment {
	position: absolute;
	top: 0px;
	right: 200px;
}

</style>

<div  style="height:20px; vertical-align:top; width:50%; float:right; text-align:right; margin-top:5px; padding-right:16px; position:relative;">

	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=253053091425708";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, "script", "facebook-jssdk"));</script>
	
	<div class="fb-like" data-href="http://www.facebook.com/MyWebsiteAdvisor" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false"></div>
	
	
	<a href="https://twitter.com/MWebsiteAdvisor" class="twitter-follow-button" data-show-count="false"  >Follow @MWebsiteAdvisor</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>


</div>';

return $social;


}			




	
	/**
	 * Display options page
	 */
	public function optionsPage() {
		// if user clicked "Save Changes" save them
		if(isset($_POST['Submit'])) {
			foreach($this->_options as $option => $value) {
				if(array_key_exists($option, $_POST)) {
					update_option($option, $_POST[$option]);
				} else {
					update_option($option, $value);
				}
			}

			$this->_messages['updated'][] = 'Options updated!';
		}


		
		
	
		foreach($this->_messages as $namespace => $messages) {
			foreach($messages as $message) {
?>
<div class="<?php echo $namespace; ?>">
	<p>
		<strong><?php echo $message; ?></strong>
	</p>
</div>
<?php
			}
		}
		
		
			
			
				
?>

<style>
.form-table{clear:left;};
</style>
	
									  
<script type="text/javascript">var wpurl = "<?php bloginfo('wpurl'); ?>";</script>

<?php echo $this->display_social_media(); ?>

<div class="wrap" id="sm_div">

	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Simple Backup Plugin Settings</h2>
	
	<p><a href='<?php echo get_option('siteurl'); ?>/wp-admin/tools.php?page=backup_files'>View Simple Backup File Manager</a></p>	
		
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
			
<?php $this->HtmlPrintBoxHeader('pl_diag',__('Plugin Diagnostic Check','diagnostic'),true); ?>

				<?php
				
				echo "<p>Plugin Version: $this->version</p>";
				
				echo "<p>Server OS: ".PHP_OS."</p>";
				
				echo "<p>Required PHP Version: 5.2+<br>";
				echo "Current PHP Version: " . phpversion() . "</p>";

				
				if( ini_get('safe_mode') ){
					echo "<p><font color='red'>PHP Safe Mode is enabled!<br><b>Disable Safe Mode in php.ini!</b></font></p>";
				}else{
					echo "<p>PHP Safe Mode: is disabled!</p>";
				}
				
				
				
				if(strpos(ini_get('disable_functions'), 'exec')  !== false){
					echo "<p><font color='red'>Disabled Functions: ".ini_get('disable_functions')."<br><b>Please enable 'exec' function in php.ini!</b></font></p>";
				}
				
				if( strpos(ini_get('disable_functions'), 'passthru') !== false){
					echo "<p><font color='red'>Disabled Functions: ".ini_get('disable_functions')."<br><b>Please enable 'passthru' function in php.ini!</b></font></p>";
				}
				
				
				echo "<p>";
				
				if(exec('type tar')){
					echo "Command 'tar' is enabled!</br>";
				}else{
					echo "Command 'tar' was not found!</br>";
				}
				
				if(exec('type gzip')){
					echo "Command 'gzip' is enabled!</br>";
				}else{
					echo "Command 'gzip' was not found!</br>";
				}
				
				if(exec('type bzip2')){
					echo "Command 'bzip2' is enabled!</br>";
				}else{
					echo "Command 'bzip2' was not found!</br>";
				}
				
				if(exec('type zip')){
					echo "Command 'zip' is enabled!</br>";
				}else{
					echo "Command 'zip' was not found!</br>";
				}
				
				if(exec('type mysqldump')){
					echo "Command 'mysqldump' is enabled!</br>";
				}else{
					echo "Command 'mysqldump' was not found!</br>";
				}
			
				echo "</p>";
				
				
							
				echo "<p>Memory Use: " . number_format(memory_get_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
				
				echo "<p>Peak Memory Use: " . number_format(memory_get_peak_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
				
				if(function_exists('sys_getloadavg')){
					$lav = sys_getloadavg();
					echo "<p>Server Load Average: ".$lav[0].", ".$lav[1].", ".$lav[2]."</p>";
				}
								
				?>

<?php $this->HtmlPrintBoxFooter(true); ?>



<?php $this->HtmlPrintBoxHeader('pl_resources',__('Plugin Resources','resources'),true); ?>

	<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/simple-backup/' target='_blank'>Plugin Homepage</a></p>
	<p><a href='http://mywebsiteadvisor.com/support/'  target='_blank'>Plugin Support</a></p>
	<p><a href='http://mywebsiteadvisor.com/contact-us/'  target='_blank'>Contact Us</a></p>
	<p><a href='http://wordpress.org/support/view/plugin-reviews/simple-backup?rate=5#postform'  target='_blank'>Rate and Review This Plugin</a></p>
		
<?php $this->HtmlPrintBoxFooter(true); ?>


<?php $this->HtmlPrintBoxHeader('pl_upgrade',__('Plugin Upgrades','upgrade'),true); ?>
	
	<p>
	<a href='http://mywebsiteadvisor.com/products-page/premium-wordpress-plugin/simple-backup-ultra/'  target='_blank'>Upgrade to Simple Backup Ultra!</a><br />
	<br />
	<b>Features:</b><br />
	-Automatic Backup Function<br />
	-Email Backup Notification<br />
	-Daily, Weekly or Monthly Schedule<br />
	-Much More!</br>
	</p>
	
<?php $this->HtmlPrintBoxFooter(true); ?>


<?php $this->HtmlPrintBoxHeader('more_plugins',__('More Plugins','more_plugins'),true); ?>
	
	<p><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/'  target='_blank'>Premium WordPress Plugins!</a></p>
	<p><a href='http://profiles.wordpress.org/MyWebsiteAdvisor/'  target='_blank'>Free Plugins on Wordpress.org!</a></p>
	<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/'  target='_blank'>Free Plugins on MyWebsiteAdvisor.com!</a></p>	
				
<?php $this->HtmlPrintBoxFooter(true); ?>


<?php $this->HtmlPrintBoxHeader('follow',__('Follow MyWebsiteAdvisor','follow'),true); ?>

	<p><a href='http://facebook.com/MyWebsiteAdvisor/'  target='_blank'>Follow us on Facebook!</a></p>
	<p><a href='http://twitter.com/MWebsiteAdvisor/'  target='_blank'>Follow us on Twitter!</a></p>
	<p><a href='http://www.youtube.com/mywebsiteadvisor'  target='_blank'>Watch us on YouTube!</a></p>
	<p><a href='http://MyWebsiteAdvisor.com/'  target='_blank'>Visit our Website!</a></p>	
	
<?php $this->HtmlPrintBoxFooter(true); ?>


</div>
</div>



	<div class="has-sidebar sm-padded" >			
		<div id="post-body-content" class="has-sidebar-content">
			<div class="meta-box-sortabless">
	
			<?php
			$opt_count = 0;
			foreach(get_option('wp_optimization_methods') as $opt_method){
				if($opt_method == true){
					$opt_count ++;
				}
			}
			?>
	
			<?php if( (get_option('db_backup') === "true") || (get_option('file_backup') === "true") || ($opt_count > 0) ) { ?>
			
				<?php $this->HtmlPrintBoxHeader('wm_dir',__('Create Backup','create-backups'),false); ?>					
					
				<?php
				echo "<form method='post' action='".admin_url()."tools.php?page=backup_files'>";
				echo "<input type='hidden' name='simple-backup' value='simple-backup'>";
				echo "<input type='submit' value='Create Backup' class='button-primary'>";
				echo "</form>";
				?>
				
				<?php $this->HtmlPrintBoxFooter(false); ?>
			
			<?php } ?>
			
			
				<form method='post'>
				

					<?php $this->HtmlPrintBoxHeader('wm_dir',__('Backup Settings','backup-settings'),false); ?>
					
					
						<table class='form-table'>
						<tr>
						<th>Database Backup Type</th>		
						<td>
						<?php $db_compression = $this->get_option('db_compression'); ?>
						<?php $db_bk_types = array(".sql.gz", ".sql.bz2", ".sql", ".sql.zip"); ?>
						
							<p><select  name='db_compression'>
							<option >Select a Backup Type...</option>
							
							<?php
								foreach($db_bk_types as $db_type){
									if ($db_type == $db_compression){
										echo "<option selected='selected'>$db_type</option>";
									}else{
										echo "<option>$db_type</option>";
									}
								}
							
							?>
							
						</select>
						</p>
						</td>
						</tr>
						
						<tr>
						<th>File Backup Type</th>		
						<td>
						<?php $file_compression = $this->get_option('file_compression'); ?>
						<?php $bk_types = array(".tar.gz", ".tar.bz2", ".tar", ".zip"); ?>
						
						<p><select  name='file_compression'>
							<option >Select a Backup Type...</option>
							
							<?php
								foreach($bk_types as $bk_type){
									if ($bk_type == $file_compression){
										echo "<option selected='selected'>$bk_type</option>";
									}else{
										echo "<option>$bk_type</option>";
									}
								}
							
							?>
							
						</select>
						</p>
						</td>
						</tr>
						
						<tr>
						<th>Backup Database</th>		
						<td>

						<?php $db_backup = $this->get_option('db_backup'); ?>
						<?php if($db_backup === "true"){$selected = "checked='checked'";}else{$selected="";}; ?>
						<p><label><input name='db_backup' type='checkbox' value='true' <?php echo $selected; ?> /> Backup Database</label></p>
						</td>
						</tr>
						
						<tr>
						<th>Backup Files</th>		
						<td>
						<?php $file_backup = $this->get_option('file_backup'); ?>
						<?php if($file_backup === "true"){$selected = "checked='checked'";}else{$selected="";}; ?>
						<p><label><input name='file_backup' type='checkbox' value='true' <?php echo $selected; ?> /> Backup Files</label></p>
						</td>
						</tr>
						
						<tr>
						<th>Display Debug Output</th>		
						<td>
						
						<?php $debug_enabled = $this->get_option('debug_enabled'); ?>
						<?php if($debug_enabled === "true"){$selected = "checked='checked'";}else{$selected="";}; ?>
						<p><label><input name='debug_enabled' type='checkbox' value='true' <?php echo $selected; ?> /> Backup Debugging Enabled</label></p>
						<p> (Useful for debugging!)</p>
						</td>
						</tr>
						</table>
						
						
						<p><input type="submit" name='Submit' value='Save Settings' class='button-primary' /></p>
						
					
					<?php $this->HtmlPrintBoxFooter(true); ?>
					
				
					
					<?php $this->HtmlPrintBoxHeader('wm_dir',__('Optimize WordPress Before Backup','backup-settings'),false); ?>
					
									
						<?php $wp_optimization_methods = get_option('wp_optimization_methods', array()); ?>
						
						<?php 
						
						global $wpdb; 
						
	
						?>
						
						
						<table class='form-table'>
						<tr>
						<th>Delete Spam Comments</th>		
						<td>
						<?php $selected=(isset($wp_optimization_methods['delete_spam_comments']) && $wp_optimization_methods['delete_spam_comments'] == 'true') ? "checked='checked'" : ""; ?>
						<p><label><input name='wp_optimization_methods[delete_spam_comments]' type='checkbox' value='true' <?php echo $selected; ?> /> Delete Spam Comments </label><br />
						Currently <?php echo $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'spam'"); ?> Spam Comments</p>
						</td>
						</tr>
						
						<tr>
						<th>Delete Unapproved Comments</th>		
						<td>
						<?php $selected=(isset($wp_optimization_methods['delete_unapproved_comments']) && $wp_optimization_methods['delete_unapproved_comments'] == 'true') ? "checked='checked'" : ""; ?>
						<p><label><input name='wp_optimization_methods[delete_unapproved_comments]' type='checkbox' value='true' <?php echo $selected; ?> /> Delete Unapproved Comments </label><br />
						Currently <?php echo $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '0'"); ?> Unapproved Comments</p>
						</td>
						</tr>
						
						<tr>
						<th>Delete Revisions</th>		
						<td>
						<?php $selected=(isset($wp_optimization_methods['delete_revisions']) && $wp_optimization_methods['delete_revisions'] == 'true') ? "checked='checked'" : ""; ?>
						<p><label><input name='wp_optimization_methods[delete_revisions]' type='checkbox' value='true' <?php echo $selected; ?> /> Delete Post Revisions </label><br />
						Currently <?php echo $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'revision'"); ?> Revisions</p>
						</td>
						</tr>
						
						<tr>
						<th>Delete Auto Drafts</th>		
						<td>
						<?php $selected=(isset($wp_optimization_methods['delete_auto_drafts']) && $wp_optimization_methods['delete_auto_drafts'] == 'true') ? "checked='checked'" : ""; ?>
						<p><label><input name='wp_optimization_methods[delete_auto_drafts]' type='checkbox' value='true' <?php echo $selected; ?> /> Delete Auto Drafts </label><br />
						Currently <?php echo $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'auto-draft'"); ?> Drafts</p>
						</td>
						</tr>
						
						<tr>
						<th>Delete Transient Options</th>		
						<td>
						<?php $selected=(isset($wp_optimization_methods['delete_transient_options']) && $wp_optimization_methods['delete_transient_options'] == 'true') ? "checked='checked'" : ""; ?>
						<p><label><input name='wp_optimization_methods[delete_transient_options]' type='checkbox' value='true' <?php echo $selected; ?> /> Delete Transient Options (Advanced)</label><br />
						Currently <?php echo $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '_transient_%'"); ?> Transient Options</p>
						</td>
						</tr>
						</table>
						
						
						
						<p><input type="submit" name='Submit' value='Save Settings' class='button-primary' /></p>
						
					
					<?php $this->HtmlPrintBoxFooter(true); ?>
					
					<?php $this->HtmlPrintBoxHeader('wm_dir',__('Optimize Database Before Backup','backup-settings'),false); ?>
					
					
						<table class='form-table'>
						<tr>
						<th>Check Database</th>		
						<td>					
						<?php $check_db_enabled = $this->get_option('check_db_enabled'); ?>
						<?php if($check_db_enabled === "true"){$selected = "checked='checked'";}else{$selected="";}; ?>
						<p><label><input name='check_db_enabled' type='checkbox' value='true' <?php echo $selected; ?> /> Check Database Before Backup </label></p>
						</td>
						</tr>
						
						<tr>
						<th>Repair Database</th>
						<td>					
						<?php $repair_db_enabled = $this->get_option('repair_db_enabled'); ?>
						<?php if($repair_db_enabled === "true"){$selected = "checked='checked'";}else{$selected="";}; ?>
						<p><label><input name='repair_db_enabled' type='checkbox' value='true' <?php echo $selected; ?> /> Repair Database Before Backup  (Advanced)</label></p>
						</td>
						</tr>
						
						<tr>
						<th>Optimize Database</th>
						<td>						
						<?php $optimize_db_enabled = $this->get_option('optimize_db_enabled'); ?>
						<?php if($optimize_db_enabled === "true"){$selected = "checked='checked'";}else{$selected="";}; ?>
						<p><label><input name='optimize_db_enabled' type='checkbox' value='true' <?php echo $selected; ?> /> Optimize Database Before Backup </label></p>
						</td>
						</tr>
						</table>
						
						<p><input type="submit" name='Submit' value='Save Settings' class='button-primary' /></p>
					
					<?php $this->HtmlPrintBoxFooter(true); ?>
					
					
							
				</form>
			
			
		
			
			<?php if( get_option('db_backup') === "true" ) { ?>
			
				<?php $this->HtmlPrintBoxHeader('wm_dir',__('Create Backup','create-backups'),false); ?>					
					
				<?php
				echo "<form method='post' action='".admin_url()."tools.php?page=backup_files'>";
				echo "<input type='hidden' name='simple-backup' value='simple-backup'>";
				echo "<input type='submit' value='Create Backup' class='button-primary'>";
				echo "</form>";
				?>
				
				<?php $this->HtmlPrintBoxFooter(false); ?>
			
			<?php } ?>
			
	
</div></div></div></div>

</div>


<?php
	}
	
}

?>