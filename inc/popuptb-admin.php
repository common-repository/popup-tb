<?php
# code setting admin popup tb
function popuptb_options_page() {
	global $popuptb_options;
	ob_start(); 
	?>
	<div class="wrap ft-popuptb">
	  <div class="ft-popuptb-box">
		<div class="ft-menu">
		<img title="Popup ThunderBolt" src="<?php echo POPUPTB_URL .'img/logo.svg'; ?>">
		</div>
		<div class="ft-main">
			<?php if( isset($_GET['settings-updated']) ) { ?>
			<div class="ft-updated">
			<?php _e('Settings saved', 'popup-tb') ?>
			</div>
			<?php } ?>
			<form method="post" action="options.php">
			<?php settings_fields('popuptb_settings_group'); ?> 
			<h2><i class="fa-solid fa-magnifying-glass"></i> <?php _e('Optimate search realtime', 'popup-tb') ?></h2>
				<p>
				<label class="nut-switch">
				<input type="checkbox" name="popuptb_settings[main-search1]" value="1" <?php if ( isset($popuptb_options['main-search1']) && 1 == $popuptb_options['main-search1'] ) echo 'checked="checked"'; ?> />
				<span class="slider"></span></label>
				<label class="ft-label-right"><?php _e('Turn on speed search', 'popup-tb'); ?></label>
				</p>
				<div class="tb-doi" id="tb-doi-sogiay" style="display:none"><img src="<?php echo POPUPTB_URL . 'img/load.gif'; ?>" /> <?php _e('Initialize automatically later <span id="sogiay" style="padding: 5px;">3</span>s', 'popup-tb'); ?></div>
				<p>
				<input class="ft-input-small" name="popuptb_settings[main-search-c1]" type="number" placeholder="10" value="<?php if(!empty($popuptb_options['main-search-c1'])){echo $popuptb_options['main-search-c1'];} ?>"/>
				<label class="ft-label-right"><?php _e('Show quantity', 'popup-tb'); ?></label>
				</p>
				
				<p class="ft-note"><i class="fa-solid fa-lightbulb"></i> <?php _e('Enter the number of articles and products displayed when you search', 'popup-tb'); ?></p>
				
				<?php 
				$args = array(
				'public'   => true,
				);
				$post_types = get_post_types($args, 'objects'); 
				foreach ($post_types as $post_type_object) {
					if ($post_type_object->name == 'attachment' || $post_type_object->name == 'page') {
						continue;
					}
					?>
					<label class="nut-switch">
						<input type="checkbox" name="popuptb_settings[main-search-posttype][]" value="<?php echo $post_type_object->name; ?>" <?php if (isset($popuptb_options['main-search-posttype']) && in_array($post_type_object->name, $popuptb_options['main-search-posttype'])) echo 'checked="checked"'; ?> />
						<span class="slider"></span>
					</label>
					<label class="ft-label-right"><?php echo $post_type_object->labels->name; ?></label>
					</p>
					<?php
				}
				?>
				<div class="save-json">
				<a href="javascript:void(0)" id="save-json"><i class="fa-solid fa-database"></i> <?php _e('Create data', 'popup-tb'); ?></a>
				<a href="javascript:void(0)" id="delete-json-folder"><i class="fa-solid fa-trash"></i> <?php _e('Delete data', 'popup-tb'); ?></a>
				<div id="tb-json"></div>
				<div class="tb-doi" id="tb-doi" style="display:none"><img src="<?php echo POPUPTB_URL . 'img/load.gif'; ?>" /> <span id="starprocess"></span></div>
				<script>
				jQuery(document).ready(function($) {
						$('input[name="popuptb_settings[main-search1]"]').change(function() {
							if ($(this).is(':checked')) {
								$('#tb-doi-sogiay').show();
								var $targetCheckbox = $('input[name="popuptb_settings[main-search-posttype][]"][value="post"]:first');
								if ($targetCheckbox.length > 0) {
									$targetCheckbox.prop('checked', true);
									var countdown = 3;
										var countdownInterval = setInterval(function() {
											$('#sogiay').text(countdown);
											countdown--;
											if (countdown < 0) {
												clearInterval(countdownInterval);
												$('#tb-doi-sogiay').hide();
												$('#save-json').trigger('click');
												$('html, body').animate({
													scrollTop: $('#save-json').offset().top
												}, 1000);
											}
										}, 1000);
								}
							}else{
								$('input[name="popuptb_settings[main-search-posttype][]"]').prop('checked', false);
							}
						});
				});
				jQuery(document).ready(function($){
					jQuery(document).ready(function($){
					$('#save-json').on('click', function() {
						$('#tb-doi').show();
						var ajax_nonce = '<?php echo wp_create_nonce('popuptb_nonce_key'); ?>';
						var page = 1;
						var sopost = 0;
						function callAjax() {
							$.ajax({
								type: 'POST',
								url: '<?php echo admin_url('admin-ajax.php'); ?>',
								data: {
									action: 'popuptb_json_file',
									security: ajax_nonce,
									page: page
								},
								success: function(response) {
									var jsonResponse = JSON.parse(response);
									if (jsonResponse.page === -1) {
										$('#loadbarprocess').html('<span><?php _e("Completed data count: '+sopost+'", "popup-tb"); ?></span>');
										$('#tb-doi').hide();
									} else {
										sopost = jsonResponse.count;
										var html = '<span><?php _e("Please wait: '+sopost+'", "popup-tb"); ?></span>';
										$('#starprocess').html(html);
										page = jsonResponse.page;
										callAjax();
									}
								}
							});
						}
						callAjax();
					});
					$('#delete-json-folder').on('click', function() {
						var ajax_nonce = '<?php echo wp_create_nonce('popuptb_json_nonce'); ?>'; 
						function callAjax() {
							$.ajax({
								type: 'POST',
								url: '<?php echo admin_url('admin-ajax.php'); ?>',
								data: {
									action: 'popuptb_json_folder', 
									security: ajax_nonce
								},
								success: function(response) {
									$('#loadbarprocess').html('<span><?php _e("Deleted successfully", "popup-tb"); ?></span>'); 
								}
							});
						}
						callAjax();
					});	
				});
					
				});
				</script>
				<div id="loadbarprocess"></div>
				</div>
				<p class="ft-note"><i class="fa-solid fa-lightbulb"></i> <?php _e('Configure options and generate search data. If you want to refresh, you can delete your search data and create it again', 'popup-tb'); ?></p>
			<div class="ft-submit">
				<button type="submit"><i class="fa-solid fa-floppy-disk"></i> <?php _e('SAVE CONTENT', 'popup-tb'); ?></button>
			</div>
			</form>
		</div>
	  </div>	
	</div>
	<script>
	jQuery(document).ready(function($) {
		$('form input[type="checkbox"]').change(function() {
			var currentForm = $(this).closest('form');
			$.ajax({
				type: 'POST',
				url: currentForm.attr('action'), 
				data: currentForm.serialize(), 
				success: function(response) {
					console.log('Turn on successfully');
				},
				error: function() {
					console.log('Error in AJAX request');
				}
			});
		});
	});
	</script>
	<?php
	echo ob_get_clean();
}
# add menu admin
function popuptb_add_options_link() {
	$icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+Cjxzdmcgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEwMCAxMDAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeG1sbnM6c2VyaWY9Imh0dHA6Ly93d3cuc2VyaWYuY29tLyIgc3R5bGU9ImZpbGwtcnVsZTpldmVub2RkO2NsaXAtcnVsZTpldmVub2RkO3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2UtbWl0ZXJsaW1pdDoyOyI+CiAgICA8Zz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTU1Ljg3NSwzMi4zODJMOTUuMiwxMi43MDJMOTUuMiw2NC43MDFMNTAsODcuMjk4TDQuOCw2NC43MDFMNC44LDEyLjcwMkwyOC4yNzMsMjQuNDQ1TDIwLjc5NywzMS4xMTFMMTMuOCwyNy41NTNMMTMuNzA2LDYwLjA5N0w0OS45MzksNzYuMjE1TDgwLjg4Nyw2MS42NjVMNTAuMzE3LDQ2LjEyMUw1Ny45ODIsMzkuMjg3TDk1LjA1Nyw1Ny43NDFMNTguMzU5LDMxLjEzOUw1NS44NzYsMzIuMzgyTDU1Ljg3NSwzMi4zODJaTTQzLjg4NiwzMi4zODJMNDMuOTMsMzIuMjc3TDQ0LjEzOSwzMi4zODJMNDMuODg2LDMyLjM4MloiIHN0eWxlPSJmaWxsOndoaXRlOyIvPgogICAgICAgICAgICA8ZyB0cmFuc2Zvcm09Im1hdHJpeCgwLjA3NzU2MTcsMCwwLDAuMDc5MDQ0MiwyMS4xMjQ3LDE1Ljc1NzcpIj4KICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik0zNDkuNCw0NC42QzM1NS4zLDMwLjkgMzUwLjksMTQuOSAzMzguOCw2LjFDMzI2LjcsLTIuNyAzMTAuMiwtMS45IDI5OC45LDcuOUw0Mi45LDIzMS45QzMyLjksMjQwLjcgMjkuMywyNTQuOCAzNCwyNjcuMkMzOC43LDI3OS42IDUwLjcsMjg4IDY0LDI4OEwxNzUuNSwyODhMOTguNiw0NjcuNEM5Mi43LDQ4MS4xIDk3LjEsNDk3LjEgMTA5LjIsNTA1LjlDMTIxLjMsNTE0LjcgMTM3LjgsNTEzLjkgMTQ5LjEsNTA0LjFMNDA1LjEsMjgwLjFDNDE1LjEsMjcxLjMgNDE4LjcsMjU3LjIgNDE0LDI0NC44QzQwOS4zLDIzMi40IDM5Ny40LDIyNC4xIDM4NCwyMjQuMUwyNzIuNSwyMjQuMUwzNDkuNCw0NC42WiIgc3R5bGU9ImZpbGw6d2hpdGU7ZmlsbC1ydWxlOm5vbnplcm87Ii8+CiAgICAgICAgICAgIDwvZz4KICAgICAgICA8L2c+CiAgICA8L2c+Cjwvc3ZnPgo=';
	add_menu_page('ThunderBolt', 'ThunderBolt', 'manage_options', 'popuptb-options', 'popuptb_options_page', $icon);
}
add_action('admin_menu', 'popuptb_add_options_link');
# add database
function popuptb_register_settings() {
	register_setting('popuptb_settings_group', 'popuptb_settings');
}
add_action('admin_init', 'popuptb_register_settings');