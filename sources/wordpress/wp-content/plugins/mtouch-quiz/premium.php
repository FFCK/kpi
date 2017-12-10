<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

?>

<div class="wrap">
  <h2>
    <?php _e("Premium Features", 'mtouchquiz'); ?>
  </h2>
  <?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>
  <?php
  	$mtq_cf7_addon_active = mtq_check_addon_cf7_active();
	$mtq_cf7_active = mtq_check_cf7_active();
	$mtq_cf7_addon_exists =  mtq_check_addon_cf7_exists();
	$mtq_cf7_exists = mtq_check_cf7_exists();
	$mtq_cf7_allgood = mtq_check_all_cf7();
	?>
  <?php  
  
	$mtq_gf_addon_active = mtq_check_addon_gf_active();
	$mtq_gf_active = mtq_check_gf_active();
	$mtq_gf_addon_exists =  mtq_check_addon_gf_exists();
	$mtq_gf_exists = mtq_check_gf_exists();
	$mtq_gf_allgood = mtq_check_all_gf();
	?>
  <?php  
	$mtq_timer_addon_active = mtq_check_addon_timer_active();
	$mtq_timer_addon_exists =  mtq_check_addon_timer_exists();
	$mtq_timer_allgood = mtq_check_all_timer();
	?>
    
      <?php  
	$mtq_theme_addon_allgood = mtq_check_theme_addon_exists();
	?>
  <?php mtq_premium_list() ?>
  <hr />
  <div style="width:600px">
  
    <div style="width:600px">
    <table class='mtq_question_heading_table'>
      <tr>
        <td><h2> <a href="http://gmichaelguy.com/quizplugin/go/theme/" title="Find out about mTouch Quiz Theme Addon">mTouch Quiz Theme Addon</a><div class="<?php if ($mtq_theme_addon_allgood){ echo "mtq_thanks"; }   else { echo "mtq_cone";}?>"></div></h2>
          Easily change the color of your quiz to match your site's theme.</td>
      </tr>
    </table>
    <table class='mtq_answer_table'>
      <colgroup>
      <col class='mtq_oce_first'/>
      </colgroup>
      <tr> </tr>
      <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_theme_addon_allgood){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://gmichaelguy.com/quizplugin/go/theme/" title="Find out about mTouch Quiz Theme Addon">mTouch Quiz Theme Addon</a> Installed and ready to go!</td>
      </tr>
    </table>
  </div>
    
    <table class='mtq_question_heading_table'>
      <tr>
        <td><h2> <a href="http://gmichaelguy.com/quizplugin/go/gf/" title="Find out about mTouch Quiz Gravity Forms Addon">Gravity Forms Addon</a><div class="<?php if ($mtq_gf_allgood){ echo "mtq_thanks"; } elseif ($mtq_gf_addon_exists ) { echo "mtq_setup";} else { echo "mtq_cone";}?>"></div></h2>
    Add the ability to email quiz results to you and/or the quiz taker! Record of the email is also stored in the dashboard.</td>
      </tr>
    </table>
    <table class='mtq_answer_table'>
      <colgroup>
      <col class='mtq_oce_first'/>
      </colgroup>
      <tr> </tr>
      <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_gf_exists){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://gmichaelguy.com/quizplugin/go/gravity/" title="Get Gravity Forms">Gravity Forms</a> Installed</td>
      </tr>
      <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_gf_active){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://gmichaelguy.com/quizplugin/go/gravity/" title="Get Gravity Forms">Gravity Forms</a> Activated </td>
      </tr>
            <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_gf_addon_exists){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://gmichaelguy.com/quizplugin/go/gf/" title="Find out about mTouch Quiz Gravity Forms Addon">mTouch Quiz Gravity Forms Addon</a> Installed</td>
      </tr>
      <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_gf_addon_active){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://gmichaelguy.com/quizplugin/go/gf/" title="Find out about mTouch Quiz Gravity Forms Addon">mTouch Quiz Gravity Forms Addon</a> Activated </td>
      </tr>
    </table>
  </div>

  <div style="width:600px">
    <table class='mtq_question_heading_table'>
      <tr>
        <td><h2> <a href="http://gmichaelguy.com/quizplugin/go/cf7/" title="Find out about mTouch Quiz Contact Form 7 Addon">Contact Form 7 Addon</a><div class="<?php if ($mtq_cf7_allgood){ echo "mtq_thanks"; } elseif ($mtq_cf7_addon_exists ) { echo "mtq_setup";}  else { echo "mtq_cone";}?>"></div></h2>
          Add the ability to email quiz results to you and/or the quiz taker!</td>
      </tr>
    </table>
    <table class='mtq_answer_table'>
      <colgroup>
      <col class='mtq_oce_first'/>
      </colgroup>
      <tr> </tr>
      <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_cf7_exists){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://contactform7.com/" title="Get Contact Form 7">Contact Form 7</a> Installed</td>
      </tr>
      <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_cf7_active){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://contactform7.com/" title="Get Contact Form 7">Contact Form 7</a> Activated </td>
      </tr>
      <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_cf7_addon_exists){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://gmichaelguy.com/quizplugin/go/cf7/" title="Find out about mTouch Quiz Contact Form 7 Addon">mTouch Quiz Contact Form 7 Addon</a> Installed</td>
      </tr>
      <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_cf7_addon_active){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://gmichaelguy.com/quizplugin/go/cf7/" title="Find out about mTouch Quiz Contact Form 7 Addon">mTouch Quiz Contact Form 7 Addon</a> Activated </td>
      </tr>
    </table>
  </div>

  <div style="width:600px">
    <table class='mtq_question_heading_table'>
      <tr>
        <td><h2> <a href="http://gmichaelguy.com/quizplugin/go/timer/" title="Find out about mTouch Quiz Timer Addon">mTouch Quiz Timer Addon</a><div class="<?php if ($mtq_timer_allgood){ echo "mtq_thanks"; } elseif ($mtq_timer_addon_exists ) { echo "mtq_setup";}  else { echo "mtq_cone";}?>"></div></h2>
          Add a time limit and countdown clock to your quiz.</td>
      </tr>
    </table>
    <table class='mtq_answer_table'>
      <colgroup>
      <col class='mtq_oce_first'/>
      </colgroup>
      <tr> </tr>
      <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_timer_addon_exists){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://gmichaelguy.com/quizplugin/go/timer/" title="Find out about mTouch Quiz Timer Addon">mTouch Quiz Timer Addon</a> Installed</td>
      </tr>
      <tr>
        <td class='mtq_letter_button_td'><span class="<?php if ($mtq_timer_addon_active){ echo "mtq_correct_marker"; } else { echo "mtq_wrong_marker";}?>"></span></td>
        <td class='mtq_answer_text'><a href="http://gmichaelguy.com/quizplugin/go/timer/" title="Find out about mTouch Quiz Timer Addon">mTouch Quiz Timer Addon</a> Activated </td>
      </tr>
    </table>
  </div>
</div>
