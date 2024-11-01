<?php
	if ( !is_admin() )
		return;

	if ( isset($_POST['delete']) ) {
		$del = $_POST['delete'];
		if ( file_exists( $uploadsPath ."/". $del ) && is_file( $uploadsPath ."/". $del )  ){
			unlink( $uploadsPath ."/". $del );
		}
	}
	
	if ( isset($_POST['update']) ) {
		$upd = $_POST['update'];
		if ( $upd != '') {
			//set url and call grabthumb by mean of webthumbs with options defaults
			$atts = array ('url' => $upd);
			echo "<div class='alert-message success'>".__('Updating from URL ', 'webthumb').$upd.webthumb_shortcode( $atts )."</div>";
		}
	}
	
	if ( is_readable ( $uploadsPath ) && $handle = opendir( $uploadsPath ) ) { 
		$entrycnt = 0;
		$datenow = time();
		$options = get_option('webthumb');
		$service = $options['service'];
		$okserv  = ($service != 'shrinktheweb');
		$entries = array();
		
		while (false !== ($entry = readdir( $handle )) ) {
			if ( webthumb_endsWith( $entry, '.png' ) ) {
				$entries[] = $entry;
				$entrycnt = $entrycnt + 1;
			}
		}
		closedir( $handle );
		asort( $entries );
		$nexto = 'desc';
		if ( isset($_GET['order']) ) {
			if ('desc' == $_GET['order']) {
				$nexto = 'asc';
				arsort( $entries );
			}
		}
		?>
		<table cellspacing="0" class="wp-list-table widefat plugins">
			<thead>
				<tr>
					<th style="" class="manage-column column-title sortable desc" id="name" scope="col"><a href="?page=webthumb&tab=cache&orderby=name&order=<?php echo $nexto;?>">
					<span><?php _e('Image thumbnail name', 'webthumb'); ?></span><span class="sorting-indicator"></span></a></th>
					<th style="" class="manage-column" id="imageview" scope="col"><?php _e('Preview', 'webthumb'); ?></th>
					<th style="" class="manage-column" id="age" scope="col"><?php _e('Age (days, hours, min, sec)', 'webthumb'); ?></th>
					<th style="" class="manage-column" id="lastmod" scope="col"><?php _e('Last modified', 'webthumb'); ?></th>
					<th style="" class="manage-column" id="filesize" scope="col"><?php _e('File size (Byte)', 'webthumb'); ?></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th style="" class="manage-column" id="name" scope="col"><?php _e('Image thumbnail name', 'webthumb'); ?></th>
					<th style="" class="manage-column" id="imageview" scope="col"><?php _e('Preview', 'webthumb'); ?></th>
					<th style="" class="manage-column" id="age" scope="col"><?php _e('Age (days, hours, min, sec)', 'webthumb'); ?></th>
					<th style="" class="manage-column" id="lastmod" scope="col"><?php _e('Last modified', 'webthumb'); ?></th>
					<th style="" class="manage-column" id="filesize" scope="col"><?php _e('File size (Byte)', 'webthumb'); ?></th>
				</tr>
			</tfoot>

			<tbody id="the-list">
			<?php
			foreach ($entries as &$entry) {
				$file = $uploadsPath . "/" . $entry;
				?>
				<tr>
					<td style="border:none; padding-bottom:0;">
						<strong><?php echo $entry; ?></strong>
					</td>
					<td style="border:none; padding-bottom:0; float:left;">
						<a href="<?php echo ( $uploadsUrl.'/'.$entry ); ?>" class="preview"><img src="<?php echo ( $uploadsUrl.'/'.$entry ); ?>" alt=" " width="30px" /></a>
					</td>
					<td style="border:none; padding-bottom:0;">
						<?php echo webthumb_time_diff_conv( $datenow, filemtime( $file ) ) ; ?>
					</td>
					<td style="border:none; padding-bottom:0;">
						<?php echo date ('F d Y H:i:s', filemtime( $file ) ); ?>
					</td>
					<td style="border:none; padding-bottom:0;">
						<?php echo number_format ( filesize( $file ) , 0, '.', ',' ); ?>
					</td>
				</tr>	
				<tr>
					<td colspan=4 style="padding: 0px 7px 7px 7px;">
						<a href="#" onclick="webthumb_delfile('<?php echo $entry ?>'); return false;"><?php _e('Delete', 'webthumb'); ?></a> <?php if ($okserv) {?> |
						<a href="#" onclick="webthumb_updatetn('<?php echo $entry ?>'); return false;"><?php _e('Update thumbnail', 'webthumb'); }?>
					</td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
	<br/>
<?php 
	} 

function webthumb_time_diff_conv( $start, $s ) {
	$t = array( //suffixes
		'd' => 86400,
		'h' => 3600,
		'm' => 60,
	);
	$s = abs( $s - $start );
        $string = '';
	foreach( $t as $key => &$val ) {
		$$key = floor( $s/$val );
		$s -= ( $$key * $val );
		$string .= ( $$key == 0 ) ? '' : $$key . "$key ";
	}
return $string . $s. 's';
}
?>

<script type="text/javascript">
function webthumb_delfile(file)
{
	if (confirm('Delete this file: ' + file + '?'))
	{
		document.formdel.delete.value = file;	
		document.formdel.submit();	
	}
}

function webthumb_updatetn(file)
{
	var fileu = file.replace('.png','').replace('[tiny]','').replace('[small]','').replace('[medium]','').replace('[big]','').replace(/_/g,'.');
	var fileurl = prompt('Edit the file URL to update: ', 'http://' + fileu)
	{
		if (fileurl!=null && fileurl!="")
		{
			document.formdel.delete.value = file;
			document.formdel.update.value = fileurl;	
			document.formdel.submit();
		}
	}
}
</script>
<form method="post" name="formdel" style="display:none;">
	<input type="hidden" name="delete" />
	<input type="hidden" name="update" />
</form>	
		