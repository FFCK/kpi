<h3>Pro Registration</h3>			
<?php if(isValidFAQKey()): ?>	
<p class="easy_faq_registered">Your plugin is succesfully registered and activated!</p>
<?php else: ?>
<p>Fill out the fields below, if you have purchased the pro version of the plugin, to activate additional features such as Front-End FAQ Submission.</p>
<p class="easy_faq_not_registered">Your plugin is not succesfully registered and activated. <a href="https://goldplugins.com/our-plugins/easy-faqs-details/upgrade-to-easy-faqs-pro/?utm_source=registration_fields" target="_blank">Click here</a> to upgrade today!</p>
<?php endif; ?>	

<?php if(!isValidMSFAQKey()): ?>
<table class="form-table">
	<?php
		// Registration Email Address (text)
		$this->shed->text( array('name' => 'easy_faqs_registered_name', 'label' =>'Email Address', 'value' => get_option('easy_faqs_registered_name'), 'description' => 'This is the e-mail address that you used when you registered the plugin.') );

		// API Key (text)
		$this->shed->text( array('name' => 'easy_faqs_registered_key', 'label' =>'API Key', 'value' => get_option('easy_faqs_registered_key'), 'description' => 'This is the API Key that you received after registering the plugin.') );
	?>

</table>
	
<table class="form-table" style="display: none;">
	<tr valign="top">
		<th scope="row"><label for="easy_faqs_registered_url">Website Address</label></th>
		<td><input type="text" name="easy_faqs_registered_url" id="easy_faqs_registered_url" value="<?php echo get_option('easy_faqs_registered_url'); ?>"  style="width: 250px" />
		<p class="description">This is the Website Address that you used when you registered the plugin.</p>
		</td>
	</tr>
</table>
	
<?php endif; ?>