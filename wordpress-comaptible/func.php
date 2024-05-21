<?php
require_once (  __DIR__ . '/scrapper.php');

function is_scrapper_started($getLastIndex = false){
    $args = [
        'post_type'      => 'scrapper-history',   
        'posts_per_page' => 1,       
        'orderby'        => 'date',   
        'order'          => 'DESC',  
    ];

    $isStarted = false;
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $temp = get_post_meta(get_the_ID(), 'has_started', true) ;
            if($temp === "Has Started"){
                $isStarted = true;
                
                if($getLastIndex){
                    $isStarted = get_post_meta(get_the_ID(), 'last_updated_index', true) ;
                }
            }
            else{
                $isStarted = false;
            }

            wp_reset_postdata();
        }
    } else {
        $isStarted = false;
    }
    return $isStarted;
}




function scrapper_cron_controller(){
    if(isset($_REQUEST['scrape'])){
        $reffer = wp_get_referer();
        if(!is_scrapper_started()){
            $post_id = wp_insert_post([
                'post_title'    => date("l jS \of F Y h:i:s A") . ' - Started',
                'post_status'   => 'publish',
                'post_type' => 'scrapper-history'
            ]);
            if($post_id && !is_wp_error($post_id)){       
                update_post_meta($post_id, 'has_started', 'Has Started'); 
                update_post_meta($post_id, 'last_updated_index', 'Just Started');              
               
                header("Location: {$reffer}&showMsg=success");
            }
            else{
                header("Location: {$reffer}&showMsg=error");
            }
        }else{            
            header("Location: {$reffer}&showMsg=warning");
        }
        
    }
}

add_action('init', 'scrapper_cron_controller');



function scrapper_notice__success() {
?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Scrapper Started!', 'sample-text-domain' ); ?></p>
    </div>
<?php
}

function scrapper_notice__warning() {
?>
    <div class="notice notice-warning is-dismissible">
        <p><?php _e( 'Scrapper Already Started!', 'sample-text-domain' ); ?></p>
    </div>
<?php
}


function scrapper_notice__error() {
?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'Something Went Wrong!', 'sample-text-domain' ); ?></p>
    </div>
<?php
}


function create_posttype() {
  
    register_post_type( 'scrapper-history',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'Scrapper History' ),
                'singular_name' => __( 'Scrapper History' )
            ),
            'public' => true,
            'has_archive' => false,
            'show_in_rest' => false,
        )
    );
}
// Hooking up our function to theme setup
add_action( 'init', 'create_posttype' );

add_action('admin_menu', 'scrapper_add_settings_page');
function scrapper_add_settings_page() {
    add_menu_page(
        'Scrapper Settings',
        'Scrapper Setting',
        'manage_options',
        'scrapper-settings',
        'scrapper_settings_page_content',
        'dashicons-admin-generic',
        20
    );
}


function scrapper_settings_page_content() {
    ?>
    <div class="wrap">
        <h2>Scrapper Settings</h2>
        <?php if(isset($_GET['showMsg']) && $_GET['showMsg'] === 'success') scrapper_notice__success(); ?>
        <?php if(isset($_GET['showMsg']) && $_GET['showMsg'] === 'warning' ) scrapper_notice__warning(); ?>
        <?php if(isset($_GET['showMsg']) && $_GET['showMsg'] === 'error' ) scrapper_notice__error(); ?>
        <form action="" method="post">
            <button  type="submit" <?= is_scrapper_started() ? 'disabled="disabled"' : '' ?> class="button button-primary" name="scrape" value="1" style="margin-top: 15px;">Scrape Pages</button>
        </form>
        <?php
        
        require_once (__DIR__ . '/templates.php');
        $pages = getAllPages();
//          echo '<h2>Templates</h2>';
        foreach($pages['templates'] as $t => $page){
            // if(!isset($templates[$t])){
			echo "<p " . (!isset($templates[$t]) ? 'style="color: red"' : 'style="color: green"') . ">Template: {$t} </p>";
            // echo '<hr/>';
		  // }
        }
//         echo '<h2>Pages Ready for Scrape '. count($pages['ready_for_scrape']) .'</h2>';
//         foreach($pages['ready_for_scrape'] as $page){
//             echo "<p>Title: {$page['title']} </p>";
//             echo "<p>ID: {$page['wp_pageid']} </p>";
//             echo "<p>Ref: {$page['scrap_from']} </p>";
//             echo "<p>Template: {$page['template']} </p>";
//             echo '<hr/>';
//         }
        // echo '<h2>Pages not have template assigned '. count($pages['not_assigned_templates']).'</h2>';
        // foreach($pages['not_assigned_templates'] as $page){
        //     echo "<p>Title: {$page['title']} </p>";
        //     echo "<p>ID: {$page['wp_pageid']} </p>";
        //     echo "<p>Ref: {$page['scrap_from']} </p>";
        //     echo '<hr/>';
        // }
        // echo '<h2>Pages not have scrapper link '. count($pages['not_have_scrapper_link']).'</h2>';
        // foreach($pages['not_have_scrapper_link'] as $page){
        //     echo "<p>Title: {$page['title']} </p>";
        //     echo "<p>ID: {$page['wp_pageid']} </p>";
        //     echo "<p>Template: {$page['template']} </p>";
        //     echo '<hr/>';
        // }
        
        
        ?>
    </div>

    <?php
}

function deleteRows( $acfRepeaterFieldKey,  $postID ) {
  reset_rows();
  $fieldValue = get_field($acfRepeaterFieldKey, $postID);
  if (is_array($fieldValue)){
    $remainingRows = count($fieldValue);
    while (have_rows($acfRepeaterFieldKey, $postID)) :
      the_row();
      delete_row($acfRepeaterFieldKey, $remainingRows--, $postID);
    endwhile;
  }
}

function scrpper_on_cron() {
    $lastIndex = is_scrapper_started(true);
    $nextIndex = 0 ;
    if($lastIndex !== false){
        if($lastIndex === 'Just Started')
            $nextIndex = 0;
        else
            $nextIndex = intval($lastIndex) + 1;

        $data = scrapePages($nextIndex);
		$pages = $data[0];
		$allPages = $data[1];
        if(isset($pages[$nextIndex])){
            $page = $pages[$nextIndex];
			if(isset($page['slug'])){
                global $wpdb;
                $wpdb->update(
                    $wpdb->posts,
                    array('post_name' => $page['slug']),
                    array('ID' => $page['wp_pageid']),
                    array('%s'),
                    array('%d')
                );
                update_post_meta( $page['wp_pageid'], 'page_slug', $page['slug']);
            }
            foreach($page['content_structure'] as $metaKey => $metaValue){
                if(!is_array($metaValue))
                    update_post_meta($page['wp_pageid'], $metaKey, $metaValue);
                else{
                    deleteRows($metaKey, $page['wp_pageid']);
                    update_field($metaKey, $metaValue, $page['wp_pageid']);
                }
            }


            $post_id = wp_insert_post([
                'post_title'    => date("l jS \of F Y h:i:s A") . ' - Progress',
                'post_status'   => 'publish',
                'post_type' => 'scrapper-history',
				'post_content' => json_encode($page),
            ]);
            if($post_id && !is_wp_error($post_id)){
                update_post_meta($post_id, 'has_started', 'Has Started');                              
                update_post_meta($post_id, 'last_updated_index', $nextIndex );                              
                update_post_meta($post_id, 'page_id', $page['wp_pageid'] );                              
                update_post_meta($post_id, 'page_scrape_from', $page['scrap_from'] );     
                update_post_meta($post_id, 'page_title', $page['title'] );     
            }
        }
        if(!isset($allPages[$nextIndex + 1])){            
            $post_id = wp_insert_post([
                'post_title'    => date("l jS \of F Y h:i:s A") . ' - Finished',
                'post_status'   => 'publish',
                'post_type' => 'scrapper-history'
            ]);
            if($post_id && !is_wp_error($post_id)){
                update_post_meta($post_id, 'has_started', 'Has Finished');                                      
            }
         }       
    }
}

function scrpper_intervals($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display' => __('Every Minute')
    );
    return $schedules;
}
add_filter('cron_schedules', 'scrpper_intervals');


function schedule_scrapper() {
   
    if (!wp_next_scheduled('scrapper_hook')) {
        wp_schedule_event(time(), 'every_minute', 'scrapper_hook');
    }
}
add_action('wp', 'schedule_scrapper');

add_action('scrapper_hook', 'scrpper_on_cron');


function update_slug_from_meta_after_save($post_id, $post, $update) {
    // Only modify post data for pages and only on update
    if ($post->post_type == 'page' && $update) {
        // Ensure this is not an autosave or a revision
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        // Get the slug from the custom meta field
        $custom_slug = get_post_meta($post_id, 'page_slug', true);

        if (!empty($custom_slug)) {
            global $wpdb;

            // Update the post_name directly in the database
            $wpdb->update(
                $wpdb->posts,
                array('post_name' => $custom_slug),
                array('ID' => $post_id),
                array('%s'),
                array('%d')
            );

            // Optionally clear the cache for the post
            clean_post_cache($post_id);
        }
    }
}

add_action('save_post', 'update_slug_from_meta_after_save', 10, 3);






remove_filter( 'sanitize_title', 'sanitize_title_with_dashes' );
add_filter( 'sanitize_title', 'wpse5029_sanitize_title_with_dashes' );


function wpse5029_sanitize_title_with_dashes($title) {
    $title = strip_tags($title);
    
    // Preserve escaped octets.
    $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
    
    // Remove percent signs that are not part of an octet.
    $title = str_replace('%', '', $title);
    
    // Restore octets.
    $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

    $title = remove_accents($title);
    
    if (seems_utf8($title)) {
        $title = utf8_uri_encode($title, 200);
    }

    // Remove HTML entities.
    $title = preg_replace('/&.+?;/', '', $title);

    // Allow specific characters: , . ( ) and underscore _
    $title = preg_replace('/[^%a-zA-Z0-9 _,\.\(\)-]/', '', $title);
    
    // Replace spaces with hyphens.
    $title = preg_replace('/\s+/', '-', $title);
    
    // Collapse multiple hyphens into one.
    $title = preg_replace('|-+|', '-', $title);
    
    // Trim leading and trailing hyphens.
    $title = trim($title, '-');

    return $title;
}