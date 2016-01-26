<?php

class EasyFAQs_SearchFAQs
{
	var $root = false;
	
	function __construct($root)
	{
		$this->root = $root;
	}
	
	function outputSearchForm($atts, $content = '')
	{
		$query = !empty($_REQUEST['search_faqs']) ? strip_tags($_REQUEST['search_faqs']) : '';
		$wrapper_class = 'easy_faqs_search_form';
		$wrapper_class = apply_filters( 'easy_faqs_search_wrapper_class', $wrapper_class, array('query' => $query) );
		
		$form_html = $this->getSearchFormTemplate($query);
		if ( !empty($query) ) {
			$form_html .= $this->getSearchResultsHTML($query);
		}
		
		$search_form = sprintf('<div class="%s">%s</div>', $wrapper_class, $form_html);		
		return $search_form;
	}
	
	function getSearchFormTemplate($query = '')
	{
		// add heading HTML
		$heading_html = $this->getSearchFormHeader();
		$heading_html = apply_filters('easy_faqs_search_heading_html', $heading_html);
		
		// add the form HTML
		$form_template = 
		'<form>
			<input name="search_faqs" id="search_faqs" value="%s" />
			<button type="submit" class="btn">%s</button>
		</form>';
		
		// allow button label to be overriden by a filter
		$button_label = 'Search';
		$button_label = apply_filters('easy_faqs_search_button_label', $button_label);
		
		// combine template + vars into the form HTML
		$form_html = sprintf($form_template, htmlentities($query), $button_label);
		
		// allow search from to be overridden
		$form_html = apply_filters( 'easy_faqs_search_form_html', $form_html, array('query' => $query, 'button_label' => $button_label) );
		
		// return the finished HTML
		return $heading_html . $form_html;
	}
	
	function getSearchFormHeader()
	{
		// default class and text
		$heading_class = 'easy_faqs_search_heading';
		$heading_text = 'Search Our Knowledgebase';
		
		// apply filters now to allow class + text to be overridden
		$heading_class = apply_filters( 'easy_faqs_search_heading_class', $heading_class );
		$heading_text = apply_filters( 'easy_faqs_search_heading_text', $heading_text );
		
		// return the formatted heading
		return sprintf('<h2 class="%s">%s</h2>', $heading_class, $heading_text);
	}
	
	function getSearchResultsHTML($search_query)
	{		
		$loop = $this->get_search_results($search_query);
		if ( $loop->have_posts() )
		{
			$args = array(
			'highlight_word' => $search_query
			);
			
			$results_heading = '<h2 class="easy_faqs_search_heading">Search Results</h2>';
			$results_heading = apply_filters( 'easy_faqs_search_results_heading', $results_heading, array('query' => $search_query) );			
			
			$results_html = $this->root->displayFAQsFromQuery($loop, $args);
			// inject the results into the template and return it
			$html = sprintf( 
			'<div class="easy_faqs_search_results_wrapper">
			%s
			<div class="easy_faqs_search_results">%s</div>
			</div>',
			$results_heading,
			$results_html
			);
			
			return $html;
		}
		else // no results
		{
			// return the 'No results found' message's HTML
			$no_results_msg = 'No FAQs were found which matched your query, "%s". Please search again.';
			$results_html = $this->format_no_results_message($no_results_msg, $search_query);
			return $results_html;
		}				
	}
	
	function get_search_results($search_query, $log_search = true)
	{
		$args = array(
			's' 		=> $search_query,
			'post_type' => 'faq',
		);
		$args = apply_filters( 'easy_faqs_search_params', $args );
		$results = new WP_Query( $args );
		$ip = $this->get_real_user_ip();
		$result_count = $results->post_count;
		if ($log_search) {
			$this->log_search($search_query, $result_count, $ip);
		}
		return $results;
	}
	
	function log_search($search_query, $result_count, $ip_address = '', $geolocate = true)
	{
		global $wpdb;		
		$table_name = $wpdb->prefix . 'easy_faqs_search_log';
		$friendly_location = '';
		
		if($geolocate) {
			$geo = $this->geolocate_current_visitor();
			$friendly_location = $geo['friendly_location'];
		}
		$wpdb->insert( 
		$table_name, 
		array( 
		'time' => current_time( 'mysql' ),
		'query' => $search_query,
		'ip_address' => $ip_address,
		'friendly_location' => $friendly_location,
		'result_count' => $result_count
		)
		);
	}
	
	function geolocate_current_visitor($ignore_cache = false)
	{
		$ip = $this->get_real_user_ip();
		$cache_key  = 'easy_faqs_geoloc_' . md5($ip);
		
		if ( !$ignore_cache && ($geo = get_transient($cache_key) !== FALSE) ) {
			return $geo;
		}
		else {		
			$geolocator_url = 'http://freegeoip.net/json/' . $ip;
			$url_contents = wp_remote_get( $geolocator_url );
			if (! is_wp_error( $url_contents ) && is_array( $url_contents ) && isset($url_contents['body']) && strlen($url_contents['body']) > 0)
			{
				$response_body = $url_contents['body'];
				$geo_json = json_decode($response_body);
				$geo = array(
				'ip' => $geo_json->ip,
				'country_code' => $geo_json->country_code,
				'country_name' => $geo_json->country_name,
				'region_name' => $geo_json->region_name,
				'state' => $geo_json->region_name,
				'city' => $geo_json->city,
				'zipcode' => isset($geo_json->zipcode) ? $geo_json->zipcode : '',
				'latitude' => $geo_json->latitude,
				'longitude' => $geo_json->longitude,
				'friendly_location' => $geo_json->country_name,
				);
				
				// if US, replace country name with city and state
				if ($geo['country_code'] == 'US') { 
					$geo['friendly_location'] = $geo_json->city . ', ' . $geo_json->region_name . ', USA';
				}
				// cache result indefinitely (1 year)
				set_transient( $cache_key, $geo, 31536000 );
				return $geo;
			}
			else {
				return false;
			}
		}
	}

	/* Source: http://stackoverflow.com/a/13646848 */
	function get_real_user_ip()
	{
		if( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
				$addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
				return trim($addr[0]);
			} else {
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}
		else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}
	
	// returns the "no results message" wrapped in the proper HTML tags 
	// will insert $query within $msg if $msg contains "%s"
	function format_no_results_message($msg, $query) {
		
		// allow the message to be overriden by a filter
		$msg = apply_filters( 'easy_faqs_search_no_results_message', $msg, array('query' => $query) );
		
		// inject search term if needed
		if (strpos($msg, '%s') !== FALSE) {
			$msg = sprintf($msg, htmlentities($query));
		}
		
		// wrap it in a <p> and return it
		$template = '<p class="%s">%s</p>';
		$css_class = 'easy_faqs_no_results';
		return sprintf($template, $css_class, $msg);		
	}
	
	/**
	 * Add a widget to the dashboard.
		*
	 * This function is hooked into the 'wp_dashboard_setup' action below.
	 */
	function add_dashboard_widget() {
		
		wp_add_dashboard_widget(
			'easy_faqs_searches_dashboard_widget',         // Widget slug.
			'Easy FAQs Pro - Recent Searches',         // Title.
			array($this, 'output_dashboard_widget') // Display function.
        );	
	}
	
	/**
	 * Create the function to output the contents of our Dashboard Widget.
	 */
	function output_dashboard_widget()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'easy_faqs_search_log';
		$limit = 10;
		$sql_template = 'SELECT * from %s ORDER BY time DESC LIMIT %d';
		$sql = sprintf($sql_template, $table_name, $limit);
		$recent_searches = $wpdb->get_results($sql);
		if (is_array($recent_searches)) {
			echo '<table id="easy_faqs_recent_searches" class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th>Time</th>';
			echo '<th>Query</th>';
			echo '<th>Results</th>';
			echo '<th>Visitor IP</th>';
			//echo '<th>Location</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			foreach($recent_searches as $i => $search)
			{
				$row_class = ($i % 2 == 0) ? 'alternate' : '';
				echo '<tr class="'.$row_class.'">';
				$friendly_time = date('Y-m-d H:i:s', strtotime($search->time));
				$friendly_time = $this->root->time_elapsed_string($friendly_time);
				printf ('<td>%s</td>', htmlentities($friendly_time));
				printf ('<td>%s</td>', htmlentities($search->query));
				printf ('<td>%s</td>', htmlentities($search->result_count));
				printf ('<td>%s</td>', htmlentities($search->ip_address));
				//printf ('<td>%s</td>', htmlentities($search->friendly_location));
				echo '</tr>';				
			}
			echo '</tbody>';
			echo '</table>';
			$view_all_searches_url= '/wp-admin/admin.php?page=easy-faqs-recent-searches';
			$link_text = 'View All Searches';
			printf ('<p class="view_all_searches"><a href="%s">%s &raquo;</a></p>', $view_all_searches_url, $link_text);
		}
	}	
}