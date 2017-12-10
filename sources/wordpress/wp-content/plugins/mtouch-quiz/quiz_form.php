<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

$dquiz = array();
if($action == 'edit') {
	$clean_quiz = intval($_REQUEST['quiz']);
	$dquiz = $wpdb->get_row($wpdb->prepare("SELECT name,description,final_screen,answer_mode,single_page,show_hints,show_start, show_final,multiple_chances,random_questions,random_answers FROM {$wpdb->prefix}mtouchquiz_quiz WHERE ID=%d", $clean_quiz));
	$final_screen = stripslashes($dquiz->final_screen);
	$answer_display = stripslashes($dquiz->answer_mode);
	$single_page = stripslashes($dquiz->single_page);
	$show_hints = stripslashes($dquiz->show_hints);
	$show_start = stripslashes($dquiz->show_start);
	$show_final = stripslashes($dquiz->show_final);
	$multiple_chances = stripslashes($dquiz->multiple_chances);
	$random_questions = stripslashes($dquiz->random_questions);
	$random_answers = stripslashes($dquiz->random_answers);
	$dquizfm = $wpdb->get_row($wpdb->prepare("SELECT form_code FROM {$wpdb->prefix}mtouchquiz_quiz WHERE ID=%d", $clean_quiz));
	$form_code = stripslashes($dquizfm->form_code);
	$tquizfm = $wpdb->get_row($wpdb->prepare("SELECT time_limit FROM {$wpdb->prefix}mtouchquiz_quiz WHERE ID=%d", $clean_quiz));
	$mtq_time = stripslashes($tquizfm->time_limit);
} else {
	$final_screen = __("<p>Congratulations - you have completed %%QUIZ_NAME%%.</p><p>You scored %%SCORE%% out of %%TOTAL%%.</p><p>Your performance has been rated as %%RATING%%</p>", 'mtouchquiz');
}

?>

<div class="wrap">
  <h2>
    <?php __(ucfirst($action) . " Quiz", 'mtouchquiz'); ?>
  </h2>
  <?php
	wpframe_add_editor_js();
?>
  <form name="post" action="<?php echo $GLOBALS['wpframe_plugin_folder'] ?>/quiz_action.php" method="post" id="post">
    <div id="poststuff">
      <div class="postbox" id="titlediv">
        <h3 class="hndle"> <span>
          <?php _e('Quiz Name', 'mtouchquiz') ?>
          </span> </h3>
          <!--p align="right"> <a class="button toggleVisualtwo"><?php _e('Visual', 'mtouchquiz') ?></a> <a class="button toggleHTMLtwo"><?php _e('HTML', 'mtouchquiz') ?></a> </p-->
        <div class="inside">
        <textarea rows='1' cols='50' style='width:100%' name='name' id='name' class='name'><?php echo stripslashes($dquiz->name); ?></textarea>
          <!--input type='text' name='name' id="title" value='<?php //echo stripslashes($dquiz->name); ?>' /-->
        </div>
      </div>
      <div class="postbox">
        <h3 class="hndle"> <span>
          <?php _e('Quiz Start Screen', 'mtouchquiz') ?>
          </span> </h3>
        <!--p align="right"> <a class="button toggleVisual"><?php _e('Visual', 'mtouchquiz') ?></a> <a class="button toggleHTML"><?php _e('HTML', 'mtouchquiz') ?></a> </p-->
        <div class="inside">
          <textarea name='description' rows='5' cols='50' style='width:100%' id='description' class='description'><?php echo stripslashes($dquiz->description); ?></textarea>
        </div>
      </div>
            <div class="postbox mtq_premium_feature"><div class="mtq_email"></div>
        <h3 class="hndle"> 
           <a href="http://gmichaelguy.com/quizplugin/go/premium/" title="Results Form" target="_blank">Results Form ID</a> <?php echo "(".__('For Email Submission of Quiz Results','mtouchquiz').")"?> Works best with <a href="http://gmichaelguy.com/quizplugin/go/gravity/" title="Find out about Gravity Forms" target="_blank">Gravity Forms</a></h3>
        <div class="inside">
         <?php
		 
	   
		 // Makes sure the plugin is defined before trying to use it
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
		
		$mtq_theme_allgood=mtq_check_theme_addon_exists();
	

	
	if ( ! $mtq_gf_addon_active && ! $mtq_cf7_addon_active ) { ?>
          <h4> <?php _e('To allow users to submit their results to you via email, you need a ','mtouchquiz'); echo '<a href="http://gmichaelguy.com/quizplugin/go/premium/" title="mTouch Quiz Premium Feature Addon" target="_blank">mTouch Quiz Premium Feature</a> addon. '; ?></h4>
      <span style="display:none"><textarea name="gravity" rows="1" cols="100"><?php echo $form_code ?></textarea></span>
      <?php } else {
		   ?>
           <h4> <?php _e('<a href="http://gmichaelguy.com/quizplugin/go/premium/" title="Find out about mTouch Quiz Premium Feature Addons">For detailed instructions on how to configure this option visit the plugin homepage.</a>', 'mtouchquiz'); 
		   
		   ?></h4>
		  <textarea name="gravity" rows="1" cols="100"><?php echo $form_code ?></textarea>
	<?php  } ?>
      
      </div></div>
      <div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea postbox">
        <h3 class="hndle"> <span>
          <?php _e('Final Screen', 'mtouchquiz') ?>
          </span> </h3>
        <div class="inside">
          <?php the_editor($final_screen); ?>
          <p> <strong>
            <?php _e('Usable Variables...', 'mtouchquiz') ?>
            </strong> </p>
          <table>
            <tr>
              <th style="text-align:left;"><?php _e('Variable', 'mtouchquiz') ?></th>
              <th style="text-align:left;"><?php _e('Value', 'mtouchquiz') ?></th>
            </tr>
                        <tr>
            <td>%%FORM%%</td>
              <td>
              <?php if ( $mtq_gf_addon_active || $mtq_cf7_addon_active  ) { ?>
              Results Form<?php _e(' location for emailing results. (You may put this variable in the ratings below for conditional email option)', 'mtouchquiz') ?>
              <?php } else {
              _e('To allow users to submit their results to you via email, you need a ','mtouchquiz'); echo '<a href="http://gmichaelguy.com/quizplugin/go/premium/" title="mTouch Quiz Premium Feature" target="_blank">mTouch Quiz Premium Feature</a>.';
              
              } ?>
              
              </td>
            </tr>
            <tr>
              <td>%%SCORE%%</td>
              <td><?php _e('The number of correct answers', 'mtouchquiz') ?></td>
            </tr>
            <tr>
              <td>%%TOTAL%%</td>
              <td><?php _e('Total number of questions', 'mtouchquiz') ?></td>
            </tr>
            <tr>
              <td>%%PERCENTAGE%%</td>
              <td><?php _e('Correct answer percentage', 'mtouchquiz') ?></td>
            </tr>
            <tr>
              <td>%%WRONG_ANSWERS%%</td>
              <td><?php _e('Number of answers you got wrong', 'mtouchquiz') ?></td>
            </tr>
            <tr>
              <td>%%RATING%%</td>
              <td><?php _e("A rating of your performance. (Customize below.)", 'mtouchquiz') ?></td>
            </tr>
            <tr>
              <td>%%TIME_ALLOWED%%</td>
              <td><?php _e("Time Allowed in Seconds. (Requires Timer Add on)", 'mtouchquiz') ?></td>
            </tr>
            <tr>
              <td>%%TIME_USED%%</td>
              <td><?php _e("Time Used in Seconds. (Requires Timer Add on)", 'mtouchquiz') ?></td>
            </tr>
            <tr>
              <td>%%QUIZ_NAME%%</td>
              <td><?php _e('The name of the quiz', 'mtouchquiz') ?></td>
            </tr>
          </table>
        </div>
      </div>
      <?php
			// I'll put 2 editors here - as soon as 'http://wordpress.org/support/topic/179110?replies=2' bug is fixed.
	?>
      <?php
			// This is somewhat of a workaround to add some editing now.
	?>
    
       <script type="text/javascript">
			jQuery(document).ready(function($) {

			var idtwo = 'name';

			$('a.toggleVisualtwo').click(
				function() {
					tinyMCE.execCommand('mceAddControl', false, idtwo);
				}
			);

			$('a.toggleHTMLtwo').click(
				function() {
					tinyMCE.execCommand('mceRemoveControl', false, idtwo);
				}
			);

		});
		</script>
      <script type="text/javascript">
			jQuery(document).ready(function($) {

			var id = 'description';

			$('a.toggleVisual').click(
				function() {
					tinyMCE.execCommand('mceAddControl', false, id);
				}
			);

			$('a.toggleHTML').click(
				function() {
					tinyMCE.execCommand('mceRemoveControl', false, id);
				}
			);

		});
		</script>

      <div class="postbox">
        <h3 class="hndle"> <span>
          <?php _e('%%RATING%% Customization for Final Screen above', 'mtouchquiz') ?>
          </span> </h3>
        <div class="inside">
          <h4><?php _e('Enter the percent (whole numbers only) and the message you would like the user to receive in place of the %%RATING%% variable. One of these messages will be displayed if their score is greater than or equal to the listed score.', 'mtouchquiz'); ?></h4>
          <?php 
		
			if ($action == 'edit') {
				$all_ratings = $wpdb->get_results($wpdb->prepare("SELECT score_rating, min_points FROM {$wpdb->prefix}mtouchquiz_ratings WHERE quiz_id=%d ORDER BY min_points", intval($_REQUEST['quiz'])));
			}
			$default_ratings = array(0,40,60,80,100);
			$default_messages = array(__("Need more practice!", 'mtouchquiz'),__("Keep trying!", 'mtouchquiz'),__("Not bad!", 'mtouchquiz'),__("Good work!", 'mtouchquiz'),__("Perfect!", 'mtouchquiz'));
			$num_ratings = 5;
			if ($action == 'edit' and $num_ratings < count($all_ratings)) $num_ratings = count($all_ratings) ;
			for($i=1; $i<=$num_ratings; $i++) 
			{
	?>
          <p>
            <textarea name="min_points[]" rows="1" cols="3" id="min_points_<?php echo $i?>" value="<?php echo $i?>"><?php if($action == 'edit') {echo stripslashes($all_ratings[$i-1]->min_points );} else {echo $default_ratings[$i-1];}?>
</textarea>
            <textarea name="score_rating[]" rows="1" cols="100" id="score_rating_<?php echo $i?>" value="<?php echo $i?>"><?php if($action == 'edit') {echo stripslashes($all_ratings[$i-1]->score_rating); }else {echo $default_messages[$i-1];} ?> 
</textarea>
          </p>
          <?php 	} ?>
        </div>
        <script type="text/javascript">
			var num_ratings = <?php echo $num_ratings?>;

			function newRating() {
				num_ratings++;
				var para = document.createElement("p");
				var Linput = document.createElement("textarea");;
				Linput.setAttribute("name", "min_points[]");
				Linput.setAttribute("value", num_ratings);
				Linput.setAttribute("id", "min_points_" + num_ratings);
				Linput.setAttribute("rows", "1");
				Linput.setAttribute("cols", "3");
				para.appendChild(Linput);
				var Atextarea = document.createElement("textarea");
				Atextarea.setAttribute("name", "score_rating[]");
				Atextarea.setAttribute("rows", "1");
				Atextarea.setAttribute("cols", "100");
				Atextarea.setAttribute("value", num_ratings);
				Atextarea.setAttribute("id", "score_rating_" + num_ratings);
				para.appendChild(Atextarea);
				//para.innerHTML("<table><tr><td>");
				//para.appendChild(Htextarea);
				//para.innerHTML("</td></tr></table>");
				//var BigTable = document.createElement("table");
				//BigTable.
				
				//$("extra-answers").innerHTML += code.replace(/%%NUMBER%%/g, answer_count);
				document.getElementById("extra-ratings").appendChild(para);
			}
		</script>
        <div id="extra-ratings"></div>
        <a href="javascript:newRating();">
        <?php _e("Add New Rating", 'mtouchquiz'); ?>
        </a> </div>
      <div class="postbox">
        <h3 class="hndle"> <span>
          <?php _e('Quiz Options', 'mtouchquiz') ?>
          </span> </h3>
        <div class="inside">
          <table width="100%">
          <thead>
          	<th> <?php _e('Options', 'mtouchquiz') ?>
            </th>
   			<th> <?php _e('Shortcode arguments', 'mtouchquiz') ?>
          </th>
          </thead>
            <tr>
              <td><input type="checkbox" name="single_page" <?php if($single_page == '1') echo 'checked="checked"'; ?> value="2" id="single_page" />
                <label for="single_page"><?php _e('Show all questions on a single page.', 'mtouchquiz'); ?></label></td>
              <td> singlepage='on' or singlepage='off' </td>
            </tr>
            <tr>
              <td><input type="checkbox" name="multiple_chances" <?php if($multiple_chances == '1') echo 'checked="checked"'; else if ($action == 'new') { echo 'checked="checked"';} ?> value="2" id="multiple_chances" />
                <label for="multiple_chances"><?php _e('Allow multiple opportunites to answer questions.*', 'mtouchquiz'); ?></label></td>
              <td>multiplechances='on' or multiplechances='off' </td>
            </tr>
            <tr>
              <td><input type="checkbox" name="show_hints" <?php if($show_hints == '1') echo 'checked="checked"'; else if ($action == 'new') { echo 'checked="checked"';} ?> value="2" id="show_hints" />
                <label for="show_hints"><?php _e('Show the hints, when available.', 'mtouchquiz'); ?></label></td>
              <td> hints='on' or hints='off' </td>
            </tr>
            <tr>
              <td><input type="checkbox" name="show_start" <?php if($show_start == '1') echo 'checked="checked"'; else if ($action == 'new') { echo 'checked="checked"';} ?> value="2" id="show_start" />
                <label for="show_start"><?php _e('Display Quiz Start Screen before quiz.', 'mtouchquiz'); ?></label></td>
              <td> startscreen='on' or startscreen='off' </td>
            </tr>
            <tr>
              <td><input type="checkbox" name="show_final" <?php if($show_final == '1') echo 'checked="checked"'; else if ($action == 'new') { echo 'checked="checked"';} ?> value="2" id="show_final" />
                <label for="show_final"><?php _e('Display Quiz Final Screen after quiz.', 'mtouchquiz'); ?></label></td>
              <td> finalscreen='on' or finalscreen='off' </td>
            </tr>
            <tr>
              <td><input type="checkbox" name="random_questions" <?php if($random_questions == '1') echo 'checked="checked"'; ?> value="2" id="random_questions" />
                <label for="show_final"><?php _e('Randomly arrange questions.', 'mtouchquiz'); ?></label></td>
              <td> randomq='on' or randomq='off' </td>
            </tr>
            <tr>
              <td><input type="checkbox" name="random_answers" <?php if($random_answers == '1') echo 'checked="checked"'; ?> value="2" id="random_answers" />
                <label for="show_final"><?php _e('Randomly arrange answers.', 'mtouchquiz'); ?></label></td>
              <td> randoma='on' or randoma='off' </td>
            </tr>
            <tr>
              <td><input type="radio" name="answer_mode" <?php if($answer_display == '0') echo 'checked="checked"'; ?> value="0" id="no-show" />
                <label for="no-show"><?php _e('Never indicate the correct answers.', 'mtouchquiz'); ?></label></td>
              <td> showanswers='never' </td>
            </tr>
            <tr>
              <td><input type="radio" name="answer_mode" <?php if($answer_display == '1') echo 'checked="checked"'; ?> value="1" id="show-end" />
                <label for="show-end"><?php _e('Indicate the correct answers only at the end of the quiz.', 'mtouchquiz'); ?></label></td>
              <td> showanswers='end' </td>
            </tr>
            <tr>
              <td><input type="radio" name="answer_mode" <?php if($answer_display == '2') echo 'checked="checked"'; else if ($action == 'new') { echo 'checked="checked"';} ?> value="2" id="show-between" />
                <label for="show-between"><?php _e('Indicate the correct answers at the end of each question.', 'mtouchquiz'); ?></label></td>
              <td> showanswers='now' </td>
            </tr>
            <tfoot>
            	<td colspan="2">
                <?php _e('* Must also select Indicate the correct answers at the end of each question.', 'mtouchquiz'); ?>
                </td>
            </tfoot>
                        <tr>
            	<td colspan="2">
                <?php //_e('** It will still indicate which problems were marked correct/wrong at the end of the quiz.', 'mtouchquiz'); ?>
                </td>
            </tr>
          </table>
        </div>
      </div>
      
      <div class="postbox mtq_premium_feature"><div class="mtq_timer_icon"></div>
        <h3 class="hndle"> 
           <a href="http://gmichaelguy.com/quizplugin/go/timer/" title="Time Limit" target="_blank">Time Limit (in seconds)</a> <?php echo "(".__('Add a time limit and countdown clock.','mtouchquiz').")"?> </h3>
        <div class="inside">
         <?php
		 
	   
		 // Makes sure the plugin is defined before trying to use it
		$mtq_timer_addon_active = mtq_check_addon_timer_active();
		$mtq_timer_addon_exists =  mtq_check_addon_timer_exists();
		$mtq_timer_allgood = mtq_check_all_timer();
	  	
	if ( ! $mtq_timer_allgood ) { ?>
          <h4> <?php _e('To set a time limit and have a countdown clock, you need the ','mtouchquiz'); echo '<a href="http://gmichaelguy.com/quizplugin/go/timer/" title="mTouch Quiz Timer Addon" target="_blank">mTouch Quiz Timer</a> addon. '; ?></h4>
      <span style="display:none"><textarea name="mtq_timer" rows="1" cols="100"><?php echo $mtq_time ?></textarea></span>
      <?php } else {
		   ?>
           <h4 > <?php _e('<a href="http://gmichaelguy.com/quizplugin/go/timer/" title="mTouch Quiz Timer Addon" target="_blank">For detailed instructions on how to configure the timer option visit the plugin homepage.</a>', 'mtouchquiz'); 
		   
		   ?></h4>
		  <textarea name="mtq_timer" rows="1" cols="100"><?php echo $mtq_time ?></textarea>
	<?php  } ?>
      
      </div></div>
      <div class="inside">
      <?php
	  if ($mtq_theme_allgood) {
	  	$mtq_color_theme=get_option("mtouchquiz_color");
	  } else {
		 $mtq_color_theme="blue"; 
	  }
	  ?>
      Your <a href="admin.php?page=mtouch-quiz/theme.php">color theme</a> is <span class="mtq_color_<?php echo $mtq_color_theme; ?>"><span class="mtq_css_letter_button">A</span><?php echo $mtq_color_theme; ?></span>
      
      </div>
      <p class="submit">
        <?php wp_nonce_field('mtq_create_edit_quiz'); ?>
        <input type="hidden" name="action" value="<?php echo $action; ?>" />
        <input type="hidden" name="quiz" value="<?php echo intval($_REQUEST['quiz']); ?>" />
        <input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
        <span id="autosave"></span>
        <input type="submit" name="submit" value="<?php _e('Save', 'mtouchquiz') ?>" style="font-weight: bold;" tabindex="4" />
      </p>
    </div>
  </form>
</div>
