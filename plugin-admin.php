<?php

class Simple_Backup_Admin extends Simple_Backup {
	/**
	 * Error messages to diplay
	 *
	 * @var array
	 */
	private $_messages = array();
	
	
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
		if(array_key_exists('option_name', $_GET) && array_key_exists('option_value', $_GET)
			&& in_array($_GET['option_name'], $allowed_options)) {
			update_option($_GET['option_name'], $_GET['option_value']);
			
			header("Location: " . $this->_settings_url);
			die();	
		}elseif(array_key_exists('delete_backup_file', $_GET)){
			$this->deleteBackupFile($_GET['delete_backup_file']);
			
			header("Location: " . $this->_settings_url);
			die();	
		} else {
			// register installer function
			register_activation_hook(TW_LOADER, array(&$this, 'activateSimpleBackup'));
		
			// add plugin "Settings" action on plugin list
			add_action('plugin_action_links_' . plugin_basename(SB_LOADER), array(&$this, 'add_plugin_actions'));
			
			// add links for plugin help, donations,...
			add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);
			
			// push options page link, when generating admin menu
			add_action('admin_menu', array(&$this, 'adminMenu'));
	
			
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
			$links[] = '<a href="http://MyWebsiteAdvisor.com/">Visit Us Online</a>';
		}
		
		return $links;
	}
	
	/**
	 * Add menu entry for Simple Backup settings and attach style and script include methods
	 */
	public function adminMenu() {		
		// add option in admin menu, for setting details on watermarking
		$plugin_page = add_options_page('Simple Backup Plugin Options', 'Simple Backup', 8, __FILE__, array(&$this, 'optionsPage'));

		add_action('admin_print_styles-' . $plugin_page,     array(&$this, 'installStyles'));
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


	public function performDatabaseBackupDebug(){
		$bk_dir = ABSPATH."simple-backup";
		$db_bk_file = $bk_dir . "/db_backup_".date('Y-m-d_His').".sql";
		$command = "mysqldump --single-transaction -u ".DB_USER." -p'".DB_PASSWORD."' ".DB_NAME." -h ".DB_HOST;
			
	}

	public function performDatabaseBackup(){
	
		$bk_dir = ABSPATH."simple-backup";
		
		$base_bk_command = "mysqldump --single-transaction -u ".DB_USER." -p'".DB_PASSWORD."' ".DB_NAME." -h ".DB_HOST;
		
		$db_compression = get_option('db_compression');
		
		//the syntax for mysqldump requires that there is NOT a space between the -p and the password
		if($db_compression == ".sql"){
			$db_bk_file = $bk_dir . "/db_backup_".date('Y-m-d_His').".sql";
			$command =  $base_bk_command . " > $db_bk_file";
			
		}elseif($db_compression == ".sql.gz"){
			$db_bk_file = $bk_dir . "/db_backup_".date('Y-m-d_His').".sql.gz";
			$command = $base_bk_command . " | gzip -c > $db_bk_file ";
			
		}elseif($db_compression == ".sql.bz2"){
			$db_bk_file = $bk_dir . "/db_backup_".date('Y-m-d_His').".sql.bz2";
			$command = $base_bk_command . " | bzip2 -cq9 > $db_bk_file";
	
		}elseif($db_compression == ".sql.zip"){
			$db_bk_file = $bk_dir . "/db_backup_".date('Y-m-d_His').".sql.zip";
			$command = $base_bk_command . " | zip > $db_bk_file";
		}
		
	
		echo "<br>";
		echo "<b>Executing Command:</b><br>$command";
		
		ob_flush();
		flush();
		
		echo "<br>";
		if( $this->get_option('debug_enabled') == "true"){
			exec($command);
			passthru($base_bk_command);
			
		}else{
			exec($command);
		};
		echo "<br>";
		
		echo "Done!";
		echo "<br>";
		
		ob_flush();
		flush();
				
	}
	
	
	public function performWebsiteBackup(){
	
		$bk_dir = ABSPATH."simple-backup";
		//$bk_name = "$bk_dir/backup-".date('Y-m-d-His').".tar.gz";
		$src_name = ABSPATH;
		$exclude = $bk_dir;
		
		$file_compression = get_option('file_compression');
		
		
		if($file_compression == ".tar.gz"){
			$bk_name = "$bk_dir/backup-".date('Y-m-d-His').".tar.gz";
			$command = "tar cvfz $bk_name $src_name --exclude=$exclude";
			
		}elseif($file_compression == ".tar.bz2"){
			$bk_name = "$bk_dir/backup-".date('Y-m-d-His').".tar.bz2";
			$command = "tar jcvf $bk_name $src_name --exclude=$exclude";
			
		}elseif($file_compression == ".tar"){
			$bk_name = "$bk_dir/backup-".date('Y-m-d-His').".tar";
			$command = "tar cvf $bk_name $src_name --exclude=$exclude";
			
		}elseif($file_compression == ".zip"){
			$bk_name = "$bk_dir/backup-".date('Y-m-d-His').".zip";
			$command = "zip -r $bk_name $src_name -x $exclude/*";
		}
		
		
	
		
		echo "<br>";
		echo "<b>Executing Command:</b><br>$command";
		
		ob_flush();
		flush();
		
		echo "<br>";
		if( $this->get_option('debug_enabled') == "true"){
			passthru($command);
		}else{
			exec($command);
		};
		echo "<br>";
		
		echo "Done!";
		echo "<br>";
		
		ob_flush();
		flush();
	
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

	
									  
<script type="text/javascript">var wpurl = "<?php bloginfo('wpurl'); ?>";</script>

<style>

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
	}(document, 'script', 'facebook-jssdk'));</script>
	
	<div class="fb-like" data-href="http://www.facebook.com/MyWebsiteAdvisor" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false"></div>
	
	
	<a href="https://twitter.com/MWebsiteAdvisor" class="twitter-follow-button" data-show-count="false"  >Follow @MWebsiteAdvisor</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>


</div>

<div class="wrap" id="sm_div">

	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Simple Backup Plugin Settings</h2>
	
		
		
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
			
<?php $this->HtmlPrintBoxHeader('pl_diag',__('Plugin Diagnostic Check','diagnostic'),true); ?>

				<?php
				
				echo "<p>Server OS: ".PHP_OS."</p>";
				
				echo "<p>Required PHP Version: 5.0+<br>";
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
				
				$lav = sys_getloadavg();
				echo "<p>Server Load Average: ".$lav[0].", ".$lav[1].", ".$lav[2]."</p>";
				
				?>

<?php $this->HtmlPrintBoxFooter(true); ?>



<?php $this->HtmlPrintBoxHeader('pl_resources',__('Plugin Resources','resources'),true); ?>

	<p><a href='http://mywebsiteadvisor.com/wordpress-plugins/simple-backup/' target='_blank'>Plugin Homepage</a></p>
	<p><a href='http://mywebsiteadvisor.com/contact-us/'  target='_blank'>Plugin Support</a></p>
	<p><a href='http://mywebsiteadvisor.com/contact-us/'  target='_blank'>Suggest a Feature</a></p>
	
<?php $this->HtmlPrintBoxFooter(true); ?>


<?php $this->HtmlPrintBoxHeader('pl_upgrade',__('Plugin Upgrades','upgrade'),true); ?>
	
	<p>
	<a href='http://mywebsiteadvisor.com/products-page/premium-wordpress-plugin/simple-backup-ultra/'  target='_blank'>Upgrade to Simple Backup Ultra!</a><br />
	<br />
	<b>Features:</b><br />
	-Automatic Backup Function<br />
	-Email Backup Notification<br />
	-Daily, Weekly or Monthly Schedule<br />
	</p>
	
<?php $this->HtmlPrintBoxFooter(true); ?>


<?php $this->HtmlPrintBoxHeader('more_plugins',__('More Plugins','more_plugins'),true); ?>
	
	<p><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/'  target='_blank'>Premium WordPress Plugins!</a></p>
	<p><a href='http://profiles.wordpress.org/MyWebsiteAdvisor/'  target='_blank'>Free Plugins on Wordpress.org!</a></p>
	<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/'  target='_blank'>Free Plugins on Our Website!</a></p>	
				
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
	
	
	
			<?php $this->HtmlPrintBoxHeader('wm_dir',__('Simple Backup Settings','backup-settings'),false); ?>	
			
				<form method='post'>
				
					<?php $db_compression = $this->get_option('db_compression'); ?>
					<?php $db_bk_types = array(".sql.gz", ".sql.bz2", ".sql", ".sql.zip"); ?>
					
						<p><b>Database Backup Type:</b><br /><select  name='db_compression'>
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
					
					
					
					<?php $file_compression = $this->get_option('file_compression'); ?>
					<?php $bk_types = array(".tar.gz", ".tar.bz2", ".tar", ".zip"); ?>
					
					<p><b>File Backup Type:</b><br /><select  name='file_compression'>
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




					
					
					<p><b>What do you want to back up?</b></p>
				
					<?php $db_backup = $this->get_option('db_backup'); ?>
					<?php if($db_backup === "true"){$selected = "checked='checked'";}else{$selected="";}; ?>
					<p><input name='db_backup' type='checkbox' value='true' <?php echo $selected; ?> /> Backup Database</p>
					
					
					<?php $file_backup = $this->get_option('file_backup'); ?>
					<?php if($file_backup === "true"){$selected = "checked='checked'";}else{$selected="";}; ?>
					<p><input name='file_backup' type='checkbox' value='true' <?php echo $selected; ?> /> Backup Files</p>
			
					
	
					<p><b>Display Backup Command Output?</b> (Useful for debugging!)</p>
					
					<?php $debug_enabled = $this->get_option('debug_enabled'); ?>
					<?php if($debug_enabled === "true"){$selected = "checked='checked'";}else{$selected="";}; ?>
					<p><input name='debug_enabled' type='checkbox' value='true' <?php echo $selected; ?> /> Backup Debugging Enabled</p>
	
				
				
					
					<input type="submit" name='Submit' value='Save Settings' />
				
				</form>
			
			
			<?php $this->HtmlPrintBoxFooter(false); ?>
			
			
			
						
			<?php $this->HtmlPrintBoxHeader('wm_dir',__('Create Backup','create-backups'),false); ?>					
				
				<?php 
					/**
					$base_dir = $_SERVER['DOCUMENT_ROOT'];
					
					$dir_info = $this->listFiles($base_dir);

					echo "<form method='post'><select name='base_dir'>";
						foreach($dir_info['dirs'] as $dir){
							$selected = "";
							if($_POST['base_dir'] == $dir){
								$selected = "selected='selected'";
							}
							echo "<option $selected>$dir</option>";
							
						}
					echo "</select> ";
					echo " <input type='submit'>";
					echo "</form>";
					echo "<br>";
					echo "<br>";
					
					
					echo "<b>" . count($dir_info['files']) . "</b> files found in: <b>" . str_replace($_SERVER['DOCUMENT_ROOT'], '', $base_dir) . "</b><br>";
					echo "<br>";
					
					echo "<form method='post'>";
					echo "<input type='hidden' name='bulk_watermark_action'>";
					echo "<div style='overflow-y:scroll; height:250px; border:1px solid grey; padding:5px;'>";
					foreach($dir_info['files'] as $file){
						echo $file;
					}
					echo "</div>";
					echo "<br>";
					echo "<input type='submit' value='Apply Bulk Watermark'>";
					echo "</form>";
					**/
				?>
				

			<?php
			echo "<form method='post'>";
			echo "<input type='hidden' name='simple-backup' value='$base_dir'>";
			echo "<input type='submit' value='Create Backup'>";
			echo "</form>";
			
			$bk_dir = ABSPATH."simple-backup";
			
			if(!is_dir($bk_dir)){
				mkdir($bk_dir);
			}
			
			if(!is_dir($bk_dir)){
				echo "Can not access: $bk_dir<br>";
			}
			
			
			
			if(array_key_exists('simple-backup', $_POST)) {
			
				set_time_limit(0);
			
				echo "<div style='overflow:scroll; height:250px;'>";
				
				if($this->get_option('db_backup') === "true"){

					$this->performDatabaseBackup();

				}
				
				if($this->get_option('file_backup') === "true"){
					
					$this->performWebsiteBackup();
					
				}
				
				echo "</div>";
				
			}
			
			
			
			?>
			
			<?php $this->HtmlPrintBoxFooter(false); ?>
			
			
			
			
			<?php $this->HtmlPrintBoxHeader('wm_dir',__('Download Backups','download-backups'),false); ?>	
			<?php
		
			$allowed_file_types = array('gz', 'sql', 'zip', 'tar', 'bz2');
			
			$bk_file_count = 0;
			
			echo "<table width='100%'>";
			echo "<tr>";
				echo "<td>Delete</td>";
				echo "<td>Download</td>";
				echo "<td>Size</td>";
				echo "<td>Date</td>";
			echo "</tr>";
			
			$iterator = new RecursiveDirectoryIterator($bk_dir);
			foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as  $file) {
				$file_info = pathinfo($file->getFilename());
				if($file->isFile() && in_array(strtolower($file_info['extension']), $allowed_file_types)){ //create list of files
				
					$fileUrl = site_url()."/simple-backup/".$file->getFilename();
					$filePath = ABSPATH."/simple-backup/".$file->getFilename();
					
					echo "<tr>";
					echo "<td><a href='".$this->_settings_url."&delete_backup_file=".$file->getFilename()."' title='Delete Backup File'>X</a></td>";
					echo "<td><a  href='$fileUrl' target='_blank' title='Download Backup File'>" . $file->getFilename() . "</a></td>";
					echo "<td>" . number_format(filesize($filePath), 0) . " bytes</td>";
					echo "<td>" . date("Y-m-d H:i:s", filectime($filePath)) . "</td>";
					echo "</tr>";
					
					$bk_file_count++;
					
				}
			}
			
			echo "</table><br>";
			
			if($bk_file_count == 0){
			
				echo "No backup files have been created yet.<br>Please click on the 'Create Backup' button above to create a backup.";
			
			}else{
			
				echo "Please click on a file to download it, click on the 'X' next to each file to delete it once it has finished downloading.<br>";
				echo "<b>Remember if you are doing a backup of both files and database, you need to download both backup files!<b>";
			
			}
		


			?>
		<?php $this->HtmlPrintBoxFooter(false); ?>
		
	
		
</div></div></div></div>

</div>


<?php
	}
	
}

?>