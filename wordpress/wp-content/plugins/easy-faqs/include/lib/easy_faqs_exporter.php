<?php
class FAQsPlugin_Exporter
{	
	public function output_form()
	{
		?>
		<form method="POST" action="">			
			<p>Click the "Export My FAQs" button below to download a CSV file of your FAQs.</p>
			<input type="hidden" name="_gp_do_export" value="_gp_do_export" />
			<p class="submit">
				<input type="submit" class="button" value="Export My FAQs" />
			</p>
		</form>
		<?php
	}
	
	public function process_export($filename = "faqs-export.csv"){					
		//load faqs
		$args = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'post_date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'faq',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 				
		);
		
		$faqs = get_posts($args);
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$filename}");
		header("Expires: 0");
		header("Pragma: public");
		
		$fh = @fopen( 'php://output', 'w' );
		
		$headerDisplayed = false;
			
		foreach($faqs as $faq){
			if ( !$headerDisplayed ) {
				// Use the keys from $data as the titles
				fputcsv($fh, array('Question','Answer'));
				$headerDisplayed = true;
			}
			
			fputcsv($fh, array($faq->post_title, $faq->post_content));
		}
		
		// Close the file
		fclose($fh);
	}
}