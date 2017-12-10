<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

?>

<div class="wrap">

  <?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

    
      <?php
	  $mtq_just_updated=false;
	  if ($_POST['mtq_theme_update'] == 'Y') {
		  update_option('mtouchquiz_color', $_POST['color']);
		  $mtq_just_updated=true;
	  }
	  
	  
	$mtq_theme_addon_allgood = mtq_check_theme_addon_exists();
	if ( !$mtq_theme_addon_allgood) {
		
		?> All the colors below (plus more to be added for free in the future), are avaiable with the purchase of the <a href="http://gmichaelguy.com/quizplugin/go/theme/" title="mTouch Quiz Theme Addon" target="_blank">mTouch Quiz Theme Addon</a>. Purchases of additional addons support the continued development of mTouch Quiz and help ensure free updates continue into the future.<?php
	
	?><h2>
    <?php _e("For a small price, you could choose a color to match your Site's Theme!", 'mtouchquiz'); ?>
  </h2>
  <div style="width:600px">
  
  <table>
<tbody>
<tr>
<td style="width: 85px;"><span class="mtq_color_blue"><span class="mtq_css_letter_button">A</span>blue</span></td>
<td style="width: 85px;"><span class="mtq_color_green"><span class="mtq_css_letter_button">A</span>green</span></td>
<td style="width: 85px;"><span class="mtq_color_red"><span class="mtq_css_letter_button">A</span>red</span></td>
<td style="width: 85px;"><span class="mtq_color_orange"><span class="mtq_css_letter_button">A</span>orange</span></td>
<td style="width: 85px;"><span class="mtq_color_yellow"><span class="mtq_css_letter_button">A</span>yellow</span></td>
</tr>
<tr>
<td style="width: 85px;"><span class="mtq_color_indigo"><span class="mtq_css_letter_button">A</span>indigo</span></td>
<td style="width: 85px;"><span class="mtq_color_violet"><span class="mtq_css_letter_button">A</span>violet</span></td>
<td style="width: 85px;"><span class="mtq_color_fuchsia"><span class="mtq_css_letter_button">A</span>fuchsia</span></td>
<td style="width: 85px;"><span class="mtq_color_khaki"><span class="mtq_css_letter_button">A</span>khaki</span></td>
<td style="width: 85px;"><span class="mtq_color_burgundy"><span class="mtq_css_letter_button">A</span>burgundy</span></td>
</tr>
<tr>
<td style="width: 85px;"><span class="mtq_color_black"><span class="mtq_css_letter_button">A</span>black</span></td>
<td style="width: 85px;"><span class="mtq_color_lightblue"><span class="mtq_css_letter_button">A</span>lightblue</span></td>
<td style="width: 85px;"><span class="mtq_color_teal"><span class="mtq_css_letter_button">A</span>teal</span></td>
<td style="width: 85px;"><span class="mtq_color_lightgreen"><span class="mtq_css_letter_button">A</span>lightgreen</span></td>
<td style="width: 85px;"><span class="mtq_color_lightpink"><span class="mtq_css_letter_button">A</span>lightpink</span></td>
</tr>
<tr>
<td style="width: 85px;"><span class="mtq_color_darkgreen"><span class="mtq_css_letter_button">A</span>darkgreen</span></td>
<td style="width: 85px;"><span class="mtq_color_brown"><span class="mtq_css_letter_button">A</span>brown</span></td>
<td style="width: 85px;"><span class="mtq_color_purple"><span class="mtq_css_letter_button">A</span>purple</span></td>
<td style="width: 85px;"><span class="mtq_color_navy"><span class="mtq_css_letter_button">A</span>navy</span></td>
<td style="width: 85px;"><span class="mtq_color_darkpink"><span class="mtq_css_letter_button">A</span>darkpink</span></td>
</tr>
<tr>
<td style="width: 85px;"><span class="mtq_color_lavender"><span class="mtq_css_letter_button">A</span>lavender</span></td>
<td><span><span class="mtq_css_letter_button" style="font-size: 14px; line-height: 16px;">Coming Soon!</span></span></td>
<td><span><span class="mtq_css_letter_button" style="font-size: 14px; line-height: 16px;">Coming Soon!</span></span></td>
<td><span><span class="mtq_css_letter_button" style="font-size: 14px; line-height: 16px;">Coming Soon!</span></span></td>
<td><span><span class="mtq_css_letter_button" style="font-size: 14px; line-height: 16px;">Coming Soon!</span></span></td>
</tr>
</tbody>
</table>
</div>
<?php } else { ?>
  
  
  
  
  
    <h2>
    <?php  _e("Choose a color to match your Site's Theme!", 'mtouchquiz'); ?>
  </h2>
  <hr />
  <strong>Select a Color from the drop down list:</strong><br />
  <form id="mtouchquiz" name="mtouchquiz" action="" method='POST'>
  <input type="hidden" name="mtq_theme_update" value="Y">
  <select name="color">
  <?php $mtq_possible_colors = mtq_color_options();
  $current_color=get_option("mtouchquiz_color"); 
  $mtq_the_color='';
  for ($count=0; $count<=count($mtq_possible_colors); $count++) {
		$mtq_the_color=$mtq_possible_colors[$count];
		if ($current_color== $mtq_the_color ) {
			echo  "<option value='".$mtq_the_color."' selected='selected'>".$mtq_the_color."</option>";
		} else {
		 echo  "<option value='".$mtq_the_color."'>".$mtq_the_color."</option>";
		}
  }
  ?>
  </select>
  <input type="submit" value="Submit Color Choice" name="submit" class="button-primary">
  <?php if ($mtq_just_updated) {
	  echo "Update Saved!"; 
	  
  }?>
  </form>
  
  <div style="width:600px">
  
  <table>
<tbody>
<tr>
<td style="width: 85px;"><span class="mtq_color_blue"><span class="mtq_css_letter_button">A</span>blue</span></td>
<td style="width: 85px;"><span class="mtq_color_green"><span class="mtq_css_letter_button">A</span>green</span></td>
<td style="width: 85px;"><span class="mtq_color_red"><span class="mtq_css_letter_button">A</span>red</span></td>
<td style="width: 85px;"><span class="mtq_color_orange"><span class="mtq_css_letter_button">A</span>orange</span></td>
<td style="width: 85px;"><span class="mtq_color_yellow"><span class="mtq_css_letter_button">A</span>yellow</span></td>
</tr>
<tr>
<td style="width: 85px;"><span class="mtq_color_indigo"><span class="mtq_css_letter_button">A</span>indigo</span></td>
<td style="width: 85px;"><span class="mtq_color_violet"><span class="mtq_css_letter_button">A</span>violet</span></td>
<td style="width: 85px;"><span class="mtq_color_fuchsia"><span class="mtq_css_letter_button">A</span>fuchsia</span></td>
<td style="width: 85px;"><span class="mtq_color_khaki"><span class="mtq_css_letter_button">A</span>khaki</span></td>
<td style="width: 85px;"><span class="mtq_color_burgundy"><span class="mtq_css_letter_button">A</span>burgundy</span></td>
</tr>
<tr>
<td style="width: 85px;"><span class="mtq_color_black"><span class="mtq_css_letter_button">A</span>black</span></td>
<td style="width: 85px;"><span class="mtq_color_lightblue"><span class="mtq_css_letter_button">A</span>lightblue</span></td>
<td style="width: 85px;"><span class="mtq_color_teal"><span class="mtq_css_letter_button">A</span>teal</span></td>
<td style="width: 85px;"><span class="mtq_color_lightgreen"><span class="mtq_css_letter_button">A</span>lightgreen</span></td>
<td style="width: 85px;"><span class="mtq_color_lightpink"><span class="mtq_css_letter_button">A</span>lightpink</span></td>
</tr>
<tr>
<td style="width: 85px;"><span class="mtq_color_darkgreen"><span class="mtq_css_letter_button">A</span>darkgreen</span></td>
<td style="width: 85px;"><span class="mtq_color_brown"><span class="mtq_css_letter_button">A</span>brown</span></td>
<td style="width: 85px;"><span class="mtq_color_purple"><span class="mtq_css_letter_button">A</span>purple</span></td>
<td style="width: 85px;"><span class="mtq_color_navy"><span class="mtq_css_letter_button">A</span>navy</span></td>
<td style="width: 85px;"><span class="mtq_color_darkpink"><span class="mtq_css_letter_button">A</span>darkpink</span></td>
</tr>
<tr>
<td style="width: 85px;"><span class="mtq_color_lavender"><span class="mtq_css_letter_button">A</span>lavender</span></td>
<td><span><span class="mtq_css_letter_button" style="font-size: 14px; line-height: 16px;">Coming Soon!</span></span></td>
<td><span><span class="mtq_css_letter_button" style="font-size: 14px; line-height: 16px;">Coming Soon!</span></span></td>
<td><span><span class="mtq_css_letter_button" style="font-size: 14px; line-height: 16px;">Coming Soon!</span></span></td>
<td><span><span class="mtq_css_letter_button" style="font-size: 14px; line-height: 16px;">Coming Soon!</span></span></td>
</tr>
</tbody>
</table>
</div>
  
  <?php }?>
  

<?php mtq_premium_list() ?>
