<?php
	if ( !is_admin() )
		return;
	// update options on submit
	if ( isset($_POST['webthumb-submit']) ) {
		$options['service'] = $_POST['webthumb-service'];
		$options['apikey']  = $_POST['webthumb-apikey'];
		$options['seckey']  = $_POST['webthumb-seckey'];
		$options['acckey']  = $_POST['webthumb-acckey'];
		$options['size']    = $_POST['webthumb-size'];
		$options['agemax']  = is_numeric($_POST['webthumb-agemax']) ? $_POST['webthumb-agemax'] : -1;
		$options['showurl'] = isset($_POST['webthumb-showurl']);
		$options['showsml'] = isset($_POST['webthumb-showsml']);
		$options['imgdir']  = $uploadsPath;
		$options['tryweb2'] = isset($_POST['webthumb-tryweb2']);
		$ures = update_option('webthumb', $options);
		if ( $ures ) 
			echo "<div class='alert-message success'>".__('Your settings were successfully saved', 'webthumb')."</div>";
		else
			echo "<div class='alert-message error'>".__('Your settings could not be saved or are not changed. Please try again.', 'webthumb')."</div>";
	}
	// get options
	$options = get_option('webthumb');
	// set default options
	if ( !is_array( $options ) ) {
		$options = array(
			'service' => 'bluganet',
			'apikey'  => '12345678901234567890123456789012',
			'seckey'  => '1ab2',
			'acckey'  => '1234567890abcde',
			'size'    => 'medium',
			'agemax'  => 30,
			'showurl' => true,
			'showsml' => false,
			'imgdir'  => '',
			'imgurl'  => '',
			'tryweb2' => true
		);
		update_option('webthumb', $options);
	}
	// display settings page
	?>
	<form method="post" action="" id="webthumb-conf">
		<h3><?php _e('Configure WPF-WebThumb options', 'webthumb'); ?></h3>
		<p><?php _e('Select the service to use (see FAQ):', 'webthumb'); ?>
			<select name="webthumb-service" id="webthumb-service" >
				<option <?php if ("bluganet"==$options['service']) echo 'selected';  ?> value="bluganet">bluga.net</option>
				<option <?php if ("pagepeeker"==$options['service']) echo 'selected';  ?> value="pagepeeker">PagePeeker</option>
				<option <?php if ("shrinktheweb"==$options['service']) echo 'selected'; ?> value="shrinktheweb">ShrinkTheWeb (jscript)</option>
				<option <?php if ("shrinktheweb2"==$options['service']) echo 'selected'; ?> value="shrinktheweb2">ShrinkTheWeb (img cache)</option>
			</select>
		</p>
		<p><input type="checkbox" <?php checked( (bool) $options['tryweb2'], true ); ?> name="webthumb-tryweb2" id="webthumb-tryweb2" />
		<?php _e(' Try <a href="http://www.webtopicture.com/" target="_blank">WebToPicture</a> first.', 'webthumb'); ?></p>
		<p><?php _e('Please register at <a href="http://webthumb.bluga.net/home" target="_blank">bluga.net webthumb API</a> service', 'webthumb'); ?> <?php _e('and insert here your APIKEY: ', 'webthumb'); ?>
		<input type="text" value="<?php echo ($options['apikey']) ? $options['apikey'] : ""; ?>" name="webthumb-apikey" id="webthumb-apikey" size="40" maxlength="32" />
		</p>
		<p><?php _e('More info about <a href="http://pagepeeker.com/" target="_blank">PagePeeper</a> service.', 'webthumb'); ?>
		</p>
		<p><?php _e('Please register at <a href="http://www.shrinktheweb.com" target="_blank">ShrinkTheWeb</a> service', 'webthumb'); ?> <?php _e('and insert here your ACCESS KEY: ', 'webthumb'); ?>
		<input type="text" value="<?php echo ($options['acckey']) ? $options['acckey'] : ""; ?>" name="webthumb-acckey" id="webthumb-acckey" size="40" maxlength="15" />
		<?php _e(' and here your SECRET KEY: ', 'webthumb'); ?>
		<input type="text" value="<?php echo ($options['seckey']) ? $options['seckey'] : ""; ?>" name="webthumb-seckey" id="webthumb-seckey" size="10" maxlength="5" />
		</p>
		<h3><?php _e('Some defaults:', 'webthumb'); ?></h3>
		<p><?php _e('select thumbnail default download size:', 'webthumb'); ?>
			<select name="webthumb-size" id="webthumb-size">
				<option <?php if ("tiny"==$options['size']) echo 'selected';  ?> value="tiny">tiny</option>
				<option <?php if ("small"==$options['size']) echo 'selected';  ?> value="small">small</option>
				<option <?php if ("medium"==$options['size']) echo 'selected';  ?> value="medium">medium</option>
				<option <?php if ("big"==$options['size']) echo 'selected';  ?> value="big">big</option>
			</select>
		</p>
		<p><?php _e('Set maximum cache age in days (0=do not cache, -1=never refresh): ', 'webthumb'); ?>
		<input type="text" value="<?php echo ($options['agemax']) ? $options['agemax'] : ""; ?>" name="webthumb-agemax" id="webthumb-agemax" size="10" maxlength="3" />
		</p>
		<p><input type="checkbox" <?php checked( (bool) $options['showurl'], true ); ?> name="webthumb-showurl" id="webthumb-showurl" />
		<?php _e(' show URL', 'webthumb'); ?>
		</p>
		<p><input type="checkbox" <?php checked( (bool) $options['showsml'], true ); ?> name="webthumb-showsml" id="webthumb-showsml" />
		<?php _e(' show small image; show bigger image when mouse is over', 'webthumb'); ?>
		</p>
		<p><?php _e('thumbnails dir is: ', 'webthumb');  echo '<strong>'.$uploadsPath.'</strong>' ?></p>
		<p><?php _e('thumbnails url is: ', 'webthumb');  echo '<strong>'.$uploadsUrl.'</strong>' ?></p>
		
		<p class="submit">
			<input type="submit" name="webthumb-submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
