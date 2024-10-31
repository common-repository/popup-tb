<?php
global $popuptb_options;
# tao custom post type post and product
if (isset($popuptb_options['main-search1'])){
// xóa post khoi json neu xoa
function popuptb_delete_search_auto_when_delete_post($post_id) {
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/json/data-search.json';
    $existing_data = array();
    if (file_exists($file_path)) {
        $existing_data = json_decode(file_get_contents($file_path), true);
        foreach ($existing_data as $key => $item) {
            if ($item['ID'] == $post_id) {
                unset($existing_data[$key]);
                break; 
            }
        }
        // Reset array keys
        $existing_data = array_values($existing_data);
        file_put_contents($file_path, json_encode($existing_data));
    }
}
add_action('delete_post', 'popuptb_delete_search_auto_when_delete_post');
// them post vào json
function popuptb_add_search_auto_whenpublish($post_id ) {
        global $popuptb_options;
        $post = get_post($post_id);
        $type = get_post_type($post->ID);
        if (isset($popuptb_options['main-search-posttype'])) {
            if(count($popuptb_options['main-search-posttype'])>0){
                $allowed_post_types = $popuptb_options['main-search-posttype'];
                if (in_array($type, $allowed_post_types)) {
                    $filed = array(
                    'ID',
                    'title',
                    'url',
                    'thum',
					'price',
                    'taxonomy'
                );
                    $item = array('type' => $type);
                    foreach ($filed as $field) {
                        switch ($field) {
                            case 'ID':
                                $item[$field] = $post->ID;
                            break;
                            case 'title':
                                $item[$field] = get_the_title($post->ID);
                                break;
                            case 'url':
                                $item[$field] = get_permalink($post->ID);
                                break;
                            case 'thum':
                                $item[$field] = get_the_post_thumbnail_url($post->ID);
                                break;
                            case 'price':
                                if ($type === 'product') {
                                    if (function_exists('wc_get_product')) {
                                        $product = wc_get_product($post->ID);
                                        $item[$field] = wc_price($product->get_price());
                                    }
                                }
                                break;
                            case 'taxonomy':
                                if ($post->post_type == 'product') {
                                    $taxonomy_terms = wp_get_post_terms($post->ID, 'product_cat');
                                    if ($taxonomy_terms && !is_wp_error($taxonomy_terms)) {
                                        $first_term = reset($taxonomy_terms);
                                        $item[$field] = $first_term->name;
                                    }
                                } else {
                                    $object_taxonomies = get_object_taxonomies($post->post_type);
                                    foreach ($object_taxonomies as $taxonomy_name) {
                                        $taxonomy_terms = get_the_terms($post->ID, $taxonomy_name);
                                        if ($taxonomy_terms && !is_wp_error($taxonomy_terms)) {
                                            $first_term = reset($taxonomy_terms);
                                            $item[$field] = $first_term->name;
                                            break;
                                        }
                                    }
                                }
                                break;
                        }
                    }
                    $newitem[$post->ID] = $item;
                    $upload_dir = wp_upload_dir();
                    $json_dir = $upload_dir['basedir'] . '/json';
                    if (!is_dir($json_dir)) {
                        mkdir($json_dir);
                    }
                    $file_path = $json_dir . '/data-search.json';
                    $existing_data = array();
                    if (file_exists($file_path)) {
                        $existing_data = json_decode(file_get_contents($file_path), true);
                    }
                    $merged_data = popuptb_merged_array($existing_data,$newitem);
                    file_put_contents($file_path, json_encode($merged_data));
                }
            }
        }
}
// Thêm hook cho từng loại post type
if(isset($popuptb_options['main-search-posttype'])){
	$main_search_post_types = $popuptb_options['main-search-posttype'];
	foreach ($main_search_post_types as $post_type) {
		$hook_name = 'publish_' . $post_type;
		add_action($hook_name, 'popuptb_add_search_auto_whenpublish');
	}
}
// lay name tu custom post type
function popuptb_post_type_name($post_type_slug) {
    $post_type_object = get_post_type_object($post_type_slug);
    if ($post_type_object) {
        $post_type_name = $post_type_object->labels->singular_name;
        return $post_type_name; 
    } 
}
// tao mang json
function popuptb_search($page = 1, $posts_per_page = 2000) {
    global $popuptb_options;
    if (isset($popuptb_options['main-search-posttype'])) {
        if(count($popuptb_options['main-search-posttype'])>0){
            foreach ($popuptb_options['main-search-posttype'] as $key => $type) {
                $post_types[$type] = array(
                        'type' => $type,
                        'fields' => array(
                            'ID',
                            'title',
                            'url',
                            'thum',
                            'price',
                            'taxonomy'
                        )
                );
            }
        }  
    } 
    $args = array(
        'numberposts' => $posts_per_page,
        'offset'      => ($page - 1) * $posts_per_page,
        'post_type'   => array_keys($post_types),
    );
    $posts = get_posts($args);
    $results = array();
    foreach ($posts as $post) {
        $post_type = $post->post_type;
        if (isset($post_types[$post_type])) {
            $type_info = $post_types[$post_type];
            $type = $type_info['type'];
            $item = array('type' => $type);
            foreach ($type_info['fields'] as $field) {
                switch ($field) {
                    case 'ID':
                        $item[$field] = $post->ID;
                        break;
                    case 'title':
                        $item[$field] = $post->post_title;
                        break;
                    case 'url':
                        $item[$field] = get_permalink($post->ID);
                        break;
                    case 'thum':
                        $item[$field] = get_the_post_thumbnail_url($post->ID);
                        break;
                    case 'price':
                        if ($type === 'product') {
                            if (function_exists('wc_get_product')) {
                                $product = wc_get_product($post->ID);
                                $item[$field] = wc_price($product->get_price());
                            }
                        }
                        break;
                    case 'taxonomy':
                        if ($post->post_type == 'product') {
                            $taxonomy_terms = wp_get_post_terms($post->ID, 'product_cat');
                            if ($taxonomy_terms && !is_wp_error($taxonomy_terms)) {
                                $first_term = reset($taxonomy_terms);
                                $item[$field] = $first_term->name;
                            }
                        } else {
                            $object_taxonomies = get_object_taxonomies($post->post_type);
                            foreach ($object_taxonomies as $taxonomy_name) {
                                $taxonomy_terms = get_the_terms($post->ID, $taxonomy_name);
                                if ($taxonomy_terms && !is_wp_error($taxonomy_terms)) {
                                    $first_term = reset($taxonomy_terms);
                                    $item[$field] = $first_term->name;
                                    break;
                                }
                            }
                        }
                        break;
                }
            }
            $results[$post->ID] = $item;
        }
    }
    return $results;
}
// ajax tao file json 
function popuptb_json_file_callback(){
	global $popuptb_options;
    check_ajax_referer('popuptb_nonce_key', 'security');
    $page = $_POST['page'];
    $data =  popuptb_search($page);
    if (empty($data)) {
        echo json_encode(array('page' => -1));
        wp_die();
    }
    $upload_dir = wp_upload_dir();
    $json_dir = $upload_dir['basedir'] . '/json';
    if (!is_dir($json_dir)) {
        mkdir($json_dir);
    }
    $file_path = $json_dir . '/data-search.json';
    $existing_data = array();
    if (file_exists($file_path)) {
        $existing_data = json_decode(file_get_contents($file_path), true);
    }
    // Xóa các custom post type không tồn tại trong main-search-posttype
    if (isset($popuptb_options['main-search-posttype']) && count($popuptb_options['main-search-posttype']) > 0) {
        $allowed_post_types = $popuptb_options['main-search-posttype'];
        foreach ($existing_data as $key => $item) {
            if (!in_array($item['type'], $allowed_post_types)) {
                unset($existing_data[$key]);
            }
        }
        $existing_data = array_values($existing_data);
    }
    $merged_data = popuptb_merged_array($existing_data, $data);
    file_put_contents($file_path, json_encode($merged_data));
    $count = count($merged_data);
    echo json_encode(array('page' =>$page+1,'count'=>$count));   
    wp_die();
}
add_action('wp_ajax_popuptb_json_file', 'popuptb_json_file_callback');
// ajax xoa thư mục json
function popuptb_delete_json_folder_callback() {
    check_ajax_referer('popuptb_json_nonce', 'security');
    if (!current_user_can('manage_options')){
        wp_die(__('Not enough permissions', 'popup-tb'));
    }
    $upload_dir = wp_upload_dir();
    $json_dir = $upload_dir['basedir'] . '/json';
    if (is_dir($json_dir)) {
        $files = glob("$json_dir/*");
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($json_dir);
    }
    wp_die();
}
add_action('wp_ajax_popuptb_json_folder', 'popuptb_delete_json_folder_callback');
// xu ly du lieu json
function popuptb_merged_array($existing_data, $data) {
    $merged_data = $existing_data;
    foreach ($data as $new_item) {
        $found = false;
        foreach ($merged_data as &$existing_item) {
            if ($existing_item['ID'] == $new_item['ID']) {
                $existing_item = $new_item;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $merged_data[] = $new_item;
        }
    }
    return array_values($merged_data);
}
// duong dan toi json trong plugin
function popuptb_search_url(){
    $upload_dir = wp_upload_dir();
    $json_dir = $upload_dir['basedir'] . '/json';
    $json_file = $json_dir . '/data-search.json';
    if (file_exists($json_file)) {
        $absolute_url = $upload_dir['baseurl'] . '/json/data-search.json';
        $relative_url = wp_make_link_relative($absolute_url);
        return $relative_url;
    } 
}
// dua vao website
function popuptb_search_footer(){ 
	global $popuptb_options;
	$limit = !empty($popuptb_options['main-search-c1']) ? $popuptb_options['main-search-c1'] : 10;
	?>
	<div class="ft-search" id="ft-search" style="display:none">
		<div class="ft-sbox">
			<span id="ft-sclose" onclick="ftnone(event, 'ft-search')">&#215;</span>
			<form class="ft-sform" action="<?php bloginfo('url'); ?>">
			<?php 
            if (in_array('product', $popuptb_options['main-search-posttype'])) {
				echo '<input type="hidden" name="post_type" value="product">';
			}
			?>
			<input type="text" id="ft-sinput" placeholder="<?php _e('Enter keywords to search', 'popup-tb'); ?>" name="s" value="" maxlength="50" required="required">
			<button id="ft-ssumit" type="submit"><?php _e('SEARCH', 'popup-tb'); ?></button>
			</form>
			<ul id="ft-show"></ul>
		</div>
	</div>
	<script>
	jQuery(document).ready(function($){
        $('input[name="s"]').on('input', function() {
			var searchText = $(this).val(); 
			$("#ft-search").css("display", "block"); 
			$('#ft-sinput').val(searchText); 
			$('#ft-sinput').trigger('keyup');
			if ($('.mfp-close').length > 0) {
			  $('.mfp-close').click();
			}
			$("#ft-sinput").focus();
		});
		$('#ft-sinput').on('input', function() {
			var searchText = $(this).val();
			$('input[name="s"]').val(searchText); 
			$(this).trigger('keyup');
		});
		var debounceTimer;
		$('#ft-sinput').on('keyup', function(){
			var searchText = $(this).val();
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(function() {
				if(searchText.length >= 1) {
					fetch('<?php echo popuptb_search_url(); ?>?search=' + searchText)
					.then(response => response.json())
					.then(data => {
						displayResults(data, searchText);
					})
					.catch(error => {
						console.error('Error fetching data:', error);
					});
				} else {
					$('#ft-show').empty(); 
					$('#ft-show').removeClass('ft-showbg');
				}
			}, 100); 
		});
		function removeDiacritics(str) {
			return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
		}
		function displayResults(data, searchText) {
			$('#ft-show').empty();
			var hasResults = false;
			<?php 
			if(isset($popuptb_options['main-search-posttype'])){
				$main_search_post_types = $popuptb_options['main-search-posttype'];
				foreach ($main_search_post_types as $id) {
					echo "var ". $id ."Results = '';var ". $id ."Count = 0;";
				}
			}
			?>
			var postLimit = <?php echo $limit; ?>; 
			if (data && data.length > 0) {
				$('#ft-show').addClass('ft-showbg');
				$.each(data, function (index, item) {
					var title = item.title;
					var normalizedTitle = removeDiacritics(title);
					var normalizedSearchText = removeDiacritics(searchText.toLowerCase());
					var regex = new RegExp(normalizedSearchText.replace(/\s+/g, '.*'), 'i');
					if (regex.test(normalizedTitle)) {
						var textmau = highlightSearchText(title, searchText);
						var type = item.type;
						var url = item.url;
						var thum = item.thum;
						var pri = item.price;
						var taxo = item.taxonomy;
						var itemHTML;
						if (!pri) {
							pri = "";
						}
						if (!taxo) {
							taxo = "";
						}
						if (thum) {
							itemHTML = '<li class="ft-ssp"><a href="' + url + '"><img src="' + thum + '"></a><a href="' + url + '"><span class="ft-ssap-tit">' + textmau + '</span><span class="ft-ssap-cm">'+ taxo +'</span><span class="ft-ssap-pri">' + pri + '</span></a></li>';
						} else {
							itemHTML = '<li class="ft-sspno"><a href="' + url + '"><span class="ft-ssap-tit">' + textmau + '</span><span class="ft-ssap-cm">'+ taxo +'</span><span class="ft-ssap-pri">' + pri + '</span></a></li>';
						}
						<?php 
						if(isset($popuptb_options['main-search-posttype'])){
							$main_search_post_types = $popuptb_options['main-search-posttype'];
							$firstCondition = true;
							foreach ($main_search_post_types as $id) {
								if($firstCondition) {
									echo "if (type === '". $id ."' && ". $id ."Count < postLimit) {
										". $id ."Results += itemHTML;
										". $id ."Count++;
										hasResults = true;
									}";
									$firstCondition = false;
								} else {
									echo "else if (type === '". $id ."' && ". $id ."Count < postLimit) {
										". $id ."Results += itemHTML;
										". $id ."Count++;
										hasResults = true;
									}";
								}
							}
						}
						?>
					}
				});
			}
			<?php 
			if(isset($popuptb_options['main-search-posttype'])){
				$main_search_post_types = $popuptb_options['main-search-posttype'];
				if (in_array('product', $main_search_post_types)) {
					unset($main_search_post_types[array_search('product', $main_search_post_types)]);
					array_unshift($main_search_post_types, 'product');
				}
				foreach ($main_search_post_types as $id) {
					echo 'if ('. $id .'Results){$(\'#ft-show\').append(\'<li class="ft-stit">'. popuptb_post_type_name($id) .'</li>\' + '. $id .'Results);}';
				}
			}
			?>
			if (!hasResults) {
				$('#ft-show').append('<li><?php _e("No results were found", "popup-tb"); ?></li>');
			}
		}
		function highlightSearchText(text, searchText){
			var regex = new RegExp(searchText.replace(/\s+/g, '|'), 'gi'); 
			return text.replace(regex, function(match){
				return '<span class="ft-sselec">' + match + '</span>';
			});
		}
	});
</script>
<?php	
}
add_action('wp_footer', 'popuptb_search_footer');
// add css js search web
function popuptb_enqueue_search(){
	wp_enqueue_style('popuptb-s', POPUPTB_URL . 'css/search.css', array(), POPUPTB_VER);
	wp_enqueue_script('popuptb-s', POPUPTB_URL . 'js/search.js', array(), POPUPTB_VER);
}
add_action('wp_enqueue_scripts', 'popuptb_enqueue_search');
}
