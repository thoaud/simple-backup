<?php


class Simple_Backup_Manager{

	private $backup_table;

	function simple_cron_admin_menu(){
        global $simple_backup_file_manager_page;
		$simple_backup_file_manager_page = add_submenu_page( 'tools.php', __('Simple Backup File Manager', 'simple_backup'), __('Backup Manager', 'simple_backup'), 'manage_options', 'backup_files', array(&$this, 'backup_manager') );
		
    }
	
	
	
	public function backup_processor_form(){
	

		//echo "<form method='post'>";
		//echo "<input type='hidden' name='simple-backup' value='$base_dir'>";
		//echo "<input type='hidden' name='simple-backup' value='simple-backup'>";
		//echo "<input type='submit' value='Create Backup' class='button-primary'>";
		//echo "</form>";
		
		$bk_dir = ABSPATH."simple-backup";
		
		if(!is_dir($bk_dir)){
			mkdir($bk_dir);
		}
		
		if(!is_dir($bk_dir)){
			echo "Can not access: $bk_dir<br>";
		}
		
		
		
		if(array_key_exists('simple-backup', $_POST)) {
		
			set_time_limit(0);
		
			echo "<div style='overflow-y:scroll; max-height:250px; border:1px solid #CCC; padding:10px;'>";
			
			
			
			
			if(get_option('wp_optimization_methods') ){

				$this->performWordPressOptimization();

			}
			
			if(get_option('check_db_enabled') === "true"){

				$this->performDatabaseCheck();

			}
			
			if(get_option('repair_db_enabled') === "true"){

				$this->performDatabaseRepair();

			}
			
			if(get_option('optimize_db_enabled') === "true"){

				$this->performDatabaseOptimization();

			}
			
			if(get_option('db_backup') === "true"){

				$this->performDatabaseBackup();

			}
			
			if(get_option('file_backup') === "true"){
				
				$this->performWebsiteBackup();
				
			}
			
			echo "</div>";
			
		}
			

	}
	
	
		
	
	public function performWordPressOptimization(){
	
		global $wpdb;
		
		$optimization_queries = array(
			'delete_spam_comments' => "DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'",
			'delete_unapproved_comments' => "DELETE FROM $wpdb->comments WHERE comment_approved = '0'",
			'delete_revisions' => "DELETE FROM $wpdb->posts WHERE post_type = 'revision'",
			'delete_auto_drafts' => "DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'",
			'delete_transient_options' => "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%'"
		);
		
		$wp_optimization_methods = get_option('wp_optimization_methods');
	
		$queries = $optimization_queries;
	
		foreach($queries as $method => $query){
			if(isset($wp_optimization_methods[$method]) && $wp_optimization_methods[$method] === "true"){
			
				echo "<p>Performing Optimization: " . $method."<br>";
				$result = $wpdb->query($query);
				echo "$result items deleted.</p>";
						
			}
		}
	}
	
	
	public function performDatabaseCheck(){
	
		$debug_enabled = get_option('debug_enabled');

		echo "Checking Database...<br>";
		
		$local_query = 'SHOW TABLE STATUS FROM `'. DB_NAME.'`';
		$result = mysql_query($local_query);
		if (mysql_num_rows($result)){
			
			while ($row = mysql_fetch_array($result)){
			
				$check_query = "CHECK TABLE ".$row['Name'];
				$check_result = mysql_query($check_query);
				if (mysql_num_rows($check_result)){
					while($rrow = mysql_fetch_assoc($check_result)){
						if( $debug_enabled == "true"){
							echo "Table: " . $row['Name'] ." ". $rrow['Msg_text'];
							echo "<br>";
						}
					}
				}
				
				//$initial_table_size += $table_size; 
				
			}
			
			echo "Done!<br>";
			
		}
	
		echo "<br>";
	
	}



	public function performDatabaseRepair(){
	
		$debug_enabled = get_option('debug_enabled');

		echo "Repairing Database...<br>";
		
		$local_query = 'SHOW TABLE STATUS FROM `'. DB_NAME.'`';
		$result = mysql_query($local_query);
		if (mysql_num_rows($result)){
			
			while ($row = mysql_fetch_array($result)){
			
				$check_query = "REPAIR TABLE ".$row['Name'];
				$check_result = mysql_query($check_query);
				if (mysql_num_rows($check_result)){
					while($rrow = mysql_fetch_assoc($check_result)){
						if( $debug_enabled == "true"){
							echo "Table: " . $row['Name'] ." ". $rrow['Msg_text'];
							echo "<br>";
						}
					}
				}
				
			}
			
			echo "Done!<br>";
			
		}
	
		echo "<br>";
	
	}
	
	
	public function performDatabaseOptimization(){
		
		$initial_table_size = 0;
		$final_table_size = 0;
		
		$debug_enabled = get_option('debug_enabled');
		
		
		
		echo "Optimizing Database...<br>";
		
		$local_query = 'SHOW TABLE STATUS FROM `'. DB_NAME.'`';
		$result = mysql_query($local_query);
		if (mysql_num_rows($result)){
			
			while ($row = mysql_fetch_array($result)){
				//var_dump($row);
				
				$table_size = ($row[ "Data_length" ] + $row[ "Index_length" ]) / 1024;
				
				$optimize_query = "OPTIMIZE TABLE ".$row['Name'];
				if(mysql_query($optimize_query)){
				
					if( $debug_enabled == "true"){
						echo "Table: " . $row['Name'] . " optimized!";
						echo "<br>";
					}
				}
				
				$initial_table_size += $table_size; 
				
			}
			
			echo "Done!<br>";
			
		}
		
		
		
		
		$local_query = 'SHOW TABLE STATUS FROM `'. DB_NAME.'`';
		$result = mysql_query($local_query);
		if (mysql_num_rows($result)){
			while ($row = mysql_fetch_array($result)){
				$table_size = ($row[ "Data_length" ] + $row[ "Index_length" ]) / 1024;
				$final_table_size += $table_size;
			}
		}
		
		
		
		echo "<br>";
		echo "Initial DB Size: " . number_format($initial_table_size, 2) . " KB<br>";
		echo "Final DB Size: " . number_format($final_table_size, 2) . " KB<br>";
		
		$space_saved = $initial_table_size - $final_table_size;
		$opt_pctg = 100 * ($space_saved / $initial_table_size);
		echo "Space Saved: " . number_format($space_saved,2) . " KB  (" .  number_format($opt_pctg, 2) . "%)<br>";
		echo "<br>";
	
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
		if( get_option('debug_enabled') == "true"){
			exec($command);
			
			ob_start();
			passthru($base_bk_command);
			$debug_output = htmlentities(ob_get_clean());
			echo $debug_output;
			
			
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
			$command = "tar cvfz $bk_name --exclude=$exclude $src_name ";
			
		}elseif($file_compression == ".tar.bz2"){
			$bk_name = "$bk_dir/backup-".date('Y-m-d-His').".tar.bz2";
			$command = "tar jcvf $bk_name --exclude=$exclude $src_name";
			
		}elseif($file_compression == ".tar"){
			$bk_name = "$bk_dir/backup-".date('Y-m-d-His').".tar";
			$command = "tar cvf $bk_name --exclude=$exclude $src_name";
			
		}elseif($file_compression == ".zip"){
			$bk_name = "$bk_dir/backup-".date('Y-m-d-His').".zip";
			$command = "zip -r $bk_name $src_name -x $exclude/*";
		}
		
		
	
		
		echo "<br>";
		echo "<b>Executing Command:</b><br>$command";
		
		ob_flush();
		flush();
		
		echo "<br>";
		if( get_option('debug_enabled') == "true"){
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


	
	public function get_backup_files(){
	
		$bk_dir = ABSPATH."simple-backup";
			
		if(!is_dir($bk_dir)){
			mkdir($bk_dir);
		}
		
		if(!is_dir($bk_dir)){
			echo "Can not access: $bk_dir<br>";
		}


		$tz = get_option('timezone_string') ? get_option('timezone_string') : "UTC+".get_option('gmt_offset');

		try {
			$date = new DateTime("@".time());
			$date->setTimezone(new DateTimeZone($tz)); 
		} catch (Exception $e) {
			echo '<div class="error" style="padding:10px;">';
			echo "ERROR: <br />";
			echo "Your Timezone is currently set to: " . $tz. "<br />";
			echo "Please Choose A Timezone like 'Chicago' on the <a href='".admin_url()."options-general.php'>Settings Page</a><br />";
			echo "</div>";
		}
		
				

		$allowed_file_types = array('gz', 'sql', 'zip', 'tar', 'bz2');
		
		$bk_file_count = 0;
		
		$bk_files = array();
		
		$iterator = new RecursiveDirectoryIterator($bk_dir);
		foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as  $file) {
			$file_info = pathinfo($file->getFilename());
			if($file->isFile() && in_array(strtolower($file_info['extension']), $allowed_file_types)){ //create list of files
			
				$fileUrl = site_url()."/simple-backup/".$file->getFilename();
				$filePath = ABSPATH."/simple-backup/".$file->getFilename();
		
				
				try {
					
					$date = new DateTime("@".filectime($filePath));
					$date->setTimezone(new DateTimeZone(get_option('timezone_string'))); 
				} catch (Exception $e) {
				

				}

				 
				
				$bk_files[ $bk_file_count ]['date'] = $date->format('Y-m-d g:i:s A T');
				$bk_files[ $bk_file_count ]['timestamp'] = $date->getTimestamp();
				$bk_files[ $bk_file_count ]['filename'] = $file->getFilename();
				$bk_files[ $bk_file_count ]['size'] = size_format(filesize($filePath));
				$bk_files[ $bk_file_count ]['link'] = $fileUrl;
				
				$bk_file_count++;
				
			}
		}


		return $bk_files;
		
	}
	



	function screen_options(){

        //execute only on login_log page, othewise return null
        $page = ( isset($_GET['page']) ) ? esc_attr($_GET['page']) : false;
        if( 'backup_files' != $page )
            return;

        $current_screen = get_current_screen();

        //define options
        $per_page_field = 'per_page';
        $per_page_option = $current_screen->id . '_' . $per_page_field;

        //Save options that were applied
        if( isset($_REQUEST['wp_screen_options']) && isset($_REQUEST['wp_screen_options']['value']) ){
            update_option( $per_page_option, esc_html($_REQUEST['wp_screen_options']['value']) );
        }

        //prepare options for display

        //if per page option is not set, use default
        $per_page_val = get_option($per_page_option, 20);
        $args = array('label' => __('Files', 'simple-backup'), 'default' => $per_page_val );

        //display options
        add_screen_option($per_page_field, $args);
        $_per_page = get_option('backup_files_per_page');

        //create custom list table class to display  data
        $this->backup_table = new Backup_List_Table;
		
    }



	function backup_manager(){
	
		echo '<style type="text/css">';
		echo '.wp-list-table .column-date { width: 20%; }';
		echo '.wp-list-table .column-size { width: 20%; }';
		echo '</style>';
		
		
		echo Simple_Backup_Admin::display_social_media();
		
		
		echo '<div class="wrap" id="sm_div">';
		
		echo '<div id="icon-tools" class="icon32"><br /></div>';
        echo '<h2>' . __('Simple Backup File Manager', 'simple-backup') . '</h2>';
		
		//echo "<p float='left'><a  href='".get_option('siteurl')."/wp-admin/options-general.php?page=simple-backup/plugin-admin.php' >View Simple Backup Plugin Settings</a></p>";
				
		echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
		
		echo '<div id="post-body" class="metabox-holder columns-2">';


		$this->backup_processor_form();

		$backup_table = $this->backup_table;

		$backup_table->prepare_items();
		$backup_table->display();
		
		
		echo "</div></div></div>";
		

		
	}


}


?>