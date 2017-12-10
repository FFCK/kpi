<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

if($_REQUEST['message'] == 'updated') wpframe_message(__('Quiz Updated', 'mtouchquiz'));

if($_REQUEST['action'] == 'delete') {
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}mtouchquiz_quiz WHERE ID='$_REQUEST[quiz]'");
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}mtouchquiz_answer WHERE question_id=(SELECT ID FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id='$_REQUEST[quiz]')");
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id='$_REQUEST[quiz]'");
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}mtouchquiz_ratings WHERE quiz_id='$_REQUEST[quiz]'");
	wpframe_message(__("Quiz Deleted", 'mtouchquiz'));
}
?>

<div class="wrap">
  <h2>
    <?php _e("Manage mTouch Quizzes", 'mtouchquiz'); ?>
  </h2>
  <?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>
  <table class="widefat">
    <thead>
      <tr>
        <th scope="col"><div style="text-align: center;">
            <?php _e('ID', 'mtouchquiz') ?>
          </div></th>
        <th scope="col"><?php _e('Title', 'mtouchquiz') ?></th>
        <th scope="col"><?php _e('Number Of Questions', 'mtouchquiz') ?></th>
        <th scope="col"><?php _e('Created on', 'mtouchquiz') ?></th>
        <th scope="col" colspan="3"><?php _e('Action', 'mtouchquiz') ?></th>
      </tr>
    </thead>
    <tbody id="the-list">
      <?php
// Retrieve the quizzes
$all_quiz = $wpdb->get_results("SELECT Q.ID,Q.name,Q.added_on,(SELECT COUNT(*) FROM {$wpdb->prefix}mtouchquiz_question WHERE quiz_id=Q.ID) AS question_count
									FROM `{$wpdb->prefix}mtouchquiz_quiz` AS Q ");

if (count($all_quiz)) {
	foreach($all_quiz as $quiz) {
		$class = ('alternate' == $class) ? '' : 'alternate';
		
		print "<tr id='quiz-{$quiz->ID}' class='$class'>\n";
		?>
    <th scope="row" style="text-align: center;"><?php echo $quiz->ID ?></th>
      <td><?php echo stripslashes(sanitize_text_field($quiz->name))?></td>
      <td><?php echo $quiz->question_count ?></td>
      <td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($quiz->added_on)) ?></td>
      <td><a href='edit.php?page=mtouch-quiz/question.php&amp;quiz=<?php echo $quiz->ID?>' class='edit'>
        <?php _e('Manage Questions', 'mtouchquiz')?>
        </a></td>
      <td><a href='edit.php?page=mtouch-quiz/quiz_form.php&amp;quiz=<?php echo $quiz->ID?>&amp;action=edit' class='edit'>
        <?php _e('Edit Quiz Options', 'mtouchquiz'); ?>
        </a></td>
      <td><a href='admin.php?page=mtouch-quiz/quiz.php&amp;action=delete&amp;quiz=<?php echo $quiz->ID?>' class='delete' onclick="return confirm('<?php echo  addslashes(__("You are about to delete this quiz? This will delete all the questions and answers within this quiz. Press 'OK' to delete and 'Cancel' to stop.", 'mtouchquiz'))?>');">
        <?php _e('Delete', 'mtouchquiz')?>
        </a></td>
    </tr>
    <?php
		}
	} else {
?>
    <tr>
      <td colspan="5"><?php _e('No Quizzes found.', 'mtouchquiz') ?></td>
    </tr>
    <?php
}
?>
      </tbody>
    
  </table>
  <a href="edit.php?page=mtouch-quiz/quiz_form.php&amp;action=new">
  <?php _e("Create New Quiz", 'mtouchquiz')?>
  </a> </div>
  <br />
  <br />
  <br />
   <br />
  <br />
  <br />
  <?php mtq_premium_list();
  echo mtq_donate_form(); ?>