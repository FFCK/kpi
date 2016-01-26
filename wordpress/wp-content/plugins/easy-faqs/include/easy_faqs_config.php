<?php

class EasyFAQs_Config
{
	function all_themes($is_pro = false, $disable_if_not_pro = true)
	{
		return array_merge(self::free_themes(), self::pro_themes($is_pro, $disable_if_not_pro));
	}

	function free_themes()
	{
		//array of free themes that are available
		//includes names
		return array(
			'free_themes' => array(
				'free_themes' => 'Basic Themes',
				'default_style' => 'Default Theme',
				'no_style' 		=> 'No Theme'
			),
			'office' => array(
				'office' => 'Office Theme',
				'office-gray' => 'Office Theme - Gray',
				'office-red' => 'Office Theme - Red',
				'office-blue' => 'Office Theme - Blue',
				'office-green' => 'Office Theme - Green',
				'office-skyblue' => 'Office Theme - Sky Blue',
				'office-teal' => 'Office Theme - Teal',
				'office-purple' => 'Office Theme - Purple',
				'office-gold' => 'Office Theme - Gold',
				'office-manilla' => 'Office Theme - Manilla',
				'office-orange' => 'Office Theme - Orange',
			)
		);		
	}
	
	function pro_themes($is_pro = false, $disable_if_not_pro = true)
	{
		//array of pro themes that are available
		//includes names
		$pro_str = $is_pro ? '' : ' (PRO)';
		$pro_themes = array(
			'banner' => array(
				'banner' => 'Banner Theme' . $pro_str,
				'banner-gold' => 'Banner Theme - Gold',
				'banner-red' => 'Banner Theme - Red',
				'banner-green' => 'Banner Theme - Green',
				'banner-blue' => 'Banner Theme - Blue',
				'banner-purple' => 'Banner Theme - Purple',
				'banner-teal' => 'Banner Theme - Teal',
				'banner-orange' => 'Banner Theme - Orange',
				'banner-gray' => 'Banner Theme - Gray',
				'banner-maroon' => 'Banner Theme - Maroon',
				'banner-brown' => 'Banner Theme - Brown',
			),
			'casualfriday' => array(
				'casualfriday' => 'Casual Friday' . $pro_str,
				'casualfriday-green' => 'Casual Friday - Green',
				'casualfriday-red' => 'Casual Friday - Red',
				'casualfriday-blue' => 'Casual Friday - Blue',
				'casualfriday-gray' => 'Casual Friday - Gray',
				'casualfriday-maroon' => 'Casual Friday - Maroon',
				'casualfriday-gold' => 'Casual Friday - Gold',
				'casualfriday-purple' => 'Casual Friday - Purple',
				'casualfriday-orange' => 'Casual Friday - Orange',
				'casualfriday-slate' => 'Casual Friday - Slate',
				'casualfriday-teal' => 'Casual Friday - Teal',
				'casualfriday-brown' => 'Casual Friday - Brown',
				'casualfriday-indigo' => 'Casual Friday - Indigo',
				'casualfriday-pink' => 'Casual Friday - Pink',
			),			
			'classic' => array(
				'classic' => 'Classic Theme' . $pro_str,
				'classic-gray' => 'Classic Theme - Black',
				'classic-green' => 'Classic Theme - Green',
				'classic-purple' => 'Classic Theme - Purple',
				'classic-black' => 'Classic Theme - Black',
				'classic-orange' => 'Classic Theme - Orange',
				'classic-blue' => 'Classic Theme - Blue',
				'classic-red' => 'Classic Theme - Red',
				'classic-brown' => 'Classic Theme - Brown',
				'classic-gold' => 'Classic Theme - Gold',
				'classic-teal' => 'Classic Theme - Teal',
				'classic-pink' => 'Classic Theme - Pink',
				'classic-indigo' => 'Classic Theme - Indigo',
				'classic-maroon' => 'Classic Theme - Maroon',
			),			
			'corporate' => array(
				'corporate' => 'Corporate Theme' . $pro_str,
				'corporate-blue' => 'Corporate Theme - Blue',
				'corporate-red' => 'Corporate Theme - Red',
				'corporate-gray' => 'Corporate Theme - Gray',
				'corporate-green' => 'Corporate Theme - Green',
				'corporate-teal' => 'Corporate Theme - Teal',
				'corporate-gold' => 'Corporate Theme - Gold',
				'corporate-skyblue' => 'Corporate Theme - Sky Blue',
				'corporate-slate' => 'Corporate Theme - Slate',
				'corporate-purple' => 'Corporate Theme - Purple',
				'corporate-orange' => 'Corporate Theme - Orange',
				'corporate-indigo' => 'Corporate Theme - Indigo',
				'corporate-brown' => 'Corporate Theme - Brown',
			),
			'deco' => array(
				'deco' => 'Deco Theme' . $pro_str,
				'deco-salmon' => 'Deco Theme - Salmon',
				'deco-smoke' => 'Deco Theme - Smoke',
				'deco-gold' => 'Deco Theme - Gold',
				'deco-teal' => 'Deco Theme - Teal',
				'deco-orange' => 'Deco Theme - Orange',
				'deco-purple' => 'Deco Theme - Purple',
				'deco-blue' => 'Deco Theme - Blue',
				'deco-green' => 'Deco Theme - Green',
				'deco-gray' => 'Deco Theme - Gray',
				'deco-red' => 'Deco Theme - Red',
				'deco-brown' => 'Deco Theme - Brown',
				'deco-indigo' => 'Deco Theme - Indigo',
				'deco-pink' => 'Deco Theme - Pink',
				'deco-maroon' => 'Deco Theme - Maroon',
				'deco-lightgreen' => 'Deco Theme - Light Green',
			),
			'future' => array(
				'future' => 'Future Theme' . $pro_str,
				'future-slate' => 'Future Theme - Slate',
				'future-gray' => 'Future Theme - Gray',
				'future-skyblue' => 'Future Theme - Sky Blue',
				'future-red' => 'Future Theme - Red',
				'future-green' => 'Future Theme - Green',
				'future-gold' => 'Future Theme - Gold',
				'future-blue' => 'Future Theme - Blue',
				'future-purple' => 'Future Theme - Purple',
				'future-teal' => 'Future Theme - Teal',
				'future-orange' => 'Future Theme - Orange',
				'future-maroon' => 'Future Theme - Maroon',
				'future-brown' => 'Future Theme - Brown',
			),			
			'modern' => array(
				'modern' => 'Modern Theme' . $pro_str,
				'modern-gray' => 'Modern Theme - Gray',
				'modern-gold' => 'Modern Theme - Gold',
				'modern-green' => 'Modern Theme - Green',
				'modern-lightgreen' => 'Modern Theme - Light Green',
				'modern-blue' => 'Modern Theme - Blue',
				'modern-indigo' => 'Modern Theme - Indigo',
				'modern-purple' => 'Modern Theme - Purple',
				'modern-slate' => 'Modern Theme - Slate',
				'modern-orange' => 'Modern Theme - Orange',
				'modern-brown' => 'Modern Theme - Brown',
				'modern-maroon' => 'Modern Theme - Maroon',
				'modern-red' => 'Modern Theme - Red',
				'modern-teal' => 'Modern Theme - Teal',
			),
			'notch' => array(
				'notch' => 'Notch Theme' . $pro_str,
				'notch-red' => 'Notch Theme - Red',
				'notch-purple' => 'Notch Theme - Purple',
				'notch-blue' => 'Notch Theme - Blue',
				'notch-green' => 'Notch Theme - Green',
				'notch-orange' => 'Notch Theme - Orange',
				'notch-gray' => 'Notch Theme - Gray',
				'notch-teal' => 'Notch Theme - Teal',
				'notch-gold' => 'Notch Theme - Gold',
				'notch-slate' => 'Notch Theme - Slate',
				'notch-maroon' => 'Notch Theme - Maroon',
			),
			'retro' => array(
				'retro' => 'Retro Theme' . $pro_str,
				'retro-blue' => 'Retro Theme - Blue',
				'retro-red' => 'Retro Theme - Red',
				'retro-green' => 'Retro Theme - Green',
				'retro-maroon' => 'Retro Theme - Maroon',
				'retro-teal' => 'Retro Theme - Teal',
				'retro-gray' => 'Retro Theme - Gray',
				'retro-gold' => 'Retro Theme - Gold',
				'retro-purple' => 'Retro Theme - Purple',
				'retro-orange' => 'Retro Theme - Orange',
				'retro-slate' => 'Retro Theme - Slate',
				'retro-brown' => 'Retro Theme - Brown',
			)
		);		
		
		if (!$is_pro && $disable_if_not_pro)
		{
			foreach ($pro_themes as $group => $themes)
			{
				$skip_next = true;
				foreach ($themes as $slug => $theme_name) {
					if ($skip_next) {
						$skip_next = false;
						continue;
					}
					
					$pro_themes[$group][$slug] = array('name' => $theme_name, 'disabled' => true);
					 
				}
			}
		}
			
			
		return $pro_themes;
	}
	
	function output_theme_selector($field_id, $field_name, $current = '', $is_pro = false)
	{
?>		
		<select class="widefat" id="<?php echo $field_id ?>" name="<?php echo $field_name; ?>">
			<?php
				$themes = self::all_themes($is_pro);
				foreach ($themes as $group_slug => $group_themes)
				{
					$skip_next = true;
					foreach ($group_themes as $theme_slug => $theme_name) {
						if ($skip_next) {
							printf('<optgroup label="%s">', $theme_name);
							$skip_next = false;
							continue;
						}
						$selected = ( strcmp($theme_slug, $current) == 0 ) ? 'selected="selected"' : '';
						printf('<option value="%s" %s>%s</option>', $theme_slug, $selected, $theme_name);
					}
					echo '</optgroup>';
				}
				?>
			</select>
<?php
	}
}