<?php
/**
 * THIS FILE CONTAINS HOOKS, FILTERS AND OTHER FUNCTIONS
 * THAT WILL ENHANCE WORDPRESS ADMIN PANEL FEATURES AS PER OUR REQUIREMENTS
 * */

// CODE TO REGISTER OUR OWN CUSTOM POST TYPE (COMPETITIONS IS CUSTOM POST TYPE)
add_action('init', 'create_competitions');
function create_competitions() {
    register_post_type('competitions', array(
        'labels' => array(
            'name' => _x( 'Competitions','post type general name','cswm' ),
            'singular_name' => _x( 'Competitions','post type singular name','cswm' ),
            'add_new' => _x( 'Add New', 'competitions', 'cswm' ),
            'add_new_item' => __( 'Add New Competition', 'cswm' ),
            'edit' => __( 'Edit', 'cswm' ),
            'edit_item' => __( 'Edit Competition', 'cswm' ),
            'new_item' => __( 'New Competition', 'cswm' ),
            'view' => __( 'View', 'cswm' ),
            'view_item' => __( 'View Competition', 'cswm' ),
            'search_items' => __( 'Search Competitions', 'cswm' ),
            'not_found' => __( 'No Competitions found', 'cswm' ),
            'not_found_in_trash' => __( 'No Competitions found in Trash', 'cswm' ),
            'parent' => __( 'Parent Competition', 'cswm' )
        ),
        'query_var' => true,
        'public' => true,
        'menu_position' => 15,
        'supports' => array('title', 'editor', 'custom-fields'),
        'taxonomies' => array(''),
        'hierarchical' => true,
        'has_archive' => false
        )
    );
}

/* * ********************************************************************************
 * CODE TO REMOVE CUSTOM POST TYPE SLUG FROM URL FOR COMPETITIONS POST TYPE STARTS
 * ********************************************************************************* */
function creole_remove_competitions_slug($post_link, $post, $leavename) {
    if ('competitions' != $post->post_type || 'publish' != $post->post_status) {
        return $post_link;
    }

    $post_link = str_replace('/' . $post->post_type . '/', '/', $post_link);
    return $post_link;
}

add_filter('post_type_link', 'creole_remove_competitions_slug', 10, 3);
function creole_parse_request_trick($query) {
    if (!$query->is_main_query())
        return;
    if (2 != count($query->query) || !isset($query->query['page']))
        return;
    if (!empty($query->query['name']))
        $query->set('post_type', array('post', 'page', 'competitions', 'artists'));
}
add_action('pre_get_posts', 'creole_parse_request_trick');
/* * ********************************************************************************
 * CODE TO REMOVE CUSTOM POST TYPE SLUG FROM URL FOR COMPETITIONS POST TYPE ENDS
 * ********************************************************************************* */

/* * ***********************************************************************
 * CODE TO ADD CUSTOM COLUMN TO MANAGE COMPETITIONS AT ADMIN PANEL STARTS
 * *********************************************************************** */
// ADD NEW TEMPLATE COLUMN TO MANAGE COMPETITIONS AT ADMIN PANEL
function template_column_head($defaults) {
    $defaults['page_template'] = __( 'Template', 'cswm' );
    $defaults['total_entries'] = __( 'Total Entries', 'cswm' );
    $defaults['starting_date'] = __( 'Start Date', 'cswm' );
    $defaults['ending_date'] = __( 'End Date', 'cswm' );
    return $defaults;
}
add_filter('manage_competitions_posts_columns', 'template_column_head');

// SHOW THE TEMPLATE NAME COLUMN
function manage_competitions_columns_content($column_name, $post_ID) {
    if ($column_name == 'page_template') {
        $current_template_file = get_page_template_slug($post_ID);
        $all_registered_templates = get_page_templates(get_post($post_ID), 'competitions');

        if (sizeof($all_registered_templates)) {
            $cnt = 0;
            foreach ($all_registered_templates as $template_nm => $template_file) {
                if ($current_template_file == $template_file) {
                    echo $template_nm;
                    $cnt++;
                    break;
                }
            }
            if ($cnt == 0)
                _e('Default Template','cswm');
        }
        else {
            _e('Default Template','cswm');
        }
    } elseif ($column_name == 'total_entries') {
        echo sprintf('<a href="%s" title="%s">%d</a>', admin_url("admin.php?page=manage-competition-entries&competition_id=$post_ID"),__('view entries', 'cswm'), get_entry_count_by_competition($post_ID));
    } elseif ($column_name == 'starting_date') {
        $competition_start_date = get_post_meta($post_ID, 'competition_start_date');
        echo $competition_start_date[0];
    } elseif ($column_name == 'ending_date') {
        $competition_start_date = get_post_meta($post_ID, 'competition_end_date');
        echo $competition_start_date[0];
    }
}
add_action('manage_competitions_posts_custom_column', 'manage_competitions_columns_content', 10, 2);
/* * *********************************************************************
 * CODE TO ADD CUSTOM COLUMN TO MANAGE COMPETITIONS AT ADMIN PANEL ENDS
 * ********************************************************************* */

/* * *******************************************************************************
 * FILTERS TO REMOVE MANAGE COMPETITIONS TABLE COLUMNS AT ADMIN PANEL STARTS HERE
 * ****************************************************************************** */
function admin_manage_competitions_columns($columns) {
    unset($columns['comments']);
    unset($columns['date']);
    unset($columns['author']);
    return $columns;
}
function admin_competitions_column_init() {
    add_filter('manage_competitions_posts_columns', 'admin_manage_competitions_columns');
}

add_action('admin_init', 'admin_competitions_column_init');
/* * ****************************************************************************
 * FILTERS TO REMOVE MANAGE COMPETITIONS TABLE COLUMNS AT ADMIN PANEL ENDS HERE
 * **************************************************************************** */

/* * **************************************************************************
 * FILTERS TO REMOVE DATE FILTER AT ADMIN PANEL WHEN ALL COMPETITIONS LISTED
 * ************************************************************************** */
function remove_date_drop_down() {
    $screen = get_current_screen();
    if ('competitions' == $screen->post_type) {
        add_filter('months_dropdown_results', '__return_empty_array');
    } elseif ('artists' == $screen->post_type) {
        add_filter('months_dropdown_results', '__return_empty_array');
    } elseif ('track' == $screen->post_type) {
        add_filter('months_dropdown_results', '__return_empty_array');
    }
}
add_action('admin_head', 'remove_date_drop_down');
/* * ************************************************************************
 * FILTERS TO REMOVE DATE FILTER AT ADMIN PANEL WHEN ALL COMPETITIONS LISTED
 * ************************************************************************ */


/* * *********************************************************************
 * ADD EXTRA QUICK LINK ABOVE COMPETITIONS LISTING AT ADMIN PANEL STARTS
 * ********************************************************************** */
add_filter('views_edit-competitions', 'add_admin_competitions_quick_link');
function add_admin_competitions_quick_link($views) {
    if (( is_admin() ) && ( $_GET['post_type'] == 'competitions' )) {
        global $wp_query;

        // ACTIVE COMPETITIONS QUICK LINK
        global $wp_query;
        $active_competition_args = array('post_type' => 'competitions',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'competition_end_date',
                    'value' => date('d-m-Y'),
                    'compare' => '>=',
                ),
            )
        );

        $active_competition_results = new WP_Query($active_competition_args);
        $count_active_competitions = 0;
        $count_active_competitions = $active_competition_results->found_posts;

        $active_link_class = false;
        if (isset($_GET['active_competitions']) && 'true' === $_GET['active_competitions']) {
            $active_link_class = 'current';
        }

        $views['active_competitions'] = sprintf('<a href="%s" class="%s">%s<span class="count">(%d)</span></a>', admin_url("edit.php?post_type=competitions&active_competitions=true"), $active_link_class, __('Active Competitions ', 'cswm'), $count_active_competitions);

        // PAST COMPETITIONS QUICK LINK
        global $wp_query;
        $past_competition_args = array('post_type' => 'competitions',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'competition_end_date',
                    'value' => date('d-m-Y'),
                    'compare' => '<',
                ),
            )
        );

        $past_competition_results = new WP_Query($past_competition_args);
        $count_past_competitions = 0;
        $count_past_competitions = $past_competition_results->found_posts;

        $past_link_class = false;
        if (isset($_GET['past_competitions']) && 'true' === $_GET['past_competitions']) {
            $past_link_class = 'current';
        }

        $views['past_competitions'] = sprintf('<a href="%s" class="%s">%s<span class="count">(%d)</span></a>', admin_url("edit.php?post_type=competitions&past_competitions=true"), $past_link_class, __('Past Competitions ', 'cswm'), $count_past_competitions);

        // WINNERS PENDING QUICK LINK
        $args = array('post_type' => 'competitions',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'winners_pending',
                    'value' => 0,
                    'compare' => '=',
                ),
            )
        );
        $result = new WP_Query($args);
        $count = 0;
        $count = $result->found_posts;

        $link_class = false;
        if (isset($_GET['winners_pending']) && 'true' === $_GET['winners_pending']) {
            $link_class = 'current';
        }

        $views['winners_pending'] = sprintf('<a href="%s" class="%s">%s<span class="count">(%d)</span></a>', admin_url("edit.php?post_type=competitions&winners_pending=true"), $link_class, __('Winners Pending ', 'cswm'), $count);

        return $views;
    }
}
function register_quick_link_params() {
    global $wp;
    $wp->add_query_var('active_competitions');
    $wp->add_query_var('past_competitions');
    $wp->add_query_var('winners_pending');
}
add_action('init', 'register_quick_link_params');
function custom_competitions_filter($wp_query) {
    if ($meta_value = $wp_query->get('active_competitions')) {
        $wp_query->set('post_status', 'publish');
        $wp_query->set('meta_key', 'competition_end_date');
        $wp_query->set('meta_value', date('d-m-Y'));
        $wp_query->set('meta_compare', '>=');
    }
    if ($meta_value = $wp_query->get('past_competitions')) {
        $wp_query->set('post_status', 'publish');
        $wp_query->set('meta_key', 'competition_end_date');
        $wp_query->set('meta_value', date('d-m-Y'));
        $wp_query->set('meta_compare', '<');
    }
    if ($meta_value = $wp_query->get('winners_pending')) {
        $wp_query->set('post_status', 'publish');
        $wp_query->set('meta_key', 'winners_pending');
        $wp_query->set('meta_value', 0);
    }
}
add_action('parse_query', 'custom_competitions_filter');
/* * *******************************************************************
 * ADD EXTRA QUICK LINK ABOVE COMPETITIONS LISTING AT ADMIN PANEL ENDS
 * ******************************************************************** */

/***********************************************************************************
 CODE TO ADD CUSTOM RULE FOR COMPETITIONS TMEPLATE FIELDS GROUP(ADVANCED CUSTOM FIELDS) STARTS
 ************************************************************************************/
add_filter('acf/location/rule_types', 'acf_location_rules_types');
function acf_location_rules_types($choices) {
    $choices['Basic']['competition_template'] = __('Competition Template','cswm');
    return $choices;
}
add_filter('acf/location/rule_values/competition_template', 'acf_location_rules_values_template');
function acf_location_rules_values_template($choices) {
    $all_registered_templates = get_page_templates(null, 'competitions');
    if ($all_registered_templates) {
        foreach ($all_registered_templates as $template_nm => $template_file) {
            $choices[$template_file] = $template_nm;
        }
    }
    return $choices;
}
add_filter('acf/location/rule_match/competition_template', 'acf_location_rules_competition_tmeplate', 10, 3);
function acf_location_rules_competition_tmeplate($match, $rule, $options) {
    $page_template = $options['page_template'];
    if (!$page_template) {
        $page_template = get_post_meta($options['post_id'], '_wp_page_template', true);
    }

    if (!$page_template) {
        $post_type = $options['post_type'];

        if (!$post_type) {
            $post_type = get_post_type($options['post_id']);
        }

        if ($post_type == 'page') {
            $page_template = "default";
        }
    }

    if ($rule['operator'] == "==") {
        $match = ( $page_template === $rule['value'] );
    } elseif ($rule['operator'] == "!=") {
        $match = ( $page_template !== $rule['value'] );
    }

    return $match;
}
/**********************************************************************************
 CODE TO ADD CUSTOM RULE FOR COMPETITIONS TMEPLATE FIELDS GROUP(ADVANCED CUSTOM FIELDS) ENDS
 **********************************************************************************/

//@auther : Hardika Satasiya
// CODE TO REGISTER OUR OWN CUSTOM POST TYPE (ARTISTS IS CUSTOM POST TYPE)
add_action('init', 'create_artists');
function create_artists() {
    register_post_type('artists', array(
        'labels' => array(
            'name' => _x( 'Artists', 'post type general name', 'cswm' ),
            'singular_name' => _x( 'Artist', 'post type singular name', 'cswm' ),
            'add_new' => _x( 'Add New', 'Artist', 'cswm' ),
            'add_new_item' => __('Add New Artist','cswm'),
            'edit' => __('Edit','cswm'),
            'edit_item' => __('Edit Artist','cswm'),
            'new_item' => __('New Artist','cswm'),
            'view' => __('View','cswm'),
            'view_item' => __('View Artist','cswm'),
            'search_items' => __('Search Artist','cswm'),
            'not_found' => __('No Artists found','cswm'),
            'not_found_in_trash' => __('No Artists found in Trash','cswm'),
        ),
        'query_var' => true,
        'public' => true,
        'menu_position' => 16,
        'supports' => array('title'),
        'taxonomies' => array(''),
        'hierarchical' => false,
        'has_archive' => false
        )
    );
}
// CODE TO REMOVE ARTIST SLUG FROM ARTISTS CUSTOM POST TYPE
function creole_remove_artists_slug($post_link, $post, $leavename) {
    if ('artists' != $post->post_type || 'publish' != $post->post_status) {
        return $post_link;
    }

    $post_link = str_replace('/' . $post->post_type . '/', '/', $post_link);
    return $post_link;
}
add_filter('post_type_link', 'creole_remove_artists_slug', 10, 3);
// CODE TO REGISTER OUR OWN CUSTOM POST TYPE (TRACK IS CUSTOM POST TYPE)
add_action('init', 'create_track');
function create_track() {
    register_post_type('track', array(
        'labels' => array(
            'name' => _x( 'Tracks', 'post type general name', 'cswm' ),
            'singular_name' => _x( 'Track', 'post type singular name', 'cswm' ),
            'add_new' => _x( 'Add New', 'Track', 'cswm' ),
            'add_new_item' => __('Add New Track','cswm'),
            'edit' => __('Edit','cswm'),
            'edit_item' => __('Edit Track','cswm'),
            'new_item' => __('New Track','cswm'),
            'view' => __('View','cswm'),
            'view_item' => __('View Track','cswm'),
            'search_items' => __('Search Track','cswm'),
            'not_found' => __('No Artists found','cswm'),
            'not_found_in_trash' => __('No Tracks found in Trash','cswm')
        ),
        'query_var' => true,
        'public' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'show_ui' => true,
        'menu_position' => 17,
        'show_in_nav_menus' => false,
        'supports' => array('title'),
        'hierarchical' => false,
        'has_archive' => false,
        'rewrite' => false,
            )
    );
}
// END TO REGISTER OUR OWN CUSTOM POST TYPE (TRACK IS CUSTOM POST TYPE)

/**********************************************************************
CODE TO CREATE CUSTOM COLUMNS ON LISTING PAGE OF COPETITION POST TYPE 
START FROM HERE
***********************************************************************/
function add_acf_columns($columns) {
    unset($columns['date']);
    return array_merge($columns, array(
        'total_tracks' => __('Tracks','cswm'),
        'total_comptitions' => __('Competitions','cswm'),
        'average_entries' => __('Average Entries','cswm')
    ));
}
add_filter('manage_artists_posts_columns', 'add_acf_columns');

############################################################################
#CODE TO FETCH COUNTS IN CUSTOMIZED COLUMN ON LISTING PAGE OF COMPETITION POSTS
#############################################################################

function artists_custom_column($column, $post_id) 
{
    switch ($column){
        case 'total_tracks':
                                $args = array(
                                    'post_type' => 'track',
                                    'meta_query' => array(
                                        array(
                                            'key' => 'add_artist',
                                            'value' => $post_id,
                                            'compare' => '='
                                        )
                                    ),
                                    'fields' => 'ids'
                                );
                                $track_occur = new WP_Query($args);
                                $artist_id = $track_occur->posts;
                                echo count($artist_id);
                            break;
        case 'total_comptitions':
                                $args = array(
                                    'post_type' => 'competitions',
                                    'meta_query' => array(
                                        array(
                                            'key' => 'artist_name',
                                            'value' => $post_id,
                                            'compare' => '='
                                        )
                                    ),
                                    'fields' => 'ids'
                                );
                                $competition_occur = new WP_Query($args);
                                $artist_id = $competition_occur->posts;
                                echo count($artist_id);
                            break;
        case 'average_entries':
                                // FETCH ALL COMPETITION OF ARTIST
                                $args = array(
                                    'post_type' => 'competitions',
                                    'meta_query' => array(
                                        array(
                                            'key' => 'artist_name',
                                            'value' => $post_id,
                                            'compare' => '='
                                        )
                                    ),
                                    'fields' => 'ids'
                                );
                                $competitions = new WP_Query($args);
                                
                                if ( $competitions->have_posts() ) 
                                {
                                    $competition_count = 0;
                                    $total_entries_count = 0;

                                    while ( $competitions->have_posts() ) 
                                    {
                                        $competitions->the_post();
                                        $competition_id = get_the_ID();
                                        $total_entries_count += get_entry_count_by_competition($competition_id);
                                        $competition_count++;
                                    }
                                    wp_reset_postdata();
                                    if($total_entries_count>0)
                                        echo $total_entries_count/$competition_count;
                                    else
                                        echo ' - ';
                                }
                                else
                                {
                                    echo ' - ';
                                }
                                
                            break;
            }
}
add_action('manage_artists_posts_custom_column', 'artists_custom_column', 10, 2);

############################################################################
#END CODE HERE TO CREATE CUSTOM COLUMNS WITH COUNTS ON LISTING PAGE OF COPETITION POST TYPE--------- END HERE
#############################################################################
############################################################################
# CODE TO FETCH COUNTS IN CUSTOMIZED COLUMN ON LISTING PAGE OF COMPETITION POSTS
#############################################################################
add_action('admin_init', 'artist_track_library');
function artist_track_library() {
    if (isset($_GET['action']) && $_GET['action'] === 'edit') 
    {
        add_meta_box('artists_meta_box', __( 'Library', 'cswm' ), 'display_artists_meta_box', 'artists', 'normal', 'high');
    }
}
############################################################################
# DISPLAY META BOX ON THE ARTIST PAGE TO SHOW THE LIBRARY
#############################################################################

function display_artists_meta_box($post) 
{
    global $wp_query;
    //TO FETCH LISTS OF TRACKS AS PER THE ARTISTS TRACK//
    $active_competition_args = array('post_type' => 'track',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'add_artist',
                'value' => $post->ID,
                'compare' => '=',
            ),
        )
    );

    $active_competition_results = query_posts($active_competition_args);
    if ($active_competition_results) 
    {
        echo '<table width="100%">';
        echo '<th width="50%">'.__('Title Of Track','cswm').'</th>';
        echo '<th width="30%">'.__('Added','cswm').'</th></tr>';

        foreach ($active_competition_results as $wk_posts) 
        {
            $field_name_one = $wk_posts->post_title;
            $field_name_two = $wk_posts->post_modified_gmt;

             echo '<td width="50%">'.sprintf('<a href="%s">%s</a>', admin_url("post.php?post=$wk_posts->ID&action=edit"),$wk_posts->post_title).'</td>';
            echo '<td width="30%">' . date('d-F-Y', strtotime($wk_posts->post_modified_gmt)) . '</td></tr>';
            echo '</tr>';
        }
        echo '</table>';
    }
    wp_reset_query();
}


############################################################################
# DISPLAY META BOX ON THE ARTIST PAGE TO SHOW THE LIBRARY
#############################################################################
add_action('save_post', 'add_artists_fields', 10, 2);
function add_artists_fields($post_id, $post)
{
    if ($post->post_type == 'artists') 
    {
        if (isset($_POST['meta'])) 
        {
            foreach ($_POST['meta'] as $key => $value) 
            {
                update_post_meta($post_id, $key, $value);
            }
        }
    }
}

/***************************************************************************
* DISPLAY META BOX ON COMPETITION PAGE TO SELECT COUNTRY FOR FACEBOOK LOGIN
****************************************************************************/
add_action('admin_init', 'competition_facebook_metabox');
function competition_facebook_metabox()
{
    add_meta_box('facebook_login_meta_box', __('Facebook Login','cswm'), 'display_competition_login_meta_box', 'competitions', 'normal', 'high');
}

/* RENDERING METABOX WITH OPTIONS STARTS HERE */
function display_competition_login_meta_box($post) {
    $selected_country = get_post_meta($post->ID, 'facebook_login_table_id');
    global $wpdb;
    $fb_tablenm = $wpdb->prefix . 'facebook_credentials';
    $sql = 'SELECT * FROM '.$fb_tablenm;
    $fb_apps = $wpdb->get_results($sql);
    
    echo '<select name="facebook_id" id="facebook_id" class="">';
    //if (empty($selected_country))
        echo '<option value="">'.__('SELECT COUNTRY','cswm').'</option>';

    if($fb_apps)
    {
        foreach ($fb_apps as $fbObj) 
        {
            $selected = '';
            if (!empty($selected_country))
            {   
                if($selected_country[0]==$fbObj->id){
                    $selected = ' selected';
                }
            }
            echo '<option value="'.$fbObj->id.'"'.$selected.'>'. $fbObj->country_nm.'</option>';
        }
    }
    echo '</select>';
}
/* RENDERING METABOX WITH OPTIONS ENDS HERE */

## CODE TO SAVE METABOX DATA IN COMPITTION PAGE
function add_competitions_fields($post_id, $post) {
    if ($post->post_type == 'competitions')
    {
        if (isset($_POST['facebook_id']) && $_POST['facebook_id'] != "")
        {
            if (!empty(get_post_meta($post_id, 'facebook_login_table_id')))
            {
                update_post_meta($post_id, 'facebook_login_table_id', $_POST['facebook_id']);
            }
            else
            {
                add_post_meta($post_id, 'facebook_login_table_id', $_POST['facebook_id'], $unique = false);
            }
        }
    }
}
add_action('save_post', 'add_competitions_fields', 99, 2);

## CODE TO ADD JQUERY VALIDATION TO METABOX FIELDS IN COMPETITIONS PAGE.
add_action('admin_footer', 'cpd_validator', 999);
function cpd_validator() 
{
    global $post;
    if (($post->post_type == 'entries' || $post->post_type == 'competitions') || (isset($_GET['page']) && $_GET['page'] == 'csv-pass-management'))
    {
        wp_enqueue_script('form_validation', get_stylesheet_directory_uri() . '/assets/js/jquery.validate.min.js');
        wp_register_script('validate_js', get_stylesheet_directory_uri() . '/assets/js/common_admin.js',array('jquery'));
        $translation_array = array(
            'password_required_msg' => __(' Please provide old password.','cswm'),
            'csv_new_pass_required' => __(' Please provide new password.','cswm'),
            'confirm_pass_required' => __(' Please confirm password.','cswm'),
            'password_minlength_msg' => __(' Password should have minimum length of 8 characters.','cswm'),
            'confirm_match_msg' => __(' Confirm password must match the new password.','cswm'),
        );
        wp_localize_script( 'validate_js', 'objectL10n', $translation_array );

        // Enqueued script with localized data.
        wp_enqueue_script( 'validate_js' );
    }
}
/*************************************************************************************
 * FILTER TO ADD QUICK LINKS ABOVE ENTRIES LISTING AT ADMIN PANEL USING OUR OWN CODE STARTS
 ************************************************************************************/
add_filter('views_toplevel_page_manage-competition-entries','entry_winners_quick_link',10, 1);
function entry_winners_quick_link($views) {
    
    if ((is_admin()) && ($_GET['competition_id']!= '')) 
    {
        /* WINNERS COUNTING */
        $args = array(
                        'post_type' => 'entries',
                        'post_status' => 'publish',
                        'meta_query' => array(
                                                'relation' => 'AND',
                                                array(
                                                    'key' => 'competition',
                                                    'value' => $_REQUEST['competition_id'],
                                                    'compare' => '='
                                                ),
                                                array(
                                                    'key' => 'winner',
                                                    'value' => 1,
                                                    'compare' => '='
                                                )
                                            ),
                     );
        
        $winner_results = new WP_Query($args);
        $winner_count = 0;
        $winner_count = $winner_results->found_posts;
        wp_reset_postdata();
        /* WINNERS COUNTING */

        /* TRASH COUNTING */
        $trashargs = array(
                        'post_type' => 'entries',
                        'post_status' => 'trash',
                        'meta_query' => array(
                                                'relation' => 'AND',
                                                array(
                                                    'key' => 'competition',
                                                    'value' => $_REQUEST['competition_id'],
                                                    'compare' => '='
                                                )
                                            ),
                     );
        
        $trash_results = new WP_Query($trashargs);
        $trash_count = 0;
        $trash_count = $trash_results->found_posts;
        wp_reset_postdata();
        /* TRASH COUNTING */
        
        $active_link_class = false;
        if (isset($_GET['winners']) && 'true' === $_GET['winners']) {
            $active_link_class = 'current';
        }        
        $views['winners'] = sprintf('<a href="%s" class="%s">%s<span class="count">(%d)</span></a>',admin_url( "admin.php?page=".$_GET["page"]."&competition_id=".$_GET["competition_id"]."&winners=true" ), $active_link_class, __(' Winners ', 'cswm'), $winner_count);
        

        if (isset($_GET['trash']) && 'true' === $_GET['trash']) {
            $active_trash_link_class = 'current';
        }
        $views['trash'] = sprintf('<a href="%s" class="%s">%s<span class="count">(%d)</span></a>',admin_url( "admin.php?page=".$_GET["page"]."&competition_id=".$_GET["competition_id"]."&trash=true" ), $active_trash_link_class, __(' Trash ', 'cswm'), $trash_count);
    }
    return $views;
}
/****************************************************************************************
 * FILTER TO ADD QUICK LINKS ABOVE ENTRIES LISTING AT ADMIN PANEL USING OUR OWN CODE ENDS
 ****************************************************************************************/

// CODE TO REGISTER OUR OWN CUSTOM POST TYPE FOR COMPETITION ENTRIES
add_action( 'init', 'create_entries' );
function create_entries() {
    register_post_type( 'entries',
        array(
            'labels' => array(
                'name' => _x( 'Entries', 'post type general name', 'cswm' ),
                'singular_name' => _x( 'Entry', 'post type singular name', 'cswm' ),
                'add_new' => _x( 'Add New', 'Entry', 'cswm' ),
                'add_new_item' => __('Add New Entry','cswm'),
                'edit' => __('Edit','cswm'),
                'edit_item' => __('Edit Entry','cswm'),
                'new_item' => __('New Entry','cswm'),
                'view' => __('View','cswm'),
                'view_item' => __('View Entry','cswm'),
                'search_items' => __('Search Entry','cswm'),
                'not_found' => __('No Entries found','cswm'),
                'not_found_in_trash' => __('No Entries found in Trash','cswm'),
            ),
            'query_var' => true,
            'exclude_from_search' => true,
            'public' => true,
            'publicly_queryable'=>false,
            'menu_position' => 18,
            'supports' => array( 'title', 'editor', 'thumbnail' , 'custom-fields' ),
            'taxonomies' => array( '' ),
            'hierarchical' => false,
            'has_archive' => false,
        )
    );
}

/**************************************************************
 * EXPORT AS CSV BUTTON ABOVE ENTY LISTIN AT ADMIN PANEL STARTS
****************************************************************/
add_filter( 'views_toplevel_page_manage-competition-entries', 'add_export_as_csv_button_to_views' );
function add_export_as_csv_button_to_views( $views )
{
    $url = "";
    $export_votes_url = "";
    if(isset($_REQUEST['competition_id']) && $_REQUEST['competition_id']!='')
    {
        $url = admin_url( "admin-post.php?action=print.csv&competition_id=".$_REQUEST['competition_id'] );

        $export_votes_url = admin_url( "admin-post.php?action=printvoters.csv&competition_id=".$_REQUEST['competition_id'] );

        $export_insta_url = admin_url( "admin-post.php?action=printinsta.csv&competition_id=".$_REQUEST['competition_id'] );
    }
    if(isset($_REQUEST['winners']) && $_REQUEST['winners']==true)
    {
        $url .= '&winners=true';
    }
    if(isset($_REQUEST['s']) && $_REQUEST['s']!='')
    {
        $url .= '&s='.$_REQUEST['s'];
    }
    if($url!="")
    {
        $passed_competition_id = $_REQUEST['competition_id'];
        $args = array('post_type' => 'entries','post_status' => 'publish','orderby'=>'ID','posts_per_page'=>-1);
        $args['meta_query'] = array(
                                        array('key' => 'competition','value' => $passed_competition_id,'compare' => '=')
                                    );
        $found_entries = new WP_Query( $args );
        if($found_entries->have_posts())
        {
            $views['export-button'] = sprintf('<a href="%s" id="entry-export-csv" class="button" title="%s">%s</a>',$url, __('Export Entries CSV', 'cswm'),__('Export Entries CSV', 'cswm'));
            wp_reset_postdata();    
        }        
    }
    if($export_votes_url!="")
    {
        if(get_page_template_slug($_REQUEST['competition_id']) != 'page-templates/competition-template-one.php')
        {

            $passed_competition_id = $_REQUEST['competition_id'];
            $entry_ids = array();

            //FETCH ALL THE ENTRIES SUBMITTED IN A COMPETITION
            $args = array('post_type' => 'entries','post_status' => 'publish','orderby'=>'ID','posts_per_page'=>-1);
            $args['meta_query'] = array(
                                            array('key' => 'competition','value' => $passed_competition_id,'compare' => '=')
                                        );

            $found_entries = new WP_Query( $args );
            if ( $found_entries->have_posts() ) 
            {
                while ($found_entries->have_posts()) {
                    $found_entries->the_post();
                    $entry_ids[] = get_the_ID();
                }   
                wp_reset_postdata();
            }

            if(sizeof($entry_ids))
            {
                global $wpdb;
                $tablename = $wpdb->prefix . 'vote_users';
                $vote_detailtablename = $wpdb->prefix . 'user_votedetail';

                //print_r($entry_ids);
                $ids = join(",",$entry_ids);   
                $querystr = "SELECT * FROM ".$tablename.", ".$vote_detailtablename." WHERE ".$tablename.".ID=".$vote_detailtablename.".user_id AND ".$vote_detailtablename.".entry_id IN (".$ids.") ORDER BY ".$vote_detailtablename.".entry_id";

                $results = $wpdb->get_results($querystr, OBJECT);
                // DISPLAY ONLY IF ANY VOTE EXIST
                if ($results):
                    $views['export-votes-button'] = sprintf('<a href="%s" id="voters-export-csv" class="button" title="%s">%s</a>',$export_votes_url, __('Export Voters CSV', 'cswm'),__('Export Voters CSV', 'cswm'));
                endif;

                if(get_page_template_slug($_REQUEST['competition_id']) == 'page-templates/competition-template-four.php')
                {
                    $views['export-insta-button'] = sprintf('<a href="%s" id="insta-export-csv" class="button" title="%s">%s</a>',$export_insta_url, __('Export Instagram Users CSV', 'cswm'),__('Export Instagram Users CSV', 'cswm'));
                }
            }
        }
    }

    return $views;
}


add_action( 'admin_head', 'export_csv_button_position_change' );
function export_csv_button_position_change(  )
{
    global $current_screen;
    if( 'toplevel_page_manage-competition-entries' != $current_screen->id )
        return;
    ?>
    <script type="text/javascript">
        jQuery(document).ready( function($) 
        {    
            if($("#entry-export-csv").length)
            {
                $("#entry-export-csv").insertAfter(".top .tablenav-pages");
                $('#entry-export-csv').css({"float":"right","margin":"0px 10px 0px 0px"});
                $(".subsubsub li.export-button").css({"display":"none"});
            }
            
            if( $("#voters-export-csv").length )
            {
                $("#voters-export-csv").insertAfter("#entry-export-csv");
                $('#voters-export-csv').css({"float":"right","margin":"0px 10px 0px 0px"});
                $(".subsubsub li.export-votes-button").css({"display":"none"});
            }  

            if( $("#insta-export-csv").length )
            {
                $("#insta-export-csv").insertAfter("#entry-export-csv");
                $('#insta-export-csv').css({"float":"right","margin":"0px 10px 0px 0px"});
                $(".subsubsub li.export-insta-button").css({"display":"none"});               
                
            }  
        });     
    </script>
    <?php 
}


// ACTION TO PROCESS EXPORT AS CSV FOR VOTES SUBMITTED
add_action( 'admin_post_printvoters.csv', 'print_voters_csv' );
function print_voters_csv()
{    
    if(!current_user_can( 'edit_posts' ) )
        return;

    $nonce = $_REQUEST['_wpnonce'];
    if ( ! wp_verify_nonce( $nonce, 'voter-csv-password-nonce' ) ) 
       return;

    if(isset($_SESSION['voter_pass_varified']) && $_SESSION['voter_pass_varified']==true)
        $_SESSION['voter_pass_varified'] = false;
    else
        return;
       
    /****** OUR QUERY FORMATION BASED ON PASSED ARGUMENTS STARTS *******/
    $passed_competition_id = $_REQUEST['competition_id'];
    $entry_ids = array();

    //FETCH ALL THE ENTRIES SUBMITTED IN A COMPETITION
    $args = array('post_type' => 'entries','post_status' => 'publish','orderby'=>'ID','posts_per_page'=>-1);
    $args['meta_query'] = array(
                                    array('key' => 'competition','value' => $passed_competition_id,'compare' => '=')
                                );
   
    $found_entries = new WP_Query( $args );
    if ( $found_entries->have_posts() ) 
    {
        while ($found_entries->have_posts()) {
            $found_entries->the_post();
            $entry_ids[] = get_the_ID();
        }   
        wp_reset_postdata();
    }
    if(sizeof($entry_ids))
    {
        global $wpdb;
        $tablename = $wpdb->prefix . 'vote_users';
        $vote_detailtablename = $wpdb->prefix . 'user_votedetail';

        $ids = join(",",$entry_ids);   
        $querystr = "SELECT ".$tablename.".gender,".$tablename.".first_name,".$tablename.".last_name,CAST(aes_decrypt(from_base64(".$tablename.".email_address),UNHEX('".ENC_KEY."')) AS CHAR) AS email_address,".$tablename.".phone_code,CAST(aes_decrypt(from_base64(".$tablename.".phone_number),UNHEX('".ENC_KEY."')) AS CHAR) AS phone_number,".$vote_detailtablename.".entry_id,".$tablename.".vote_date FROM ".$tablename.", ".$vote_detailtablename." WHERE ".$tablename.".ID=".$vote_detailtablename.".user_id AND ".$vote_detailtablename.".entry_id IN (".$ids.") ORDER BY ".$vote_detailtablename.".entry_id";

        $results = $wpdb->get_results($querystr, OBJECT);
        if ($results):

            /* FILE NAME CREATION */
            $sitename = sanitize_key( get_bloginfo( 'name' ) );
            if ( ! empty($sitename) ) $sitename .= '-';

            // COMPETITION NAME PROCESSING
            $competition_nm = get_the_title($passed_competition_id);
            $string = "n&ndash;dash";
            $competition_nm = mb_convert_encoding($competition_nm, 'UTF-8', 'HTML-ENTITIES');

            $output_filename  = $sitename.$competition_nm. '-voters-log-' . date('d-m-Y') ;
            $output_filename = str_replace(' ', '-', $output_filename);
            $output_filename = strtolower(trim(preg_replace('![^a-z0-9]+!i', '-', $output_filename)));
            $output_filename = $output_filename. '.csv';

            $output_handle = fopen('php://output', 'w');
            $csv_headers = array();
            $csv_headers[] = __('Name','cswm');
            $csv_headers[] = __('Email Address','cswm');
            $csv_headers[] = __('Country Code','cswm');
            $csv_headers[] = __('Phone','cswm');
            $csv_headers[] = __('Entry ID','cswm');
            $csv_headers[] = __('Voted On','cswm');

            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header( 'Content-Description: File Transfer' );
            header( 'Content-type: text/csv' );
            header( 'Content-Disposition: attachment; filename=' . $output_filename );
            header( 'Expires: 0' );
            header( 'Pragma: public' );

            // Insert header row
            fputcsv( $output_handle, $csv_headers );

            foreach ( $results as $result ) 
            {
                $data = array();

                $data[] = $result->first_name." ".$result->last_name;
                $data[] = $result->email_address;
                $data[] = $result->phone_code;
                $data[] = $result->phone_number;
                //FETCHING ENTRY ID FROM POST META TABLE
                $data[] = get_post_meta( $result->entry_id, 'entry_id', true );
                if ( '0000-00-00 00:00:00' == $result->vote_date ) {
                    $data[] = ' - ';
                } else {
                    /* translators: date format in table columns, see https://secure.php.net/date */
                    $data[]= date(__('d/m/Y H:i:s'), strtotime($result->vote_date));
                }
                fputcsv( $output_handle, $data );
            }
            fclose( $output_handle ); 
            return;
        else:
            return;
        endif;
    }
    else
    {
        return;
    }
}
add_action( 'admin_post_printinsta.csv', 'print_insta_csv' );
function print_insta_csv()
{    
    if(!current_user_can( 'edit_posts' ) )
        return;

    $nonce = $_REQUEST['_wpnonce'];
    if ( ! wp_verify_nonce( $nonce, 'insta-csv-password-nonce' ) ) {
       return;
    }

    if(isset($_SESSION['insta_pass_varified']) && $_SESSION['insta_pass_varified']==true)
        $_SESSION['insta_pass_varified'] = false;
    else
        return;

    /****** OUR QUERY FORMATION BASED ON PASSED ARGUMENTS STARTS *******/
    $passed_competition_id = $_REQUEST['competition_id'];

    $search_param = "";
    if(isset($_REQUEST['s']) && $_REQUEST['s']!='')
        $search_param = $_REQUEST['s'];

    $args = array('post_type' => 'entries','post_status' => 'publish','orderby'=>'ID');

    if($search_param!="")
        $args['post_title_like'] = $search_param;

    if($search_param!="")
    {
        $args['encrypted_email_search_meta'] = $search_param;
        $common_meta_arr = array('relation'=>'AND',
                                    array('key' => 'competition','value' => $passed_competition_id,'compare' => '='),
                                    array('relation'=> 'OR',
                                            array('key' => 'user_first_name','value' => $search_param,'compare' => 'LIKE'),
                                            array('key' => 'user_last_name','value' => $search_param,'compare' => 'LIKE'),
                                            array('key' => 'user_email_address','value' => $search_param,'compare' => 'LIKE')
                                         )
                                );
    }
    else
    {
        $common_meta_arr = array('key' => 'competition','value' => $passed_competition_id,'compare' => '=');
    }

    $winners = "";
    if(isset($_REQUEST['winners']) && $_REQUEST['winners']!="")
        $winners = $_REQUEST['winners'];
    if($winners!='')
    {
        $args['meta_query'] = array(
                                        'relation' => 'AND',
                                        $common_meta_arr,
                                        array(
                                                'key' => 'winner',
                                                'value' => 1,
                                                'compare' => '='
                                              )
                                    );
    }
    else 
    {
         $args['meta_query'] = array($common_meta_arr);
    }
    $args['posts_per_page'] = -1;
    /****** OUR QUERY FORMATION BASED ON PASSED ARGUMENTS ENDS *******/

    $competition_entries = new WP_Query( $args );
    if ( $competition_entries->have_posts() ) 
    {

        /* FILE NAME CREATION */
        $sitename = sanitize_key( get_bloginfo( 'name' ) );
        if ( ! empty($sitename) ) $sitename .= '-';

        // COMPETITION NAME PROCESSING
        $competition_nm = get_the_title($passed_competition_id);
        $string = "n&ndash;dash";
        $competition_nm = mb_convert_encoding($competition_nm, 'UTF-8', 'HTML-ENTITIES');

        $output_filename  = $sitename.$competition_nm. '-insta-user-log-' . date('d-m-Y') ;
        $output_filename = str_replace(' ', '-', $output_filename);
        $output_filename = strtolower(trim(preg_replace('![^a-z0-9]+!i', '-', $output_filename)));
        $output_filename = $output_filename. '.csv';

        $output_handle = fopen('php://output', 'w');
        $csv_headers = array();
        $csv_headers[] = __('Entry Id','cswm');
        $csv_headers[] = __('Instagram User Name','cswm');
        $csv_headers[] = __('Instagram Profile Link','cswm');
        $csv_headers[] = __('Instagram User Id','cswm');

        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
        header( 'Content-Description: File Transfer' );
        header( 'Content-type: text/csv' );
        header( 'Content-Disposition: attachment; filename=' . $output_filename );
        header( 'Expires: 0' );
        header( 'Pragma: public' );

        // Insert header row
        fputcsv( $output_handle, $csv_headers );

        while ( $competition_entries->have_posts() ) 
        {
            $competition_entries->the_post();
            $entry_id = get_the_ID();
            $data = array();

            $entry_meta_id = get_post_meta( $entry_id, 'entry_id', true );
            if($entry_meta_id!='')
                $data[] = $entry_meta_id;
            else
                $data[] = ' - ';

            $instaUserName = get_post_meta( $entry_id, 'insta_user_name', true );
            if($instaUserName!='')
                $data[] = $instaUserName;
            else
                $data[] = ' - ';

            $instaProfile = get_post_meta( $entry_id, 'insta_profile', true );
            if($instaProfile!='')
                $data[] = $instaProfile;
            else
                $data[] = ' - ';

            $instaUserId = get_post_meta( $entry_id, 'insta_user_id', true );
            if($instaUserId!='')
                $data[] = $instaUserId;
            else
                $data[] = ' - ';

            fputcsv( $output_handle, $data );
        }
        // Close output file stream
        fclose( $output_handle ); 

        return;
    }
    else
    {
        /* If there are no logs - abort */
        return;
    }
}

// ACTION TO PROCESS EXPORT AS CSV FOR ENTRIES SUBMITTED
add_action( 'admin_post_print.csv', 'print_csv' );
function print_csv()
{    
    if(!current_user_can( 'edit_posts' ) )
        return;
       
    $nonce = $_REQUEST['_wpnonce'];
    if ( ! wp_verify_nonce( $nonce, 'entry-csv-password-nonce' ) ) 
       return;


    if(isset($_SESSION['entry_pass_varified']) && $_SESSION['entry_pass_varified']==true)
        $_SESSION['entry_pass_varified'] = false;
    else
        return;

    $passed_competition_id = $_REQUEST['competition_id']; 

    /* FACEBOOK FIELDS SHOULD NOT BE ADDED TO THE CSV IF NO FACEBOOK ENTRY IS SUBMITTED & FACEBOOK LOGIN IS DISABLED FOR THE COMPETITION */
    global $wpdb;
    $posts = $wpdb->prefix . 'posts';
    $postmeta = $wpdb->prefix . 'postmeta';

    $querystr = "select COUNT(*) FROM ".$posts.",".$postmeta." WHERE ".$posts.".ID=".$postmeta.".post_id AND ".$posts.".post_status='publish' AND ".$postmeta.".meta_key='user_fb_link' AND ".$postmeta.".meta_value!='' AND ".$posts.".ID IN (select post_id from ".$postmeta." where meta_key='competition' AND meta_value=".$passed_competition_id.")";
    $fbEntryCnt = $wpdb->get_var($querystr);

    $showFbFields = true;
    $fb_login = get_field('facebook_login',$passed_competition_id);
    if($fb_login == 'disable') {
        if($fbEntryCnt == 0) {
            $showFbFields = false;
        }
    }
    /* FACEBOOK FIELDS SHOULD NOT BE ADDED TO THE CSV IF NO FACEBOOK ENTRY IS SUBMITTED & FACEBOOK LOGIN IS DISABLED FOR THE COMPETITION */

    /****** OUR QUERY FORMATION BASED ON PASSED ARGUMENTS STARTS *******/ 
    $search_param = "";
    if(isset($_REQUEST['s']) && $_REQUEST['s']!='')
        $search_param = $_REQUEST['s'];

    $args = array('post_type' => 'entries','post_status' => 'publish','orderby'=>'ID');

    if($search_param!="")
        $args['post_title_like'] = $search_param;

    if($search_param!="")
    {
        $args['encrypted_email_search_meta'] = $search_param;
        $common_meta_arr = array('relation'=>'AND',
                                    array('key' => 'competition','value' => $passed_competition_id,'compare' => '='),
                                    array('relation'=> 'OR',
                                            array('key' => 'user_first_name','value' => $search_param,'compare' => 'LIKE'),
                                            array('key' => 'user_last_name','value' => $search_param,'compare' => 'LIKE'),
                                            array('key' => 'user_email_address','value' => $search_param,'compare' => 'LIKE')
                                         )
                                );
    }
    else
    {
        $common_meta_arr = array('key' => 'competition','value' => $passed_competition_id,'compare' => '=');
    }

    $winners = "";
    if(isset($_REQUEST['winners']) && $_REQUEST['winners']!="")
        $winners = $_REQUEST['winners'];
    if($winners!='')
    {
        $args['meta_query'] = array(
                                        'relation' => 'AND',
                                        $common_meta_arr,
                                        array(
                                                'key' => 'winner',
                                                'value' => 1,
                                                'compare' => '='
                                              )
                                    );
    }
    else 
    {
         $args['meta_query'] = array($common_meta_arr);
    }
    $args['posts_per_page'] = -1;
    /****** OUR QUERY FORMATION BASED ON PASSED ARGUMENTS ENDS *******/

    $competition_entries = new WP_Query( $args );
    if ( $competition_entries->have_posts() ) 
    {
        /* FILE NAME CREATION */
        $sitename = sanitize_key( get_bloginfo( 'name' ) );
        if ( ! empty($sitename) ) $sitename .= '-';

        // COMPETITION NAME PROCESSING
        $competition_nm = get_the_title($passed_competition_id);
        $string = "n&ndash;dash";
        $competition_nm = mb_convert_encoding($competition_nm, 'UTF-8', 'HTML-ENTITIES');

        $output_filename  = $sitename.$competition_nm. '-entries-log-' . date('d-m-Y') ;
        $output_filename = str_replace(' ', '-', $output_filename);
        $output_filename = strtolower(trim(preg_replace('![^a-z0-9]+!i', '-', $output_filename)));
        $output_filename = $output_filename. '.csv';

        $output_handle = fopen('php://output', 'w');
        $csv_headers = array();
        $csv_headers[] = __('Name','cswm');
        $csv_headers[] = __('Email Address','cswm');
        $csv_headers[] = __('Country Code','cswm');
        $csv_headers[] = __('Phone','cswm');
        $csv_headers[] = __('Views','cswm');
        $csv_headers[] = __('Votes','cswm');
        $csv_headers[] = __('Editor Pick','cswm');
        $csv_headers[] = __('Winner','cswm');
        
        if($showFbFields)
        {
            $csv_headers[] = __('Facebook Id','cswm');
            $csv_headers[] = __('Age Range','cswm');
            $csv_headers[] = __('Facebook Link','cswm');
            $csv_headers[] = __('Gender','cswm');
            $csv_headers[] = __('Locale','cswm');
            $csv_headers[] = __('Timezone','cswm');
        }
        $csv_headers[] = __('Submitted on','cswm');
       

        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
        header( 'Content-Description: File Transfer' );
        header( 'Content-type: text/csv' );
        header( 'Content-Disposition: attachment; filename=' . $output_filename );
        header( 'Expires: 0' );
        header( 'Pragma: public' );

        // Insert header row
        fputcsv( $output_handle, $csv_headers );

        while ( $competition_entries->have_posts() ) 
        {
            $competition_entries->the_post();
            $entry_id = get_the_ID();
            $entry_title = get_the_title();

            $competition_type = get_post_meta( $entry_id, 'competition_type', true );
            $user_ph_code = $user_phno = "";
            $user_ph_code = get_post_meta( $entry_id, 'user_ph_code', true ); 
            $entry_views = get_post_meta( $entry_id, 'entry_views', true );
            $entry_votes = get_post_meta( $entry_id, 'entry_votes', true );
            $editor_pick = get_post_meta( $entry_id, 'editor_pick', true );
            $winner = get_post_meta( $entry_id, 'winner', true );

            $data = array();

            $data[] = $entry_title;

            $user_email_address = get_post_meta( $entry_id, 'user_email_address', true );
            if(is_null($user_email_address)){
                $data[] = ' - ';  
            }
            else if ( base64_encode(base64_decode($user_email_address, true)) === $user_email_address)
            {
                $decyptedEmail = getDecryptedMeta('user_email_address', $entry_id);
                if (empty( $decyptedEmail )) {
                    $data[] = ' - '; 
                }
                else {
                    $data[] = $decyptedEmail;     
                }
            }

            if($user_ph_code!="" || $user_ph_code!=NULL)
                $data[] = $user_ph_code;
            else
                $data[] = ' - ';

            $user_phno = get_post_meta( $entry_id, 'user_phno', true );
            if(is_null($user_phno)){
                $data[] = ' - ';  
            } 
            else if ( base64_encode(base64_decode($user_phno, true)) === $user_phno) {
                $decyptedPhno = getDecryptedMeta('user_phno', $entry_id);
                if (empty( $decyptedPhno )) {
                    $data[] = ' - '; 
                }
                else {
                    $data[] = $decyptedPhno;     
                }
            }

            $data[] = $entry_views;
            $data[] = $entry_votes;
            if($editor_pick==1)
                $data[] = 'Yes';
            else
                $data[] = ' - ';

            if($winner==1)
                $data[] = 'Yes';
            else
                $data[] = ' - ';


            if($showFbFields)
            {   
                $user_fb_id = $user_fb_link = $user_fb_gender = $user_fb_locale = "";
                $user_fb_timezone = $user_fb_min_age = $user_fb_max_age = $user_gender = "";

                $user_fb_id = get_post_meta( $entry_id, 'user_fb_id', true );
                if($user_fb_id!='')
                    $data[] = $user_fb_id;
                else
                    $data[] = ' - ';

                $user_fb_min_age = get_post_meta( $entry_id, 'user_fb_min_age', true );
                $user_fb_max_age = get_post_meta( $entry_id, 'user_fb_max_age', true );
                if($user_fb_min_age!="" && $user_fb_max_age!="")
                {
                    $data[] = $user_fb_min_age." - ".$user_fb_max_age;
                }
                else if($user_fb_min_age=="" && $user_fb_max_age!="")
                {
                    $data[] = $user_fb_max_age;
                }
                else if($user_fb_min_age!="" && $user_fb_max_age=="")
                {
                    $data[] = $user_fb_min_age . "+";
                }
                else
                {
                    $data[] = " - ";
                }

                $user_fb_link = get_post_meta( $entry_id, 'user_fb_link', true );
                if($user_fb_link!='')
                    $data[] = $user_fb_link;
                else
                    $data[] = ' - ';


                $user_gender = get_post_meta( $entry_id, 'user_gender', true );
                $user_fb_gender = get_post_meta( $entry_id, 'user_fb_gender', true );
                if($user_fb_gender!='')
                {
                    $data[] = $user_fb_gender;
                }
                else if($user_gender!='')
                {
                    if($user_gender=='Mr')
                        $data[] = 'male';
                    else
                        $data[] = 'female';
                }
                else
                    $data[] = ' - ';

                $user_fb_locale = get_post_meta( $entry_id, 'user_fb_locale', true );
                if($user_fb_locale!='')
                    $data[] = $user_fb_locale;
                else
                    $data[] = ' - ';

                $user_fb_timezone = get_post_meta( $entry_id, 'user_fb_timezone', true );
                if($user_fb_timezone!='')
                    $data[] = $user_fb_timezone;
                else
                    $data[] = ' - ';
            } 
            //END $showFbFields

            $data[] = get_the_date( 'd/m/Y H:i:s', $entry_id); 

            fputcsv( $output_handle, $data );
        }
        // Close output file stream
        fclose( $output_handle ); 

        return;
    }
    else
    {
        /* If there are no logs - abort */
        return;
    }
}
/************************************************************
 * EXPORT AS CSV BUTTON ABOVE ENTY LISTIN AT ADMIN PANEL ENDS
*************************************************************/

/***************************************************************************************
* CUSTOM ADMIN PANEL PAGE IT DISPLAY ENTRIES SUBMITTED ON PARTICULAR COMPETITION STARTS
***************************************************************************************/
add_action( 'admin_menu', 'my_plugin_menu' );
function my_plugin_menu() {
    add_menu_page( __('Competition Entries','cswm'), __('Competition Entries','cswm'), 'edit_posts', 'manage-competition-entries','manage_competition_entries','', '18');
}

require_once ( dirname(__FILE__) . '/Manage_Competition_Entries_List_Table.php');
function manage_competition_entries()
{
    wp_enqueue_style('fancybox', get_theme_file_uri('/assets/css/fancybox/jquery.fancybox-1.3.4.css'));
    wp_enqueue_script('fancybox', get_theme_file_uri('/assets/js/jquery.fancybox-1.3.4.pack.js'), array( 'jquery' ));

    wp_register_script('custom-admin', get_theme_file_uri('/assets/js/custom_admin.js'), array( 'jquery','fancybox'));

    // Localize the script with new data
    $translation_array = array(
        'ajaxurl' => admin_url('admin-ajax.php')
    );

    wp_localize_script( 'custom-admin', 'admin_localized', $translation_array );
    // Enqueued script with localized data.
    wp_enqueue_script( 'custom-admin' );
?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Manage Entries','cswm'); ?></h1>
        <form method="post">
        <?php

        // NEED TO CHECK IF COMPETITION HAS ENDED OR NOT.
        // IF YES, THEN WE'LL ENABLE MARK AS WINNER DROP DOWN OPTION ABOVE ENTRIRES LISTING
        /*if(isset($_GET['competition_id']) && $_GET['competition_id']!="")
        {
            $competition_end_date = get_post_meta($_GET['competition_id'], 'competition_end_date', true);
            $current_date = date('d-m-Y');

            if ($current_date <= $competition_end_date) 
                echo "<input type='hidden' name='competition_ended' id='competition_ended' value='0'>";
            else
                echo "<input type='hidden' name='competition_ended' id='competition_ended' value='1'>";
        }*/
        
        
        if(isset($_GET['competition_id']) && $_GET['competition_id']!="")
        {
            $competition_id = $_GET['competition_id'];
            $entriresListTable = new Manage_Competition_Entries_List_Table();
            
            if( isset($_REQUEST['s']) ){
                    $entriresListTable->prepare_items($competition_id,$_REQUEST['s']);
            } else {
                    $entriresListTable->prepare_items($competition_id);
            }
            
            $entriresListTable->views();
            
            // CUSTOM SERCH BOX FOR ENTRIES LISTING TABLE AT ADMIN PANEL
            echo '<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />';
            $entriresListTable->search_box('Search Entries', 'custom-entries');
            
            $entriresListTable->display();
        }
        ?>
        </form>
    </div>
<?php
}

/************************************************************************************
* CUSTOM ADMIN PANEL PAGE IT DISPLAY ENTRIES SUBMITTED ON PARTICULAR COMPETITION ENDS
*************************************************************************************/

/*****************************************************************************
 *  CODE TO REMOVE ENTRIES ORIGINAL CUSTOM POST TYPE MENU FROM ADMIN PANEL
 * WE'LL ADD OUR OWN MENU TO LIST ENTRIRES SUBMITTED TO SPECIFIC COMPETITION
 *****************************************************************************/
function remove_menu_items() {
    remove_menu_page( 'edit.php?post_type=entries' );
    remove_menu_page( 'edit.php' );
    remove_menu_page( 'edit-comments.php' );
    remove_menu_page( 'manage-competition-entries' );
}
add_action( 'admin_menu', 'remove_menu_items' );

/**************************************************************************************
 * FUNCTION TO GET COUNT OF TOTAL ENTRIES SUBMITTED ON PARTICULAR COMPETITION ENDS HERE
 *************************************************************************************/
add_action( 'admin_init', 'codex_init' );
function codex_init() {
    add_action( 'delete_post', 'creole_delete_extra_meta_data', 10 );
}
function creole_delete_extra_meta_data( $pid ) 
{
    global $post_type;   
    $current_action = current_action();
    if ( $post_type != 'competitions' )
        return;

    // WHEN A COMPETITION IS PERMENANTLY DELETED REMOVE ALL ENTRIES SUBMITTED ON IT
    if ( $post_type == 'competitions' && $current_action=='delete_post')
    {
        $args = array(
                        'post_type' =>'entries',
                        'posts_per_page' => -1,
                        'post_status' => 'any',
                        'meta_query'  => array(
                                                array(
                                                    'key' => 'competition',
                                                    'compare' => '=',
                                                    'value' => $pid,
                                                ),
                                            )
                     );

        $the_query = new WP_Query( $args );
        if ( $the_query->have_posts() ) 
        {
            while ( $the_query->have_posts() ) 
            {
                $the_query->the_post();
                $entry_id = get_the_id();

                /* DELETE POST AUTHOR DETAILS IS USER ROLE IS SUBSCRIBER */
                $post_author = get_the_author_id();
                $user_meta = get_userdata($post_author); 
                $user_roles = $user_meta->roles; 
                if (in_array("subscriber", $user_roles)) 
                {
                    /* SUBSCRIBER IS THE USER REGISTERED ON ENTRY SUBMISSION */ 
                    $args = array(
                                    'post_type'   => 'entries',
                                    'author'      => $post_author,
                                    'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
                                    'numberposts' => -1
                                );

                    $other_entries = get_posts($args);
                    if ( $other_entries ) {
                        
                        // USER DETAILS WILL ONLY BE DELETED IF HE/SHE HAS NO OTHER POSTS SUBMITTED
                        if(count($other_entries) == 1)
                        {
                            $metas = get_user_meta($post_author);
                            foreach ($metas as $key => $value) {
                                delete_user_meta( $post_author, $key);
                            }

                            // AS RE-ASSIGNED PERAMETER IS NOT PASSED ALL THE  POST OF THIS USER WILL BE AUTOMATICALLY DELETED
                            wp_delete_user( $post_author );
                        }
                        // USER DETAILS WILL ONLY BE DELETED IF HE/SHE HAS NO OTHER POSTS SUBMITTED
                        wp_reset_postdata();
                    }
                }
                /* DELETE POST AUTHOR DETAILS IS USER ROLE IS SUBSCRIBER */

                /** DELETING ENTRY/ENRTY META/ENTRY VOTE DETAILS STARTS **/
                $attachment_id = 0;
                $postmetas = get_post_meta($entry_id);
                foreach ($postmetas as $key => $value) { 

                    if($key == '_thumbnail_id')
                        $attachment_id = get_post_meta($post_id, '_thumbnail_id', true);

                    delete_post_meta($post_id, $key);
                }

                /* WE WILL DELETE ATTACHMENTS SUBMITTED FOR THE ENTRY (FOR COMPETITION TEMPLATE ONE )*/
                if($attachment_id!=0) {
                    wp_delete_attachment( $attachment_id, true );
                }

                /* DELETING VOTES SUBMITTED ON THE ENTRY */
                global $wpdb;
                $tablename = $wpdb->prefix . 'vote_users';
                $vote_detailtablename = $wpdb->prefix . 'user_votedetail';

                $querystr = "SELECT * FROM ".$tablename.", ".$vote_detailtablename." WHERE ".$tablename.".ID=".$vote_detailtablename.".user_id AND ".$vote_detailtablename.".entry_id=".$entry_id." ORDER BY ".$vote_detailtablename.".entry_id";

                $results = $wpdb->get_results($querystr, OBJECT);
                if ($results):
                    foreach ( $results as $result ) 
                    {
                        $wpdb->delete( $vote_detailtablename , array( 'ID' => $result->ID ) );
                        $wpdb->delete( $tablename , array( 'ID' => $result->ID ) );
                    }
                endif;
                /* DELETING VOTES SUBMITTED ON THE ENTRY */

                wp_delete_post( $entry_id );
                /** DELETING ENTRY/ENRTY META/ENTRY VOTE DETAILS ENDS **/
            }
            wp_reset_postdata();
        } 
    }
}


// MANAGE CUSTOM LIST TABLE COLUMN WIDTHS
add_action('admin_head', 'creole_entries_column_width');
function creole_entries_column_width() 
{
    echo '<style type="text/css">';
    echo '.column-entry_name{ text-align: left; width:12% !important; }';
    echo '.column-entry_email{ text-align: left; width:20% !important; }';
    echo '.column-entry_views,.column-entry_votes,.column-editor_pick,.column-winner{ width:7% !important; }';
    echo '.column-entry_detail{text-align: left; width:20% !important; }';
    echo '#fancybox-overlay {z-index:11111 !important;}';
    echo '#fancybox-wrap {z-index:11112 !important;}';
    echo '</style>';

    if(!current_user_can('administrator')){
        echo '<style type="text/css">';
        echo '.column-email{ display: none; }';
        echo '.user-email-wrap {display:none; }';
        echo '</style>';
    }
}
/*
add_filter( 'posts_request', 'dump_request' );
function dump_request( $input ) {

    var_dump($input);

    return $input;
}
*/
add_action('admin_init', 'custom_preview_button');
function custom_preview_button() 
{
    //if (isset($_GET['action']) && $_GET['action'] === 'edit') 
    if (isset($_GET['post_type']) && $_GET['post_type'] == 'competitions')
    {
        add_meta_box('competition_meta_box',__('Template Preview','cswm'), 'display_preview_meta_box', 'competitions', 'side', 'low');
    }
}

add_action('admin_head-media-upload-popup', 'custom_tb_styles');
function custom_tb_styles() {
?>
<style>
    #TB_window 
    {
        background:silver;
    }
</style>
<?php
}
function display_preview_meta_box()
{ 
    wp_enqueue_script(
    'custom-thickbox', get_theme_file_uri('/assets/js/custom_admin_thickbox.js'), 
    array( 'thickbox' ));
    add_thickbox();
    ?>
    <div id="preview-template" style="display:none;">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/template_1.png" style="max-width: 100%; display: none;" id="template_1_preview"  class="preview_img">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/template_2.png" style="max-width: 100%; display: none;" id="template_2_preview" class="preview_img">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/template_3.png" style="max-width: 100%; display: none;" id="template_3_preview" class="preview_img">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/template_4.png" style="max-width: 100%; display: none;" id="template_4_preview" class="preview_img">
    </div>
    <?php
    echo '<a class="button button-primary thickbox button-large" id="template-preview" href="#TB_inline?&inlineId=preview-template" >'.__('Preview Template','cswm').'</a>';
    ?>
    
    <script type="text/javascript"> 
       jQuery(document).ready(function($)
            {
                $('.thickbox.button').click(function(e)
                {
                    e.preventDefault();    
                    selected_option = $( "#page_template option:selected" ).val();
                    $( ".preview_img" ).each(function( index ) 
                    {   
                        if(selected_option == 'page-templates/competition-template-one.php' && $( this ).attr("id")=='template_1_preview')
                        {
                            $(this).css("display","block");
                        }
                        else if(selected_option == 'page-templates/competition-template-two.php' && $( this ).attr("id")=='template_2_preview')
                        {
                            $(this).css("display","block");
                        }
                        else if(selected_option == 'page-templates/competition-template-three.php' && $( this ).attr("id")=='template_3_preview')
                        {
                            $(this).css("display","block");
                        }
                        else if(selected_option == 'page-templates/competition-template-four.php' && $( this ).attr("id")=='template_4_preview')
                        {
                            $(this).css("display","block");
                        }
                        else
                        {
                            $(this).css("display","none");
                        }
                    });
                });

                var tb = document.getElementById('TB_ajaxContent');
                $(tb).css("width","800px");

            });
    </script>
    <?php
}
function remove_default_page_template() 
{
    global $pagenow;
    if ( in_array( $pagenow, array( 'post-new.php', 'post.php') ) && get_post_type() == 'competitions' ) 
    { 
        ?>
            <script type="text/javascript"> 
                jQuery(document).ready(function($)
                {
                    if($('#page_template').length)
                    {
                        $('#page_template option[value="default"]').remove();
                        $("#page_template select").val("Template #1");
                        acf.screen.page_template = $("#page_template").val();
                        $(document).trigger('acf/update_field_groups');
                    }
                });
            </script>
    <?php 
    }
}
add_action('admin_footer', 'remove_default_page_template', 10);
function fetch_instagram_details($url,$parameter)
{
    $api = file_get_contents("http://api.instagram.com/oembed?url=$url&maxwidth=333");
    $apiObj = json_decode($api,true);
    return $apiObj[$parameter];
}
/* ADDING PASSWORD OPTION FOR EACH CSV FILE */
add_action( 'init', 'csv_pass_admin_init' );
function csv_pass_admin_init() {
    $settings = get_option( "csv_pass_settings_option" );
    if ( empty( $settings ) ) {
        $settings = array(
            'voter_csv_password' => '$P$BBQJs06xtwdX7gSE1YRIsasmiOHKqf.',
            'instagram_csv_password' => '$P$BBQJs06xtwdX7gSE1YRIsasmiOHKqf.',
            'entries_csv_password' => '$P$BBQJs06xtwdX7gSE1YRIsasmiOHKqf.',
        );
        add_option( "csv_pass_settings_option", $settings, '', 'yes' );
    }
}

add_action( 'admin_menu', 'csv_pass_menu_init' );
function csv_pass_menu_init() {
    $settings_page = add_menu_page( __('CSV Password Management','cswm'), __('CSV Password Management','cswm'), 'manage_options', 'csv-pass-management','csv_pass_management','', '16');

    add_action( "load-{$settings_page}", 'csv_pass_settings_page' );
}
function csv_pass_settings_page() {
    if (isset($_POST["csv-settings-submit"] ) && $_POST["csv-settings-submit"] == 'Y' ) 
    {
        check_admin_referer( "csv-pass-settings" );
        $settings = get_option( "csv_pass_settings_option" );
        $entries_csv_password = !empty($settings) ? $settings['entries_csv_password'] : '';
        $voter_csv_password = !empty($settings) ? $settings['voter_csv_password'] : '';
        $instagram_csv_password = !empty($settings) ? $settings['instagram_csv_password'] : '';

        if(isset($_POST['entries_csv_old_password']) && $_POST['entries_csv_old_password']!= "" && 
            isset($_POST['entries_csv_confirm_password']) && $_POST['entries_csv_confirm_password']!= "")
        {
            if(wp_check_password($_POST['entries_csv_old_password'], $entries_csv_password)) 
            {
                if(isset($_POST['entries_csv_password']) && $_POST['entries_csv_password']!='')
                {
                    $settings['entries_csv_password'] = wp_hash_password($_POST['entries_csv_password']);
                }
            }
            else
            {
                $url_parameters = isset($_GET['tab'])? 'updated=false&tab='.$_GET['tab'] : 'updated=false';
                wp_redirect(admin_url('admin.php?page=csv-pass-management&'.$url_parameters));
                exit;
            }
        }
        if(isset($_POST['voter_csv_old_password']) && $_POST['voter_csv_old_password']!= "" && 
            isset($_POST['voter_csv_confirm_password']) && $_POST['voter_csv_confirm_password']!= "")
        {
            if(wp_check_password($_POST['voter_csv_old_password'], $voter_csv_password)) 
            {
                if(isset($_POST['voter_csv_password']) && $_POST['voter_csv_password']!='')
                {
                    $settings['voter_csv_password'] = wp_hash_password($_POST['voter_csv_password']);
                }
            }
            else
            {
                $url_parameters = isset($_GET['tab'])? 'updated=false&tab='.$_GET['tab'] : 'updated=false';
                wp_redirect(admin_url('admin.php?page=csv-pass-management&'.$url_parameters));
                exit;
            }
        }
        if(isset($_POST['instagram_csv_old_password']) && $_POST['instagram_csv_old_password']!= "" && 
            isset($_POST['instagram_csv_confirm_password']) && $_POST['instagram_csv_confirm_password']!= "")
        {
            if(wp_check_password($_POST['instagram_csv_old_password'], $instagram_csv_password)) 
            {
                if(isset($_POST['instagram_csv_password']) && $_POST['instagram_csv_password']!='')
                {
                    $settings['instagram_csv_password'] = wp_hash_password($_POST['instagram_csv_password']);
                }
            }
            else 
            {
                $url_parameters = isset($_GET['tab'])? 'updated=false&tab='.$_GET['tab'] : 'updated=false';
                wp_redirect(admin_url('admin.php?page=csv-pass-management&'.$url_parameters));
                exit;
            }
        }       
        $updated = update_option( "csv_pass_settings_option", $settings );
        $url_parameters = isset($_GET['tab'])? 'updated=false&tab='.$_GET['tab'] : 'updated=true';
        wp_redirect(admin_url('admin.php?page=csv-pass-management&'.$url_parameters));
        exit;
    }
}
function csv_pass_admin_tabs( $current = 'entries-csv-pass' ) { 
    $tabs = array( 
                    'entries-csv-pass' => __('Entries CSV Password', 'cswm'),
                    'voters-csv-pass' => __('Voters CSV Password', 'cswm'), 
                    'insta-csv-pass' => __('Instagram CSV Password', 'cswm'),
                 ); 
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=csv-pass-management&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}
function csv_pass_management() {
    if(!current_user_can( 'manage_options' ) )
        return;   

    ?>
    <div class="wrap">
        <h2><?php _e('CSV Password Management','cswm'); ?></h2>
        <?php
            global $pagenow;
            $settings = get_option( "csv_pass_settings_option" );   

            if ( isset ( $_GET['tab'] ) ) 
                csv_pass_admin_tabs($_GET['tab']); 
            else 
                csv_pass_admin_tabs('entries-csv-pass');
            if (isset($_GET['updated']) && 'true' == esc_attr( $_GET['updated'] ) ) {
                echo sprintf('<div class="notice notice-success is-dismissible" ><p>%s</p></div>', __('Password updated successfully.', 'cswm'));
            }
            if (isset($_GET['updated']) && 'false' == esc_attr( $_GET['updated'] ) ) {
                echo sprintf('<div class="notice notice-error is-dismissible" ><p>%s</p></div>', __('Password updation failed due to wrong old password.', 'cswm'));
            }
        ?>
        <form method="post" action="<?php admin_url( 'admin.php?page=csv-pass-management' ); ?>" id="csv-pass-management">
            <?php 
                wp_nonce_field( "csv-pass-settings" ); 
                $voter_csv_pass = !empty($settings) ? $settings['voter_csv_password'] : '';
                $insta_csv_pass = !empty($settings) ? $settings['instagram_csv_password'] : '';
                $entry_csv_pass = !empty($settings) ? $settings['entries_csv_password'] : '';
            ?>
            <table>
                <?php
                    if ( $pagenow == 'admin.php' && $_GET['page'] == 'csv-pass-management' )
                    { 
                        if ( isset ( $_GET['tab'] ) ) 
                            $tab = $_GET['tab']; 
                        else 
                            $tab = 'entries-csv-pass'; 

                        switch ( $tab )
                        {
                            case 'entries-csv-pass' : 
                            ?>
                                <tr><td colspan="2">&nbsp;</td></tr>
                                <tr>
                                    <td width="168"><strong><label><?php _e('Old Password','cswm'); ?></label></strong></td>
                                    <td><input type="password" name="entries_csv_old_password" id="entries_csv_old_password" value=""/></td>
                                </tr>
                                <tr>
                                    <td width="168"><strong><label><?php _e('New Password','cswm'); ?></label></strong></td>
                                    <td><input type="password" name="entries_csv_password" id="entries_csv_password" value=""/></td>
                                </tr>
                                <tr>
                                    <td width="168"><strong><label><?php _e('Confirm Password','cswm'); ?></label></strong></td>
                                    <td><input type="password" name="entries_csv_confirm_password" id="entries_csv_confirm_password" value=""/></td>
                                </tr>
                                <tr><td colspan="2">&nbsp;</td></tr>
                                <tr>
                                    <td  colspan="2"><span class="description"><?php _e('The password added here will be used to export csv file of the entries submitted on a competition.','cswm'); ?></span></td>
                                </tr>
                            <?php
                            break;
                            case 'voters-csv-pass' : 
                            ?>
                                <tr><td colspan="2">&nbsp;</td></tr>
                                <tr>
                                    <td width="168"><strong><label><?php _e('Old Password','cswm'); ?></label></strong></td>
                                    <td><input type="password" name="voter_csv_old_password" id="voter_csv_old_password" value=""/></td>
                                </tr>
                                <tr>
                                    <td width="168"><strong><label><?php _e('New Password','cswm'); ?></label></strong></td>
                                    <td><input type="password" name="voter_csv_password" id="voter_csv_password" value=""/></td>
                                </tr>
                                <tr>
                                    <td width="168"><strong><label><?php _e('Confirm Password','cswm'); ?></label></strong></td>
                                    <td><input type="password" name="voter_csv_confirm_password" id="voter_csv_confirm_password" value=""/></td>
                                </tr>
                                <tr><td colspan="2">&nbsp;</td></tr>
                                <tr>
                                    <td colspan="2"><span class="description"><?php _e('The password added here will be used to export voters csv file.','cswm'); ?></span></td>
                                </tr>
                            <?php
                            break;
                            case 'insta-csv-pass' :
                            ?>
                                <tr><td colspan="2">&nbsp;</td></tr>
                                <tr>
                                    <td width="168"><strong><label><?php _e('Old Password','cswm'); ?></label></strong></td>
                                    <td><input type="password" name="instagram_csv_old_password" id="instagram_csv_old_password" value=""/></td>
                                </tr>
                                <tr>
                                    <td width="168"><strong><label><?php _e('New Password','cswm'); ?></label></strong></td>
                                    <td><input type="password" name="instagram_csv_password" id="instagram_csv_password" value=""/></td>
                                </tr>
                                <tr>
                                    <td width="168"><strong><label><?php _e('Confirm Password','cswm'); ?></label></strong></td>
                                    <td><input type="password" name="instagram_csv_confirm_password" id="instagram_csv_confirm_password" value=""/></td>
                                </tr>
                                <tr><td colspan="2">&nbsp;</td></tr>
                                <tr>
                                    <td colspan="2"><span class="description"><?php _e('The password added here will be used to export instagram user csv file.','cswm'); ?></span></td>
                                </tr>
                                <tr><td colspan="2">&nbsp;</td></tr>
                            <?php 
                            break;
                        }
                    }
                ?>                
            </table>
            <p class="submit" style="clear: both;">
                <input type="submit" name="Submit" class="button-primary" value="<?php _e('Update Password','cswm');?>" />
                <input type="hidden" name="csv-settings-submit" value="Y" />
            </p>
        </form>
    </div>
    <?php
}
add_action('wp_ajax_entry_csv_fancybox','entry_csv_fancybox');
function entry_csv_fancybox() {
    ?>
    <form method="post" id="entries-csv-form" name="entries-csv-form">
        <table width="100%">
            <tbody>
                <tr><td colspan="2"><h3><?php _e('Entries CSV Varification', 'cswm'); ?></h3></td></tr>
                <tr>
                    <td class="label">
                        <label for="password"><?php _e('Password', 'cswm'); ?></label>
                    </td>
                    <td><input type="password" name="password" id="password" value=""></td>
                </tr>
                <tr><td colspan="2"><p class="description"><?php _e('Enter password to download this csv', 'cswm'); ?></p></td></tr>
            </tbody>           
        </table>
        <?php
            $nonce = wp_create_nonce( 'entry-csv-password-nonce' );
            $url = $_REQUEST['url'];
            $competition_id = $_REQUEST['competition_id'];
            $url .= '&competition_id='.$competition_id;
            $url .= '&_wpnonce='.$nonce;
            if(isset($_REQUEST['s']) && $_REQUEST['s']!=''){
                $url .= '&s'.$_REQUEST['s'];
            }
            if(isset($_REQUEST['winners']) && $_REQUEST['winners']!=''){
                $url .= '&winners'.$_REQUEST['winners'];
            }
            echo '<input type="hidden" name="download_url" id="download_url" value="'.$url.'">';
        ?>
        <input name="varify-entry-pass" type="submit" class="button button-primary button-large" id="varify-entry-pass" value="<?php _e('Download', 'cswm'); ?>">
    </form>
    <?php
    exit();
}

add_action('wp_ajax_download_entry_csv','download_entry_csv');
function download_entry_csv()
{
    if(isset($_POST['password']) && $_POST['password']!='')
    {
        $pass = $_POST['password'];
        $settings = get_option( "csv_pass_settings_option" );
        $entry_csv_pass = !empty($settings) ? $settings['entries_csv_password'] : '';
        if(wp_check_password($pass, $entry_csv_pass))
            $_SESSION['entry_pass_varified'] = true;

        echo wp_check_password($pass, $entry_csv_pass);
    }
    exit();
}

add_action('wp_ajax_insta_csv_fancybox','insta_csv_fancybox');
function insta_csv_fancybox() {
    ?>
    <form method="post" id="insta-csv-form" name="insta-csv-form">
        <table width="100%">
            <tbody>
                <tr><td colspan="2"><h3><?php _e('Instagram Users CSV Varification', 'cswm'); ?></h3></td></tr>
                <tr>
                    <td class="label">
                        <label for="password"><?php _e('Password', 'cswm'); ?></label>
                    </td>
                    <td><input type="password" name="password" id="password" value=""></td>
                </tr>
                <tr><td colspan="2"><p class="description"><?php _e('Enter password to download this csv', 'cswm'); ?></p></td></tr>
            </tbody>           
        </table>
        <?php
            $nonce = wp_create_nonce( 'insta-csv-password-nonce' );
            $url = $_REQUEST['url'];
            $competition_id = $_REQUEST['competition_id'];
            $url .= '&competition_id='.$competition_id;
            $url .= '&_wpnonce='.$nonce;
            if(isset($_REQUEST['s']) && $_REQUEST['s']!=''){
                $url .= '&s'.$_REQUEST['s'];
            }
            if(isset($_REQUEST['winners']) && $_REQUEST['winners']!=''){
                $url .= '&winners'.$_REQUEST['winners'];
            }
            echo '<input type="hidden" name="download_url" id="download_url" value="'.$url.'">';
        ?>
        <input name="varify-insta-pass" type="submit" class="button button-primary button-large" id="varify-insta-pass" value="<?php _e('Download', 'cswm'); ?>">
    </form>
    <?php
    exit();
}

add_action('wp_ajax_download_insta_csv','download_insta_csv');
function download_insta_csv()
{
    if(isset($_POST['password']) && $_POST['password']!='')
    {
        $pass = $_POST['password'];
        $settings = get_option( "csv_pass_settings_option" );
        $instagram_csv_password = !empty($settings) ? $settings['instagram_csv_password'] : '';
        if(wp_check_password($pass, $instagram_csv_password))
            $_SESSION['insta_pass_varified'] = true;

        echo wp_check_password($pass, $instagram_csv_password);
    }
    exit();
}

add_action('wp_ajax_voter_csv_fancybox','voter_csv_fancybox');
function voter_csv_fancybox() {
    ?>
    <form method="post" id="voter-csv-form" name="voter-csv-form">
        <table width="100%">
            <tbody>
                <tr><td colspan="2"><h3><?php _e('Voters CSV Varification', 'cswm'); ?></h3></td></tr>
                <tr>
                    <td class="label">
                        <label for="password"><?php _e('Password', 'cswm'); ?></label>
                    </td>
                    <td><input type="password" name="password" id="password" value=""></td>
                </tr>
                <tr><td colspan="2"><p class="description"><?php _e('Enter password to download this csv', 'cswm'); ?></p></td></tr>
            </tbody>           
        </table>
        <?php
            $nonce = wp_create_nonce( 'voter-csv-password-nonce' );
            $url = $_REQUEST['url'];
            $competition_id = $_REQUEST['competition_id'];
            $url .= '&competition_id='.$competition_id;
            $url .= '&_wpnonce='.$nonce;
            echo '<input type="hidden" name="download_url" id="download_url" value="'.$url.'">';
        ?>
        <input name="varify-voter-pass" type="submit" class="button button-primary button-large" id="varify-voter-pass" value="<?php _e('Download', 'cswm'); ?>">
    </form>
    <?php
    exit();
}

add_action('wp_ajax_download_voter_csv','download_voter_csv');
function download_voter_csv()
{
    if(isset($_POST['password']) && $_POST['password']!='')
    {
        $pass = $_POST['password'];
        $settings = get_option( "csv_pass_settings_option" );
        $voter_csv_password = !empty($settings) ? $settings['voter_csv_password'] : '';
        if(wp_check_password($pass, $voter_csv_password))
            $_SESSION['voter_pass_varified'] = true;

        echo wp_check_password($pass, $voter_csv_password);
    }
    exit();
}

// FILTER ADDDED TO ADD SEARCH ON POST TITLE FOR SUBMITTED ENTRIES
add_filter( 'posts_where', 'title_like_posts_where', 10, 2 );
function title_like_posts_where( $where, $wp_query ) 
{
    global $wpdb;
    if(isset($wp_query->query['post_type']) && $wp_query->query['post_type']=='entries')
    {
        $post_title_like = "";
        if(isset($wp_query->query['post_title_like']) && $wp_query->query['post_title_like']!='')
        {
            $post_title_like = $wp_query->query['post_title_like'];
            $where .= ' OR (' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $post_title_like ) ) . '%\' OR ' . $wpdb->posts . '.post_content LIKE \'%' . esc_sql( $wpdb->esc_like( $post_title_like ) ) . '%\')';
        }
    }
    // ADDED TO COMPARE ENCRYPTED PHONE NUMBER (THE EXACT MATCH) META VALUE
    if(isset($wp_query->query['encrypted_phone_compare']) && $wp_query->query['encrypted_phone_compare']!='')
    {
        if(strpos($where, ".meta_key = 'user_phno'") !== false)
        {
            $tableAliasName = trim(getTableAliasName($where,".meta_key = 'user_phno'"));
            $find = ".meta_key = 'user_phno' AND ".$tableAliasName.".meta_value";
            $replace = ".meta_key = 'user_phno' AND aes_decrypt(FROM_BASE64(".$tableAliasName.".meta_value),UNHEX('".ENC_KEY."'))";
            $where = str_replace($find,$replace,$where);
        }
    }
    // ADDED TO COMPARE ENCRYPTED EMAIL ADDRESS (THE EXACT MATCH) META VALUE
    if(isset($wp_query->query['encrypted_email_compare']) && $wp_query->query['encrypted_email_compare']!='')
    {
        if(strpos($where, ".meta_key = 'user_email_address'") !== false)
        {
            $tableAliasName = trim(getTableAliasName($where,".meta_key = 'user_email_address'"));
            $find = ".meta_key = 'user_email_address' AND ".$tableAliasName.".meta_value";
            $replace = ".meta_key = 'user_email_address' AND aes_decrypt(FROM_BASE64(".$tableAliasName.".meta_value),UNHEX('".ENC_KEY."'))";
            $where = str_replace($find,$replace,$where);
        }
    }
    // ADDED TO COMPARE ENCRYPTED EMAIL ADDRESS META VALUE (FOR SEARCHING)
    if(isset($wp_query->query['encrypted_email_search_meta']) && $wp_query->query['encrypted_email_search_meta']!='')
    {
        if(strpos($where, ".meta_key = 'user_email_address'") !== false)
        {   
            $email_address = $wp_query->query['encrypted_email_search_meta'];
            $tableAliasName = trim(getTableAliasName($where,".meta_key = 'user_email_address'"));
            $find = ".meta_key = 'user_email_address' AND ".$tableAliasName.".meta_value LIKE '%".$email_address."%'";
            $replace = ".meta_key = 'user_email_address' AND aes_decrypt(FROM_BASE64(".$tableAliasName.".meta_value),UNHEX('".ENC_KEY."')) LIKE '%".$email_address."%'";
            $where = str_replace($find,$replace,$where);
        }
    }
    return $where;
}

add_action( 'pre_user_query', 'add_my_custom_queries' );
function add_my_custom_queries( $user_query ) {
    global $wpdb;
    // ADDED TO COMPARE ENCRYPTED EMAIL ADDRESS (THE EXACT MATCH) META VALUE
    if(isset($user_query->query_vars['encrypted_user_email_compare']) && $user_query->query_vars['encrypted_user_email_compare']!='')
    {
        $where = $user_query->query_where;
        if (strpos($where, ".meta_key = 'user_email'") !== false)
        {
            $tableAliasName = trim(getTableAliasName($where,".meta_key = 'user_email'"));
            $find = "= 'user_email' AND ".$tableAliasName.".meta_value";
            $replace = "= 'user_email' AND aes_decrypt(FROM_BASE64(".$tableAliasName.".meta_value),UNHEX('".ENC_KEY."'))";
            $user_query->query_where = str_replace($find,$replace,$where);
        }
    }
}
function getTableAliasName($originalText = '', $searchString = '') {
    $foundPosition = strpos($originalText,$searchString);
    $tableAliasName = '';
    if($foundPosition)
    {
        $stringWithTableNameAtEnd = substr($originalText, 0, $foundPosition);
        $spacePosition = strrpos($stringWithTableNameAtEnd," ");
        $lengthOfTableAlias = $foundPosition - $spacePosition;
        $tableAliasName = substr($originalText, $spacePosition,$lengthOfTableAlias);
    }
    return $tableAliasName;
}

// FUNCTION ADDED TO FETCH ENCRYPTED META DATA
function getDecryptedMeta($metaKey, $postID) {
    global $wpdb;
    $query = "select CAST(aes_decrypt(from_base64(meta_value),UNHEX('".ENC_KEY."')) as CHAR) AS meta_value FROM ".$wpdb->postmeta." WHERE meta_key = '".$metaKey."' AND post_id=".$postID;
    $returned_meta = $wpdb->get_var($query);
    return $returned_meta;
}

// FUNCTION ADDED TO FETCH ENCRYPTED META DATA
function getDecryptedVoteDetail($field, $voteID) {
    global $wpdb;
    $query = "select CAST(aes_decrypt(from_base64(".$field."),UNHEX('".ENC_KEY."')) as CHAR) AS ".$field." FROM ".$wpdb->vote_users." WHERE ID=".$voteID;
    $returned_meta = $wpdb->get_var($query);
    return $returned_meta;
}