<?php
	require_once('wpframe.php');
	
	if(!isset($GLOBALS['mtq_number_displayed'])) {
		$GLOBALS['mtq_number_displayed'] = 1;
	} else {
		$GLOBALS['mtq_number_displayed']++;
	} 
	if (true) {
		
		global $wpdb;
		$GLOBALS['wpframe_plugin_name'] = basename(dirname(__FILE__));
		$GLOBALS['wpframe_plugin_folder'] = $GLOBALS['wpframe_wordpress'] . '/wp-content/plugins/' . $GLOBALS['wpframe_plugin_name'];

		$quiz_options = $wpdb->get_row($wpdb->prepare("SELECT name,description,answer_mode,single_page,show_hints,show_start,show_final,multiple_chances,final_screen,random_questions,random_answers FROM {$wpdb->prefix}mtouchquiz_quiz WHERE ID=%d", $quiz_id));			 		
		$final_screen = stripslashes($quiz_options->final_screen);
		$answer_display = stripslashes($quiz_options->answer_mode);
		$single_page = stripslashes($quiz_options->single_page);
		$show_hints = stripslashes($quiz_options->show_hints);
		$show_start = stripslashes($quiz_options->show_start);
		$show_final = stripslashes($quiz_options->show_final);
		$multiple_chances = stripslashes($quiz_options->multiple_chances);
		$random_questions = stripslashes($quiz_options->random_questions);
		$random_answers = stripslashes($quiz_options->random_answers);
		
		$mtq_show_alerts = get_option('mtouchquiz_showalerts');
		
		$dquizfm = $wpdb->get_row($wpdb->prepare("SELECT form_code FROM {$wpdb->prefix}mtouchquiz_quiz WHERE ID=%d", $quiz_id));
		$form_code = stripslashes($dquizfm->form_code);
		$form_id= $form_code;
		$tquizfm = $wpdb->get_row($wpdb->prepare("SELECT time_limit FROM {$wpdb->prefix}mtouchquiz_quiz WHERE ID=%d", $quiz_id));
		$db_time = stripslashes($tquizfm->time_limit);
		
		$mtq_cf7_addon_active = mtq_check_addon_cf7_active();
		$mtq_cf7_active = mtq_check_cf7_active();
		$mtq_cf7_addon_exists =  mtq_check_addon_cf7_exists();
		$mtq_cf7_exists = mtq_check_cf7_exists();
		$mtq_cf7_allgood = mtq_check_all_cf7();
	  
		$mtq_gf_addon_active = mtq_check_addon_gf_active();
		$mtq_gf_active = mtq_check_gf_active();
		$mtq_gf_addon_exists =  mtq_check_addon_gf_exists();
		$mtq_gf_exists = mtq_check_gf_exists();
		$mtq_gf_allgood = mtq_check_all_gf();
		
		$mtq_form_present = 0;
		
		$mtq_use_gf = 0;
		$mtq_use_cf = 0;
		
		if ( $mtq_cf7_allgood || $mtq_gf_allgood ) {
			if ( strlen($form_code) ) {
				if ( $forcegf ) {
					$mtq_use_gf = 1;	
				} elseif ($forcecf) {
					$mtq_use_cf = 1;
					
				} elseif ($mtq_gf_allgood ) {
					$mtq_use_gf = 1;
					
				} elseif ($mtq_cf7_allgood ) {
					$mtq_use_cf = 1;
				}
			}	
		}
		
		if ( $input_formid != -1 ) {
			$form_code = $input_formid;
		}
		
		$gf_formid_number = $form_code;
		
		if ( $mtq_use_gf && $mtq_gf_allgood ) {
			$mtq_form_present = 1;
			if ( substr($form_code,0,1) != "[" ) {
				$form_code = "[gravityform id=" .$form_code;
			}
			
			if ( substr($form_code,-1) == "]" ) {
				$form_code=str_replace("]","",$form_code) ;
			}
			
			if ( !strpos($form_code,"ajax") ) {
				$form_code.=" ajax=true ";
			}
			
			if ( substr($form_code,-1) != "]" ) {
				$form_code .="]" ;
			}
		}
		
		
		
		if ( $mtq_use_cf && $mtq_cf7_allgood ) {
			$mtq_form_present = 1;
			if ( substr($form_code,0,1) != "[" ) {
				$form_code = '[contact-form-7 id="' .$form_code.'"';
			}
						
			if ( substr($form_code,-1) != "]" ) {
				$form_code .="]" ;
			}
		}
		
		if ( $forcecf ) {
			$mtq_use_cf = 1;
		}
		
		if ( $forcegf || $inform ) {
			$mtq_use_gf = 1;
		}
		
		
		if ( $input_randomq != -1 ) {
			if ( $input_randomq == 'on' ){
				$random_questions = 1;
			} elseif  ( $input_randomq == 'off' ){
				$random_questions = 0;
			}
		}
		
		if ( $input_alerts != -1 ) {
			if ( $input_alerts == 1 ){
				$mtq_show_alerts = 1;
			} elseif  ( $input_alerts == 0 ){
				$mtq_show_alerts = 0;
			}
		}
		
		if ( $input_randoma != -1 ) {
			if ( $input_randoma == 'on' ){
				$random_answers = 1;
			} elseif  ( $input_randoma == 'off' ){
				$random_answers = 0;
			}
		}
		
		if ( $input_singlepage != -1 ) {
			if ( $input_singlepage == 'on' ){
				$single_page = 1;
			} elseif  ( $input_singlepage == 'off' ){
				$single_page = 0;
			}
		}
		
		if ( $inform ) {
			$single_page = 1;
		}
		
		if ( $input_hints!= -1 ) {
			if ( $input_hints == 'on' ){
				$show_hints = 1;
			} elseif  ( $input_hints == 'off' ){
				$show_hints = 0;
			}
		}
		
		
		
		if ( $input_startscreen!= -1 ) {
			if ( $input_startscreen == 'on' ){
				$show_start = 1;
			} elseif  ( $input_startscreen == 'off' ){
				$show_start = 0;
			}
		}
		

		if ( $input_showanswers != -1 ) {
			if ( $input_showanswers == 'never' ){
				$answer_display = 0;
			} elseif  ( $input_showanswers == 'end' ){
				$answer_display = 1;
			} elseif  ( $input_showanswers == 'now' ){
				$answer_display = 2;
			}
		}
		
		
		
		if ( $input_finalscreen != -1 ) {
			if ( $input_finalscreen == 'on' ){
				$show_final = 1;
			} elseif  ( $input_finalscreen == 'off' ){
				$show_final = 0;
			}
		}
		
		
		
		if ( $input_multiplechances!= -1 ) {
			if ( $input_multiplechances == 'on' ){
				$multiple_chances = 1;
			} elseif  ( $input_multiplechances == 'off' ){
				$multiple_chances = 0;
			}
		}
		
		
		if( $multiple_chances == 1  ) {
			$answer_display = 2; // You cannot allow multiple chances and not show the answers immediately.
		}
		
		if ( $single_page ) {
			$show_list = 0;	
		}
		
		if ( $proofread ) {
			$random_answers = 0;
			$random_questions = 0;
			$input_number_questions = 0;
			$single_page = 1;
			$offset_start = 1;
		}


		
		$mtq_all_vars = "";
		$number_questions_available = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id=%d",$quiz_id)); 
		if ($input_number_questions <= 0){ // If the user didn't specify the number of questions, then get them all
			$input_number_questions = $number_questions_available;
		}
		
	
		
		$foff = $offset_start - 1;
		if ( $offset_stop ) {
			$loff = $offset_stop - 1;
		}		

		//$first_id_value = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id=$quiz_id ORDER BY ID LIMIT 0, 1"));//,0,$offset_start-1);
		$first_id_value = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id=$quiz_id ORDER BY ID",0,$foff);  
		if ( $offset_stop ) {
			$last_id_value = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id=$quiz_id ORDER BY ID",0,$loff);
		} else {
			$last_id_value = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id=$quiz_id ORDER BY ID",0,$number_questions_available-1);
		}
		
		if ( ! $mtq_use_timer  && $db_time > 0) {
			$mtq_max_time = $db_time;
			$mtq_use_timer = 1;
		}
		
		
		$theexecutedcode = '';
		$theexecutedcode.= "mtouchquiz id=".$quiz_id;
		$theexecutedcode.=" alerts=".$mtq_show_alerts;
		$theexecutedcode.=" singlepage=".$single_page;
		$theexecutedcode.=" hints=".$show_hints;
		$theexecutedcode.=" startscreen=".$show_start;
		$theexecutedcode.=" finalscreen=".$show_final;
		$theexecutedcode.=" multiplechances=".$multiple_chances;
		$theexecutedcode.=" showanswers=".$answer_display;
		$theexecutedcode.=" show_stamps=".$show_stamps;
		$theexecutedcode.=" randomq=".$random_questions;
		$theexecutedcode.=" randoma=".$random_answers;
		$theexecutedcode.= " status=".$show_status;
		$theexecutedcode.= " labels=".$show_labels;
		$theexecutedcode.= " title=".$show_title;
		$theexecutedcode.= " proofread=".$proofread;
		$theexecutedcode.= " list=".$show_list;
		$theexecutedcode.=" time=".$mtq_max_time;
		$theexecutedcode.=" scoring=".$scoring;
		$theexecutedcode.=" formid=".$form_id;
		$theexecutedcode.=" vform=".$vform;
		$theexecutedcode.=" autoadvance=".$autoadvance;
		$theexecutedcode.=" autosubmit=".$auto_submit;
		$theexecutedcode.=" inform=".$inform;
		$theexecutedcode.=" forcecf=".$forcecf;
		$theexecutedcode.=" forcegf=".$forcegf;
		$replace_these	= array('1','0');
		$with_these = array ('on','off');	
		$theexecutedcode = str_replace($replace_these, $with_these,$theexecutedcode);
		$replace_these	= array('id=on','showanswers=off','showanswers=on','showanswers=2');
		$with_these = array ('id=1','showanswers=never','showanswers=end','showanswers=now');
		$theexecutedcode = str_replace($replace_these, $with_these,$theexecutedcode);
		
		$theexecutedcode.= " offset=".$offset_start;
		$theexecutedcode.= " questions=".$input_number_questions;
		$theexecutedcode.= " firstid=".$first_id_value;	
		$theexecutedcode.= " lastid=".$last_id_value;	
		$theexecutedcode.=	"";
		
		
		if ( $input_color == 'random') {
			
			$mtq_possible_colors=mtq_color_options();
			$mtq_color=$mtq_possible_colors[array_rand($mtq_possible_colors,1)];
			$theexecutedcode.= " color=random(".$mtq_color.")";
		} else {
			$mtq_color=$input_color;
			$theexecutedcode.= " color=".$mtq_color;
		}
		
		// Thanks http://ranawd.wordpress.com/2009/03/25/select-random-value-from-mysql-database-table/
		
		if( $random_questions == 1 && $input_number_questions > 0 ) { // Select questions randomly
			$all_question = $wpdb->get_results($wpdb->prepare("SELECT ID,question,explanation, point_value FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id=%d AND ID>=$first_id_value AND ID<=$last_id_value ORDER BY RAND() LIMIT 0, $input_number_questions", $quiz_id));
		} elseif( $random_questions != 1 && $input_number_questions > 0 ) { // Select questions in order
			$all_question = $wpdb->get_results($wpdb->prepare("SELECT ID,question,explanation, point_value FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id=%d AND ID>=$first_id_value  AND ID<=$last_id_value ORDER BY ID LIMIT 0, $input_number_questions", $quiz_id)); // Not random
		} else { // select all questions in order
			$all_question = $wpdb->get_results($wpdb->prepare("SELECT ID,question,explanation, point_value FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id=%d ORDER BY ID", $quiz_id)); // Not random, get everything, fallback else
		}


		if($all_question) 
		{
			
			$mtqid = $GLOBALS['mtq_number_displayed']; ?>
<?php if ( $proofread == 1 ) { ?>
<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['wpframe_plugin_folder']?>/proofread.min.css" />
<?php
			} 
			?>
<?php 
				//$GLOBALS['mtq_client_includes_loaded'] = true; // Make sure that this code is not loaded more than once.
			//}
				if(isset($_REQUEST['action']) and $_REQUEST['action']) 
				{ 
					// Quiz Results.
					// Replaced with client side javascript
				} else  // Show The Quiz.
				{ 
				?>
<div id="mtq_quiz_area-<?php echo $mtqid ?>" class="mtq_quiz_area mtq_color_<?php echo $mtq_color?>"> 
  <!--Quiz generated using <?php echo mtq_DISPLAY_NAME ?> Version <?php echo mtq_VERSION ?> by G. Michael Guy (<?php echo mtq_URL ?>)-->
  <?php if  ( $mtq_gf_addon_active ) {
	echo "<!--Enhanced with ".mtq_gf_DISPLAY_NAME." Version ".mtq_gf_VERSION ." (". mtq_gf_URL.")-->" ; 
	if ( ! $mtq_gf_addon_exists ) {
		echo "<!--(Gravity Forms Plugin is NOT installed, however.)-->" ; 
	}
	elseif ( ! $mtq_gf_active ) {
		echo "<!--(Gravity Forms Plugin is installed but NOT active.)-->" ; 
	}
  }?>
  <?php if  ( $mtq_cf7_addon_active ) {
	echo "<!--Enhanced with ".mtq_cf7_DISPLAY_NAME." Version ".mtq_cf7_VERSION ." (". mtq_cf7_URL.")-->" ; 
	if ( ! $mtq_cf7_addon_exists ) {
		echo "<!--(Contact Form 7 Plugin is NOT installed, however.)-->" ; 
	}
	elseif ( ! $mtq_cf7_active ) {
		echo "<!--(Contact Form 7 Plugin is installed but NOT active.)-->" ; 
	}
  }?>
  <?php if  ( mtq_check_all_timer() ) {
	echo "<!--Enhanced with ".mtq_timer_DISPLAY_NAME." Version ".mtq_timer_VERSION ." (". mtq_timer_URL.")-->" ; 
  }?>
  
    <?php if  ( mtq_check_theme_addon_exists() ) {
	echo "<!--Enhanced with ".mtq_theme_DISPLAY_NAME." Version ".mtq_theme_VERSION ." (". mtq_theme_URL.")-->" ; 
  }?>
  
  <!-- Shortcode entered <?php echo $thetypedcode; ?> --> 
  <!-- Shortcode interpreted <?php echo $theexecutedcode;?> --> 
  <!--form action="" method="post" class="quiz-form" id="quiz-<?php echo $quiz_id?>"-->
  <?php $mtq_all_vars.="<input type='hidden' id='mtq_id-{$mtqid}' name='mtq_id_value' value='{$mtqid}' />"; ?>
  <div id="mtq_quiztitle-<?php echo $mtqid ?>" class="mtq_quiztitle" <?php if ( ! $show_title ) { echo "style='display:none'"; } ?>>
  <h2><?php echo stripslashes($quiz_options->name)?></h2>
  </div>
    <?php if ($mtq_use_timer) {?>
  <div id="mtq_timer_row-<?php echo $mtqid ?>"><div id="mtq_timer_box-<?php echo $mtqid ?>" class="mtq_timer"></div> </div>
  <?php $mtq_all_vars.=   "<input type='hidden' id='mtq_timer_val-$mtqid' value='".$mtq_max_time."'/>";?>
  <?php }?>
  <noscript>
  <div id="mtq_javawarning-<?php echo $mtqid ?>" class="mtq_javawarning">
  <?php _e('Please wait while the activity loads.</br> If this activity does not load, try refreshing your browser. Also, this page requires javascript. Please visit using a browser with javascript enabled.', 'mtouchquiz'); ?>
  <div class="mtq_failed_button" onclick="mtq_start_one(<?php echo $mtqid ?>)">
  <?php _e('If loading fails, click here to try again','mtouchquiz') ?>
  </div></div>
  </noscript>
  <?php if ($show_start) {?>
  <div id="mtq_instructions-<?php echo $mtqid ?>" class="mtq_instructions"><?php echo stripslashes($quiz_options->description)?></div> <div id="mtq_start_button-<?php echo $mtqid ?>" class='mtq_action_button mtq_css_button mtq_start_button' onclick='mtq_start_quiz(<?php echo $mtqid ?>)'> <div class="mtq_start_text">
  <?php _e("Start", 'mtouchquiz'); ?>
  </div> </div>
  <?php } 
if ($show_final ) {?>
<div id="mtq_quiz_results_bubble-<?php echo $mtqid ?>" class="mtq_quiz_results_bubble"> <div id="mtq_quiz_results-<?php echo $mtqid ?>" class="mtq_quiz_results"><?php echo str_replace('%%QUIZ_NAME%%','<em>'.stripslashes($quiz_options->name).'</em>',$final_screen);?> <br>    
  </div><?php if  ( $mtq_form_present && ! ( $inform ) ) { ?>
    <div id="mtq_contact_form-<?php echo $mtqid ?>"> <?php echo ($form_code); ?> </div>
    <?php } ?> <div id="mtq_quiz_results_highlight-<?php echo $mtqid ?>" class="mtq_quiz_results_highlight">
  <?php _e('Your answers are highlighted below.', 'mtouchquiz'); ?>
  </div> </div>
  <?php } 
			if ($mtq_mobile_device ){
				?>
  <div id='mtq_view_anchor-<?php echo $mtqid ?>'></div>
  <?php
			}
		//}
		?>
  
  <!-- root element for mtqscrollable -->

  <div id="mtq_question_container-<?php echo $mtqid ?>" <?php if ( $show_start ) { echo "style='display:none'"; } ?>>
  <div <?php if (!$single_page) { echo "class='mtqscrollable' id='mtq_scroll_container-{$mtqid}'";}?>>
    <?php if (!$single_page) {?>
    <!-- root element for the items -->
    
    <div id="mtq_scroll_items_container-<?php echo $mtqid ?>" class="items">
      <?php }?>
      <?php
								
							$question_count = 1;
								foreach ($all_question as $ques) {
									echo   "<div class='mtq_question mtq_scroll_item-$mtqid' id='mtq_question-$question_count-$mtqid'>"; 
											echo   "<table class='mtq_question_heading_table'><tr><td>";
												if ( $show_labels ) { 
												echo   "<div class='mtq_question_label '>";
													ob_start();
															printf(__('Question %d', 'mtouchquiz'), $question_count);
													$q_label = ob_get_contents();
													ob_end_clean();
													echo $q_label;
												echo   "</div>";
												}
												echo   "<div id='mtq_stamp-$question_count-$mtqid' class='mtq_stamp'></div>";
												echo   "</td></tr></table>";
												echo "<div id='mtq_question_text-$question_count-$mtqid' class='mtq_question_text'>";
											echo   stripslashes($ques->question) ;
											echo "</div>";
											$mtq_all_vars.=   "<input type='hidden' name='question_id[]' value='{$ques->ID}'/>";
											$mtq_all_vars.=   "<input type='hidden' id='mtq_is_answered-{$question_count}-$mtqid' value='0'/>";
											$mtq_all_vars.=   "<input type='hidden' id='mtq_is_correct-{$question_count}-$mtqid' value='0'/>";
											$mtq_all_vars.=   "<input type='hidden' id='mtq_is_worth-{$question_count}-$mtqid' value='{$ques->point_value}'/>";
											$mtq_all_vars.=   "<input type='hidden' id='mtq_num_attempts-{$question_count}-$mtqid' value='0'/>";
											$mtq_all_vars.=   "<input type='hidden' id='mtq_points_awarded-{$question_count}-$mtqid' value='0'/>";
											 echo   "<table class='mtq_answer_table'>";
												 echo   "<colgroup>";
													 echo   "<col class='mtq_oce_first'/>";
												 echo   "</colgroup>";
												if ( $random_answers == 1 ) {
													$dans = $wpdb->get_results("SELECT ID,answer,correct,hint FROM {$wpdb->prefix}mtouchquiz_answer WHERE question_id={$ques->ID} ORDER BY RAND()"); // This will randomize the question answer order
												} else {
													$dans = $wpdb->get_results("SELECT ID,answer,correct,hint FROM {$wpdb->prefix}mtouchquiz_answer WHERE question_id={$ques->ID} ORDER BY sort_order");
												}
												$answer_count = 1;
												$num_correct = 0;
												$mtq_the_alphabet="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
												foreach ($dans as $ans) {
													$image_number = ($answer_count-1) % 26;
													echo   "<tr id='mtq_row-{$question_count}-{$answer_count}-$mtqid' onclick='mtq_button_click({$question_count},{$answer_count},$mtqid)' class='mtq_clickable'>";
														echo   "<td class='mtq_letter_button_td'>";
															echo   "<div id='mtq_button-{$question_count}-{$answer_count}-$mtqid' class='mtq_css_letter_button mtq_letter_button_{$image_number}'  alt='".$q_label .", Choice ".$answer_count."'>";															
														echo   substr($mtq_the_alphabet,$answer_count-1,1)."</div>";
														if ($ans->correct) {
																echo   "<div id='mtq_marker-{$question_count}-{$answer_count}-$mtqid' class='mtq_marker mtq_correct_marker' alt='".__("Correct", 'mtouchquiz')."'></div>"; 
																$num_correct++;
														} else {
																echo   "<div id='mtq_marker-{$question_count}-{$answer_count}-$mtqid' class='mtq_marker mtq_wrong_marker' alt='".__("Wrong", 'mtouchquiz')."'></div>"; 
														}
														
														echo   "</td>";
														echo   "<td class='mtq_answer_td'>";
															echo   "<div id='mtq_answer_text-{$question_count}-{$answer_count}-$mtqid' class='mtq_answer_text'>".stripslashes($ans->answer)."</div>";
															$is_correct_value = '0';
															if($ans->correct) $is_correct_value = '1';
															$mtq_all_vars.=   "<input type='hidden' id='mtq_is_correct-{$question_count}-{$answer_count}-$mtqid' value='$is_correct_value'/>";
															$mtq_all_vars.=   "<input type='hidden' id='mtq_was_selected-{$question_count}-{$answer_count}-$mtqid' value='0'/>";
															$mtq_all_vars.=   "<input type='hidden' id='mtq_was_ever_selected-{$question_count}-{$answer_count}-$mtqid' value='0'/>";
															$has_hint_value = '0';
															if($ans->hint) $has_hint_value = '1';
															$mtq_all_vars.=   "<input type='hidden' id='mtq_has_hint-{$question_count}-{$answer_count}-$mtqid' value='$has_hint_value'/>";
															if ( $has_hint_value && $show_hints ) {
															echo   "<div id='mtq_hint-$question_count-$answer_count-$mtqid' class='mtq_hint'>";					
																echo   "<div class='mtq_hint_label'>".__('Hint', 'mtouchquiz').": </div>";
																echo   "<div class='mtq_hint_text'>".stripslashes($ans->hint)."</div>";
															echo   "</div>";
															}
														echo   "</td>";
													echo   "</tr>";
													$answer_count++;
												}
												$answer_count--;
											echo   "</table>";

											if ($ques->explanation) //Need to format this better 
											{
												echo   "<div id='mtq_question_explanation-{$question_count}-$mtqid' class='mtq_explanation'>";
														echo   "<div class='mtq_explanation-label'>";
															 printf(__('Question %d Explanation:', 'mtouchquiz'), $question_count);
														echo   "&nbsp;</div>";
														echo   "<div class='mtq_explanation-text'> ".stripslashes($ques->explanation."</div>");
												echo   "</div>";
												//echo   "<input type='hidden' id='mtq_has_explanation-$question_count-$mtqid' value='1' />";
											} else {
												//echo   "<input type='hidden' id='mtq_has_explanation-$question_count-$mtqid' value='0' />";
											}
										//echo   "</div>";
									$mtq_all_vars.=   "<input type='hidden' id='mtq_num_ans-$question_count-$mtqid' value='$answer_count' />";
									$mtq_all_vars.=   "<input type='hidden' id='mtq_num_correct-$question_count-$mtqid' value='$num_correct' />";
									echo   "</div>";

									$question_count++;
									//echo "</div>";
								}
								$question_count--;
							?>
      <?php if ($show_final) {?>
      <div id="mtq_results_request-<?php echo $mtqid ?>" class="mtq_results_request mtq_scroll_item-<?php echo $mtqid;?>">
        <?php _e('Once you are finished, click the button below. Any items you have not completed will be marked incorrect.', 'mtouchquiz'); ?>
        <span id="mtq_results_button-<?php echo $mtqid ?>" class='mtq_action_button mtq_css_button mtq_results_button'  onclick='mtq_get_results(<?php echo $mtqid ?>)'> <span class="mtq_results_text">
        <?php _e("Get Results", 'mtouchquiz'); ?>
        </span> </span> </div>
      <?php } ?>
      <?php if (!$single_page) {?>
    </div>
    <?php } ?>
    <!--End of mtqscrollable items--> 
    
  </div>
  <!--End of mtqscrollable--> 
  <!--mtq_status-->
  <?php if ($show_status) { ?>
  <div id="mtq_quiz_status-<?php echo $mtqid ?>" class="mtq_quiz_status">
  <?php if ($question_count == 1 ){ _e('There is 1 question to complete.', 'mtouchquiz');} else { printf(__("There are %d questions to complete.", 'mtouchquiz'), $question_count); } ?>
  </div>
  <?php } ?>
  <?php if (!$single_page && ($question_count > 1 || $show_final)) { ?>
  <table id="mtq_listrow-<?php echo $mtqid ?>" class="mtq_listrow">
    <tr>
      <td class="mtq_listrow_button-td"><div id="mtq_back_button-<?php echo $mtqid ?>" class='prev browse left mtq_css_back_button mtq_listrow_button' alt="<?php _e("Go to Previous Question",'mtouchquiz'); ?>" onclick="mtq_back_nav(<?php echo $mtqid ?>)">&#8592;</div></td>
      <td><?php if ($show_list) {?>
        <div id="mtq_show_list-<?php echo $mtqid ?>" class="mtq_show_list mtq_css_button mtq_list_button" onclick="mtq_show_nav(<?php echo $mtqid ?>)" rel="mtq_navigator-<?php echo $mtqid ?>"> <div class="mtq_list_text">
          <?php _e("List", 'mtouchquiz');?>
          </div> </div>
        <?php }?></td>
      <td class="mtq_listrow_button-td"><div id="mtq_next_button-<?php echo $mtqid ?>" class='next browse right mtq_css_next_button mtq_listrow_button' alt='<?php _e("Go to Next Question",'mtouchquiz');?>' onclick="mtq_next_nav(<?php echo $mtqid ?>)">&#8594;</div></td>
    </tr>
  </table>
  <?php } ?>
  </div> 
  <!--Holds all questions-->
  <?php if ( $show_list ) {?>
  <div id="mtq_navigator-<?php echo $mtqid ?>" class="mtq_navigator"> <div id='mtq_return_list_t-<?php echo $mtqid ?>' class="mtq_return_list mtq_css_button mtq_return_button" onclick='mtq_nav_click(0,<?php echo $mtqid ?>)'> <div class="mtq_return_text">
    <?php _e('Return', 'mtouchquiz');?>
    </div> </div> <div id="mtq_shaded_item_msg-<?php echo $mtqid ?>" class="mtq_shaded_item_msg">
    <?php _e('Shaded items are complete.','mtouchquiz');?>
    </div>
    <table id="mtq_question_list_container-<?php echo $mtqid ?>" class="mtq_question_list_container">
      <tr>
        <?php
								 
									for ($i=1; $i<=$question_count; $i++) {
										echo "<td id='mtq_list_item-$i-$mtqid' class='mtq_list_item' onclick='mtq_nav_click($i,$mtqid)'>$i</td>";
										if ( ($i % 5) == 0 && $i > 1) {
											echo "</tr><tr>";
										}
									}
									if ( $show_final ) {
										echo "<td id='mtq_list_item-end-$mtqid' class='mtq_list_item' onclick='mtq_nav_click($i,$mtqid)'>".__('End', 'mtouchquiz')."</td>";
									}
									
								
								?>
      </tr>
    </table>
    <div id='mtq_return_list_b-<?php echo $mtqid ?>' class="mtq_return_list mtq_css_button mtq_return_button" onclick='mtq_nav_click(0,<?php echo $mtqid ?>)'> <div class="mtq_return_text">
    <?php _e('Return', 'mtouchquiz');?>
    </div> </div></div>
  <?php } ?>
  <div id="mtq_variables" class="mtq_preload" style="display:none"> <?php echo $mtq_all_vars; ?> <div id="mtq_have_completed_string" class="mtq_preload">
    <?php _e('You have completed', 'mtouchquiz') ?>
    </div> <div id="mtq_questions_string" class="mtq_preload">
    <?php _e('questions', 'mtouchquiz') ?>
    </div> <div id="mtq_question_string" class="mtq_preload">
    <?php _e('question', 'mtouchquiz') ?>
    </div> <div id="mtq_your_score_is_string"  class="mtq_preload">
    <?php _e('Your score is', 'mtouchquiz')?>
    </div> <div id="mtq_correct_string"  class="mtq_preload">
    <?php _e('Correct', 'mtouchquiz')?>
    </div> <div id="mtq_wrong_string"  class="mtq_preload">
    <?php _e('Wrong', 'mtouchquiz')?>
    </div> <div id="mtq_partial_string"  class="mtq_preload">
    <?php _e('Partial-Credit', 'mtouchquiz')?>
    </div> <div id="mtq_exit_warning_string"  class="mtq_preload">
    <?php _e('You have not finished your quiz. If you leave this page, your progress will be lost.', 'mtouchquiz')?>
    </div> <div id='mtq_correct_answer_string' class='mtq_preload'>
    <?php _e('Correct Answer', 'mtouchquiz')?>
    </div> <div id='mtq_you_selected_string' class='mtq_preload'>
    <?php _e('You Selected', 'mtouchquiz')?>
    </div> <div id='mtq_not_attempted_string' class='mtq_preload'>
    <?php _e('Not Attempted', 'mtouchquiz')?>
    </div> <div id='mtq_final_score_on_quiz_string' class='mtq_preload'>
    <?php _e('Final Score on Quiz', 'mtouchquiz')?>
    </div> <div id='mtq_attempted_questions_correct_string' class='mtq_preload'>
    <?php _e('Attempted Questions Correct', 'mtouchquiz')?>
    </div> <div id='mtq_attempted_questions_wrong_string' class='mtq_preload'>
    <?php _e('Attempted Questions Wrong', 'mtouchquiz')?>
    </div> <div id='mtq_questions_not_attempted_string' class='mtq_preload'>
    <?php _e('Questions Not Attempted', 'mtouchquiz')?>
    </div> <div id='mtq_total_questions_on_quiz_string' class='mtq_preload'>
    <?php _e('Total Questions on Quiz', 'mtouchquiz')?>
    </div> <div id='mtq_question_details_string' class='mtq_preload'>
    <?php _e('Question Details', 'mtouchquiz')?>
    </div> <div id='mtq_quiz_results_string' class='mtq_preload'>
    <?php _e('Results', 'mtouchquiz')?>
    </div> <div id='mtq_date_string' class='mtq_preload'>
    <?php _e('Date', 'mtouchquiz')?>
    </div> <div id='mtq_score_string' class='mtq_preload'>
    <?php _e('Score', 'mtouchquiz')?>
    </div> <div id='mtq_hint_string' class='mtq_preload'>
    <?php _e('Hint', 'mtouchquiz')?>
    </div>
    <div id='mtq_time_allowed_string' class='mtq_preload'><?php _e('Time allowed', 'mtouchquiz')?></div>
<div id='mtq_minutes_string' class='mtq_preload'><?php _e('minutes', 'mtouchquiz')?></div>
<div id='mtq_seconds_string' class='mtq_preload'><?php _e('seconds', 'mtouchquiz')?></div>
<div id='mtq_time_used_string' class='mtq_preload'><?php _e('Time used', 'mtouchquiz')?></div>
<div id='mtq_answer_choices_selected_string' class='mtq_preload'><?php _e('Answer Choice(s) Selected', 'mtouchquiz')?></div>
<div id='mtq_question_text_string' class='mtq_preload'><?php _e('Question Text', 'mtouchquiz')?></div>


    <input type='hidden' id='mtq_answer_display-<?php echo $mtqid ?>' value='<?php echo $answer_display;?>'/>
    <input type='hidden' id='mtq_autoadvance-<?php echo $mtqid ?>' value='<?php echo $autoadvance;?>'/>
    <input type='hidden' id='mtq_autosubmit-<?php echo $mtqid ?>' value='<?php echo $auto_submit;?>'/>
    <input type='hidden' id='mtq_single_page-<?php echo $mtqid ?>' value='<?php echo $single_page;?>'/>
    <input type='hidden' id='mtq_show_hints-<?php echo $mtqid ?>' value='<?php echo $show_hints;?>'/>
    <input type='hidden' id='mtq_show_start-<?php echo $mtqid ?>' value='<?php echo $show_start;?>'/>
    <input type='hidden' id='mtq_show_final-<?php echo $mtqid ?>' value='<?php echo $show_final;?>'/>
    <input type='hidden' id='mtq_show_alerts-<?php echo $mtqid ?>' value='<?php echo $mtq_show_alerts;?>'/>
    <input type='hidden' id='mtq_multiple_chances-<?php echo $mtqid ?>' value='<?php echo $multiple_chances;?>'/>
    <input type='hidden' id='mtq_proofread-<?php echo $mtqid ?>' value='<?php echo $proofread;?>'/>
    <input type='hidden' id='mtq_scoring-<?php echo $mtqid ?>' value='<?php echo $scoring;?>'/>
    <input type='hidden' id='mtq_vform-<?php echo $mtqid ?>' value='<?php echo $vform;?>'/>
    <input type="hidden" name="quiz_id" id="quiz_id-<?php echo $mtqid ?>" value="<?php echo  $quiz_id ?>" />
    <input type="hidden" name="mtq_total_questions" id="mtq_total_questions-<?php echo $mtqid ?>" value="<?php echo  $question_count; ?>" />
    <input type="hidden" name="mtq_current_score" id="mtq_current_score-<?php echo $mtqid ?>" value="0" />
    <input type="hidden" name="mtq_max_score" id="mtq_max_score-<?php echo $mtqid ?>" value="0" />
    <input type="hidden" name="mtq_questions_attempted" id="mtq_questions_attempted-<?php echo $mtqid ?>" value="0" />
    <input type="hidden" name="mtq_questions_correct" id="mtq_questions_correct-<?php echo $mtqid ?>" value="0" />
    <input type="hidden" name="mtq_questions_wrong" id="mtq_questions_wrong-<?php echo $mtqid ?>" value="0" />
    <input type="hidden" name="mtq_questions_not_attempted" id="mtq_questions_not_attempted-<?php echo $mtqid ?>" value="0" />
    <input type="hidden" id="mtq_display_number-<?php echo $mtqid ?>" value="<?php echo  $display_number; ?>" />
    <input type="hidden" id="mtq_show_list_option-<?php echo $mtqid ?>" value="<?php echo  $show_list; ?>" />
    <input type="hidden" id="mtq_show_stamps-<?php echo $mtqid ?>" value="<?php echo  $show_stamps; ?>" />
    <?php 
								$all_ratings = $wpdb->get_results($wpdb->prepare("SELECT score_rating, min_points FROM {$wpdb->prefix}mtouchquiz_ratings WHERE quiz_id=%d ORDER BY min_points", $quiz_id));
								$mtq_num_ratings=count($all_ratings);
								if ($mtq_num_ratings == 0)
								{
							?>
    <input type="hidden" id="mtq_num_ratings-<?php echo $mtqid ?>" value="5"/>
    <input type="hidden" id="mtq_ratingval-1-<?php echo $mtqid ?>" value="0"/>
    <div id="mtq_rating-1-<?php echo $mtqid ?>" class="mtq_preload">
    <?php _e('Need more practice!', 'mtouchquiz'); ?>
    </div>
    <input type="hidden" id="mtq_ratingval-2-<?php echo $mtqid ?>" value="40"/>
    <div id="mtq_rating-2-<?php echo $mtqid ?>" class="mtq_preload">
    <?php _e('Keep trying!', 'mtouchquiz'); ?>
    </div>
    <input type="hidden" id="mtq_ratingval-3-<?php echo $mtqid ?>" value="60"/>
    <div id="mtq_rating-3-<?php echo $mtqid ?>" class="mtq_preload">
    <?php _e('Not bad!', 'mtouchquiz'); ?>
    </div>
    <input type="hidden" id="mtq_ratingval-4-<?php echo $mtqid ?>" value="80"/>
    <div id="mtq_rating-4-<?php echo $mtqid ?>" class="mtq_preload">
    <?php _e('Good work!', 'mtouchquiz'); ?>
    </div>
    <input type="hidden" id="mtq_ratingval-5-<?php echo $mtqid ?>" value="100"/>
    <div id="mtq_rating-5-<?php echo $mtqid ?>" class="mtq_preload">
    <?php _e('Perfect!', 'mtouchquiz'); ?>
    </div>
    <?php
								} else
								{
									$how_many = $mtq_num_ratings + 1;
									echo "<input type='hidden' id='mtq_num_ratings-$mtqid' value='". $how_many ."'/>";
									echo "<input type='hidden' id='mtq_ratingval-1-$mtqid' value='-1'/>";
                            		echo "<div id='mtq_rating-1-$mtqid' class='mtq_preload'>".__('All done', 'mtouchquiz')."</div>";
									$counter = 2;
									foreach ($all_ratings as $quiz_rating) 
									{
										echo "<input type='hidden' id='mtq_ratingval-$counter-$mtqid' value='$quiz_rating->min_points'/>";
                            			echo "<div id='mtq_rating-$counter-$mtqid' class='mtq_preload'>".trim(stripslashes($quiz_rating->score_rating))."</div>";
										$counter++;
									}
									
								}
							?>
    <input type="hidden" id="mtq_gf_present-<?php echo $mtqid ?>" value="<?php echo $mtq_use_gf ?>"/>
    <input type="hidden" id="mtq_cf7_present-<?php echo $mtqid ?>" value="<?php echo $mtq_use_cf  ?>"/>
    <input type="hidden" id="mtq_quiz_in_form-<?php echo $mtqid ?>" value="<?php echo $inform  ?>"/>
    <input type="hidden" id="mtq_gf_formid_number-<?php echo $mtqid ?>" value="<?php echo $gf_formid_number  ?>"/>

  </div>
  
  <!--Variables Div--> 
  <!--/form--> 
</div>
<!--Quiz area div-->

<?php 			} // closes show the quiz else statement
			} //closes the else that prevents more than one loading
	} // closes the else for #If this is in the listing page
?>
