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
			$links[] = '<a href="http://MyWebsiteAdvisor.com">Visit Us Online</a>';
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
				
				echo "<p>Memory Use: " . number_format(memory_get_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
				echo "<p>Peak Memory Use: " . number_format(memory_get_peak_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
				
				?>

<?php $this->HtmlPrintBoxFooter(true); ?>



<?php $this->HtmlPrintBoxHeader('pl_resources',__('Plugin Resources','resources'),true); ?>
	<p><a href='http://mywebsiteadvisor.com/wordpress-plugins/simple-backup' target='_blank'>Plugin Homepage</a></p>
	<p><a href='http://mywebsiteadvisor.com/contact-us'  target='_blank'>Plugin Support</a></p>
	<p><a href='http://mywebsiteadvisor.com/contact-us'  target='_blank'>Suggest a Feature</a></p>
<?php $this->HtmlPrintBoxFooter(true); ?>

</div>
</div>



	<div class="has-sidebar sm-padded" >			
		<div id="post-body-content" class="has-sidebar-content">
			<div class="meta-box-sortabless">
			
								
			<?php $this->HtmlPrintBoxHeader('wm_dir',__('Create Backup','watermark-directory'),false); ?>					
				
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
			echo "<input type='hidden' name='watermark_backup' value='$base_dir'>";
			echo "<input type='submit' value='Create Backup'>";
			echo "</form>";
			
			$bk_dir = ABSPATH."simple-backup";
			
			if(array_key_exists('watermark_backup', $_POST)) {
			
				$bk_name = "$bk_dir/backup-".date('Y-m-d-His').".tgz";
				$src_name = ABSPATH;
				$exclude = $bk_dir;
				
				$command = "tar cvfz $bk_name $src_name --exclude=$exclude";
				
				echo "<br>";
				echo "executing command: $command";
				echo "<br>";
				exec($command);
				echo "<br>";
				
			
			}
			
			
			
			
			if(!is_dir($bk_dir)){
				mkdir($bk_dir);
			}
			
			if(!is_dir($bk_dir)){
				echo "Can not access: $bk_dir<br>";
			}
			
			$iterator = new RecursiveDirectoryIterator($bk_dir);
			foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as  $file) {
				$file_info = pathinfo($file->getFilename());
				if($file->isFile() && strtolower($file_info['extension']) == 'tgz'){ //create list of files
				
					$fileUrl = site_url()."/simple-backup/".$file->getFilename();
					$filePath = ABSPATH."/simple-backup/".$file->getFilename();
					
					echo "<p><a  href='$fileUrl' target='_blank'>" . $file->getFilename() . "</a> : ";
					echo number_format(filesize($filePath), 0) . " bytes   ";
					echo date("Y-m-d H:i:s", filectime($filePath));
					echo "</p>";
					
				}
			}
		
		
			?>
		<?php $this->HtmlPrintBoxFooter(false); ?>
		
	
		
</div></div></div></div>

</div>


<?php
	}
	
}

?>