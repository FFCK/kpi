<?php
class FAQsPlugin_Importer
{
	var $root;
	
    public function __construct($root)
    {
		$this->root = $root;
	}
	
	//convert CSV to array
	private function csv_to_array($filename='', $delimiter=','){
		if(!file_exists($filename) || !is_readable($filename))
			return FALSE;

		$header = NULL;
		$data = array();
		
		if (($handle = fopen($filename, 'r')) !== FALSE)
		{
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
			{
				if(!$header){
					$header = $row;
				} else {
					$data[] = array_combine($header, $row);
				}
			}
			fclose($handle);
		}
		return $data;
	}
	
	//process data from CSV import
	private function import_faqs_from_csv($faqs_file){	
		//increase execution time before beginning import, as this could take a while
		set_time_limit(0);		
		
		$faqs = $this->csv_to_array($faqs_file);
		
		foreach($faqs as $faq){	//look for a location with the same address and phone number				
			//defaults
			$the_question = '';
			$the_answer = '';

			if (isset($faq['Question'])) {
				$the_question = strip_tags($faq['Question']);
			}

			if (isset ($faq['Answer'])) {
				$the_answer = strip_tags($faq['Answer']);
			}
			
			//if not found, insert this one
			$postslist = get_page_by_title( $the_question, OBJECT, 'faq' );
			
			//if this is empty, a match wasn't found and therefore we are safe to insert
			if(empty($postslist)){					
				//insert the questions				
				$tags = array();
			   
				$post = array(
					'post_title'    => $the_question,
					'post_content' 	=> $the_answer,
					'post_category' => array(1),  // custom taxonomies too, needs to be an array
					'tags_input'    => $tags,
					'post_status'   => 'publish',
					'post_type'     => 'faq'
				);
			
				$new_id = wp_insert_post($post);
			   
				//update_post_meta( $new_id, '_ikcf_street_address', $street_address );
			   
				$inserted = true;
				echo "<p>Successfully imported '{$the_question}'!</p>";
				
			} else { //rejected as duplicate
				echo "<p>Could not import <em>{$the_question}</em>; rejected as Duplicate</p>";
			}
		}
	}
	
	//displays fields to allow user to upload and import a CSV of faqs
	//if a file has been uploaded, this will dispatch the file to the import function
	public function csv_importer(){
		echo '<form method="POST" action="" enctype="multipart/form-data">';
		
		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';

		if ( !class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) )
				require_once $class_wp_importer;
		}		
		
		if(empty($_FILES)){		
			echo "<p>Use the below form to upload your CSV file for importing.</p>";
			echo "<p><strong>Example CSV Data:</strong></p>";
			echo "<p><code>Question, Answer</code></p>";
			echo "<p><strong>Please Note:</strong> the first line of the CSV will need to match the text in the above example, for the Import to work.  Depending on your server settings, you may need to run the import several times if your script times out.</p>";

			echo '<div class="gp_upload_file_wrapper">';
			wp_import_upload_form( add_query_arg('step', 1) );
			echo '</div>';
		} else {
			$file = wp_import_handle_upload();

			if ( isset( $file['error'] ) ) {
				echo '<p><strong>' . 'Sorry, there has been an error.' . '</strong><br />';
				echo esc_html( $file['error'] ) . '</p>';
				return false;
			} else if ( ! file_exists( $file['file'] ) ) {
				echo '<p><strong>' . 'Sorry, there has been an error.' . '</strong><br />';
				printf( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', esc_html( $file['file'] ) );
				echo '</p>';
				return false;
			}
			
			$fileid = (int) $file['id'];
			$file = get_attached_file($fileid);
			$result = $this->import_faqs_from_csv($file);
			
			if ( is_wp_error( $result ) ){
				echo $result;
			} else {
				echo "<p>FAQs successfully imported!</p>";
			}
		}
		echo '</form>';
	}
}