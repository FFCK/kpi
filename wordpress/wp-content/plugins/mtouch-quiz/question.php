<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

if(isset($_REQUEST['submit']) && check_admin_referer('mtq_question_form') ) {
	$correct_answers = $_REQUEST['correct_answer'];
	$num_correct = count($correct_answers);
	//$question_id = $_REQUEST['question'];
	$point_value = intval($_REQUEST['point_value']);
	if($action == 'edit'){ //Update goes here //, $_REQUEST['question']
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}mtouchquiz_question SET question=%s, explanation=%s, number_correct=%d, point_value=%d WHERE ID=%d", $_REQUEST['content'], $_REQUEST['explanation'], $num_correct,$point_value,$_REQUEST['question'] ));
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mtouchquiz_answer WHERE question_id=%d", $_REQUEST['question']));
		
		wpframe_message(__('Question updated.', 'mtouchquiz'));
		
	} else {
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}mtouchquiz_question(quiz_id, question, explanation,number_correct, point_value) VALUES(%d, %s, %s, %d, %d)", $_REQUEST['quiz'], $_REQUEST['content'], $_REQUEST['explanation'],  $num_correct, $point_value));//Inserting the questions
		wpframe_message(__('Question added.', 'mtouchquiz'));
		$_REQUEST['question'] = $wpdb->insert_id;
		$action='edit';
	}
	$question_id = $_REQUEST['question'];
	$left_delimit = get_option('mtouchquiz_leftdelimit');
	$right_delimit = get_option('mtouchquiz_rightdelimit');
	//Yes, we need 2 different counters - the $counter will skip over empty answers - $sort_order_counter will not.
	$counter = 1;
	$sort_order_counter = 1;
	$correct_answers = $_REQUEST['correct_answer'];
	$num_correct = count($correct_answers);
	$latex_answers = $_REQUEST['enclose_latex'];
	$num_latex = count($latex_answers);
	foreach ($_REQUEST['answer'] as $answer_text) {
		$correct = 0;
		for ($i=0; $i< $num_correct; $i++)
		{
			if ($correct_answers[$i] == $counter) $correct = 1;	
		}
		
		if($answer_text || is_numeric($answer_text)) {
			$hint_text = $_REQUEST['hint'][$counter-1];
			$show_hint=0;
			for ($i=0; $i< $num_latex; $i++)
			{
				if ($latex_answers[$i] == $counter) $answer_text =  $left_delimit .$answer_text.$right_delimit ;//"\\\(\\\displaystyle{".$answer_text."}\\\)";	
			}
			if($hint_text) {
				$show_hint=1;
			}
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}mtouchquiz_answer(question_id,answer,hint,correct,sort_order) 
				VALUES(%d, %s, %s, %s, %d)", $question_id, $answer_text, $hint_text, $correct, $sort_order_counter)); 
			$sort_order_counter++;
		}
		$counter++;
	}
	
	//$counter = 1;
	//$sort_order_counter = 1;
	//foreach ($_REQUEST['hint'] as $hint_text) {
		
		//if($hint_text) {
		//	$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}mtouchquiz_answer(question_id,answer,correct, sort_order) 
		//		VALUES(%d, %s, %s, %d)", $question_id, $answer_text, $correct, $sort_order_counter)); 
		//	$sort_order_counter++;
		//}
		//$counter++;
	//}
}


if($_REQUEST['message'] == 'new_quiz') {
	wpframe_message(__('New quiz added', 'mtouchquiz'));
}

if($_REQUEST['action'] == 'delete') {
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mtouchquiz_answer WHERE question_id=%d", $_REQUEST['question']));
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mtouchquiz_question WHERE ID=%d", $_REQUEST['question']));
	wpframe_message(__('Question Deleted', 'mtouchquiz'));
}
$quiz_name = stripslashes($wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}mtouchquiz_quiz WHERE ID=%d", $_REQUEST['quiz'])));
?>

<div class="wrap">
<?php
$quiz_request=intval($_REQUEST['quiz']);
 if (! $quiz_name) { $quiz_name =__("Quiz ID Number",'mtouchquiz')." ".$quiz_request; } ?>
  <h2><?php echo _e("Manage Questions in ", 'mtouchquiz') . $quiz_name; ?></h2>
  <?php
wp_enqueue_script( 'listman' );
wp_print_scripts();

?>
  <p>
    <?php  _e('To add this quiz to your blog, insert the code ', 'mtouchquiz') ?>
    [mtouchquiz <?php echo $quiz_request ?>]
    <?php _e('into any post.', 'mtouchquiz') ?>
  </p>
    <a href="edit.php?page=mtouch-quiz/question_form.php&amp;action=new&amp;quiz=<?php echo $_REQUEST['quiz'] ?>">
  <?php _e('Create New Question', 'mtouchquiz')?>
  </a> 
  <table class="widefat">
    <thead>
      <tr>
        <th scope="col"><div style="text-align: center;">#</div></th>
        <th scope="col"><?php _e('Question', 'mtouchquiz') ?></th>
        <th scope="col"><div style="text-align: center;">
            <?php _e('Number Answers', 'mtouchquiz') ?>
          </div></th>
        <th scope="col"><?php _e('Number Correct Answers', 'mtouchquiz') ?></th>
        <th scope="col"><?php _e('Point Value', 'mtouchquiz') ?></th>
        <th scope="col" colspan="2"><div style="text-align: center;">
            <?php _e('Action', 'mtouchquiz') ?>
          </div></th>
      </tr>
    </thead>
    <tbody id="the-list">
      <?php
// Retrieve the questions
$all_question = $wpdb->get_results("SELECT Q.ID,Q.sort_order,Q.question, Q.number_correct, Q.point_value, (SELECT COUNT(*) FROM {$wpdb->prefix}mtouchquiz_answer WHERE question_id=Q.ID) AS answer_count
										FROM `{$wpdb->prefix}mtouchquiz_question` AS Q
										WHERE Q.quiz_id=$quiz_request ORDER BY Q.sort_order, Q.ID");
										

if (count($all_question)) {
	$bgcolor = '';
	$class = ('alternate' == $class) ? '' : 'alternate';
	$question_count = 0;
	foreach($all_question as $question) {
		$question_count++;
		print "<tr id='question-{$question->ID}' class='$class'>\n";
		?>
    <th scope="row" style="text-align: center;"><?php echo $question_count ?></th>
      <td><?php echo stripslashes($question->question) ?></td>
      <td><?php echo $question->answer_count ?></td>
      <td><?php echo $question->number_correct ?></td>
      <td><?php echo $question->point_value ?></td>
      <td><a href='edit.php?page=mtouch-quiz/question_form.php&amp;question=<?php echo $question->ID?>&amp;action=edit&amp;quiz=<?php echo $_REQUEST['quiz']?>' class='edit'>
        <?php _e('Edit', 'mtouchquiz'); ?>
        </a></td>
      <td><a href='edit.php?page=mtouch-quiz/question.php&amp;action=delete&amp;question=<?php echo $question->ID?>&amp;quiz=<?php echo $_REQUEST['quiz']?>' class='delete' onclick="return confirm('<?php echo addslashes(__("You are about to delete this question. This will delete the answers and hints to this question. Press 'OK' to delete and 'Cancel' to stop.", 'mtouchquiz'))?>');">
        <?php _e('Delete', 'mtouchquiz')?>
        </a></td>
    </tr>
    <?php
		}
	} else {
?>
    <tr style='background-color: <?php echo $bgcolor; ?>;'>
      <td colspan="4"><?php _e('No questions found.', 'mtouchquiz') ?></td>
    </tr>
    <?php
}
?>
      </tbody>
    
  </table>
  <a href="edit.php?page=mtouch-quiz/question_form.php&amp;action=new&amp;quiz=<?php echo $_REQUEST['quiz'] ?>">
  <?php _e('Create New Question', 'mtouchquiz')?>
  </a> </div>
