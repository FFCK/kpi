<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

$question= $wpdb->get_row($wpdb->prepare("SELECT question, explanation, point_value FROM {$wpdb->prefix}mtouchquiz_question WHERE ID=%d", $_REQUEST['question']));
$all_answers = $wpdb->get_results($wpdb->prepare("SELECT answer, correct, hint FROM {$wpdb->prefix}mtouchquiz_answer WHERE question_id=%d ORDER BY sort_order", $_REQUEST['question']));

$answer_count = 5;
if($action == 'edit' and $answer_count < count($all_answers)) $answer_count = count($all_answers) ;

?>

<div class="wrap">
  <h2><?php echo "&nbsp;" .__(ucfirst($action))."&nbsp;" ._e("Question", 'mtouchquiz').'&nbsp;'; ?></h2>
  <div id="titlediv">
    <input type="hidden" id="title" name="ignore_me" value="This is here for a workaround for a editor bug" />
  </div>
  <?php
wpframe_add_editor_js();
?>
<style type="text/css">
.qtrans_title, .qtrans_title_wrap {display:none;}
</style>
<script type="text/javascript">
var answer_count = <?php echo $answer_count?>;

function newAnswer() {
	answer_count++;
	var para = document.createElement("p");
	var Clabel = document.createElement("label");
	Clabel.setAttribute("for", "correct_answer_" + answer_count);
	Clabel.appendChild(document.createTextNode("<?php _e("Correct", 'mtouchquiz'); ?>"));
	para.appendChild(Clabel);
	var Cinput = document.createElement("input");
	Cinput.setAttribute("type", "checkbox");
	Cinput.setAttribute("name", "correct_answer");
	Cinput.className = "correct_answer";
	Cinput.setAttribute("value", answer_count);
	Cinput.setAttribute("id", "correct_answer_" + answer_count);
	para.appendChild(Cinput);
	para.appendChild(document.createTextNode("<?php _e("Delimit", 'mtouchquiz'); ?>"));
	var Linput = document.createElement("input");
	Linput.setAttribute("type", "checkbox");
	Linput.setAttribute("name", "enclose_latex");
	Linput.className = "enclose_latex";
	Linput.setAttribute("value", answer_count);
	Linput.setAttribute("id", "enclose_latex_" + answer_count);
	para.appendChild(Linput);
	//para.appendChild(document.createTextNode("<?php _e("Answer:", 'mtouchquiz'); ?>"));
	var Atextarea = document.createElement("textarea");
	Atextarea.setAttribute("name", "answer[]");
	Atextarea.setAttribute("rows", "3");
	Atextarea.setAttribute("cols", "50");
	para.appendChild(Atextarea);
	para.appendChild(document.createTextNode("<?php _e("Hint:", 'mtouchquiz'); ?>"));
	var Htextarea = document.createElement("textarea");
	Htextarea.setAttribute("name", "hint[]");
	Htextarea.setAttribute("rows", "3");
	Htextarea.setAttribute("cols", "50");
	para.appendChild(Htextarea);
	//para.innerHTML("<table><tr><td>");
	//para.appendChild(Htextarea);
	//para.innerHTML("</td></tr></table>");
	//var BigTable = document.createElement("table");
	//BigTable.
	
	//$("extra-answers").innerHTML += code.replace(/%%NUMBER%%/g, answer_count);
	document.getElementById("extra-answers").appendChild(para);
}
function init() {
	jQuery("#post").submit(function(_e) {
		// Make sure question is suplied
		var contents;
		if(window.tinyMCE && document.getElementById("content").style.display=="none") { // If visual mode is activated.
			contents = tinyMCE.get("content").getContent();
		} else {
			contents = document.getElementById("content").value;
		}
		
		if(!contents) {
			alert("<?php _e("Please enter the question", 'mtouchquiz'); ?>");
			_e.preventDefault();
			_e.stopPropagation();
			return true;
		}
		
		// We must have atleast 2 answers.
		var answer_count = 0
		jQuery(".answer").each(function() {
			if(this.value) answer_count++;
		});
		//if(answer_count < 2) {
		//	alert("<?php //_e("Please enter atleast two answers"); ?>");
		//	_e.preventDefault();
		//	_e.stopPropagation();
		//	return true;
		//}
		
		//A correct answer must be selected.
		var correct_answer_selected = false;
		jQuery(".correct_answer").each(function() {
			if(this.checked) {
				correct_answer_selected = true;
				return true;
			}
		});
		if(!correct_answer_selected) {
			alert("<?php _e("Please select a correct answer", 'mtouchquiz'); ?>");
			_e.preventDefault();
			_e.stopPropagation();
		}
	});
}
jQuery(document).ready(init);
</script>
  <form name="post" action="edit.php?page=mtouch-quiz/question.php&amp;quiz=<?php echo $_REQUEST['quiz']; ?>" method="post" id="post">
    <div id="poststuff">
      <div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
        <div class="postbox">
          <h3 class="hndle">
            <?php _e('Question', 'mtouchquiz') ?>
            </span></h3>
          <div class="inside">
            <?php the_editor(stripslashes($question->question)); ?>
          </div>
        </div>
        <div class="postbox">
          <h3 class="hndle"><span>
            <?php _e('Answers and Hints', 'mtouchquiz') ?>
            </span></h3>
          <div class="inside">
            <?php
for($i=1; $i<=$answer_count; $i++) { ?>
            <p>
            <table>
              <tr>
                <td><table>
                    <tr>
                      <td><input type="checkbox" class="correct_answer" id="correct_answer_<?php echo $i?>" <?php if($all_answers[$i-1]->correct == 1 && $action != 'new') echo 'checked="checked"';?> name="correct_answer[]" value="<?php echo $i?>" />
                        <label for="correct_answer_<?php echo $i?>">
                          <?php _e("Correct", 'mtouchquiz'); ?>
                        </label></td>
                    </tr>
                    <tr>
                      <td><input type="checkbox" id="enclose_latex_<?php echo $i?>" name="enclose_latex[]" value="<?php echo $i?>" />
                        <label for="enclose_latex_<?php echo $i?>">
                          <?php _e("Delimit", 'mtouchquiz'); ?>
                        </label></td>
                    </tr>
                  </table></td>
                <td><textarea name="answer[]" class="answer" rows="3" cols="50"><?php if($action == 'edit') echo stripslashes($all_answers[$i-1]->answer); ?>
</textarea></td>
                <td> <?php _e("Hint"); ?>
                  <textarea name="hint[]" class="hint" rows="3" cols="50"><?php if($action == 'edit') echo stripslashes($all_answers[$i-1]->hint); ?>
</textarea>
                  </p></td>
              </tr>
            </table>
            <?php } ?>
            
            <div id="extra-answers"></div>
            <a href="javascript:newAnswer();">
            <?php _e("Add New Answer", 'mtouchquiz'); ?>
            </a> </div>
        </div>
        <div class="postbox">
          <h3 class="hndle"><span>
            <?php _e('Explanation', 'mtouchquiz') ?>
            </span></h3>
          <div class="inside">
            <textarea name="explanation" rows="5" cols="50"><?php echo stripslashes($question->explanation)?></textarea>
            <br />
            <p>
              <?php _e('You can use this field to explain the correct answer. This will be shown whenever correct answers are revealed.', 'mtouchquiz') ?>
            </p>
          </div>
        </div>
        <div class="postbox">
          <h3 class="hndle"><span>
            <?php _e('Point Value', 'mtouchquiz') ?>
            </span></h3>
          <div class="inside">
            <textarea name="point_value" rows="1" cols="3"><?php if ($action == 'new') { echo '100';} else { echo stripslashes($question->point_value);} ?>
</textarea>
          </div>
        </div>
      </div>
      <p class="submit">
        <input type="hidden" name="quiz" value="<?php echo $_REQUEST['quiz']?>" />
        <input type="hidden" name="question" value="<?php echo stripslashes($_REQUEST['question'])?>" />
        <input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
        <input type="hidden" name="action" value="<?php echo $action ?>" />
        <span id="autosave"></span>
        <input type="submit" name="submit" value="<?php _e('Save', 'mtouchquiz') ?>" style="font-weight: bold;" />
      </p>
      <a href="edit.php?page=mtouch-quiz/question.php&amp;quiz=<?php echo $_REQUEST['quiz']?>">
      <?php _e("Go to Questions Page", 'mtouchquiz') ?>
      </a> </div>
	    <?php wp_nonce_field('mtq_question_form'); ?>
  </form>
</div>
