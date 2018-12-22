<?php

########################################################################
## CODE TO REMOVE UNWANTED OPTIONS FROM THEME CUSTOMIZER
########################################################################

add_action( "customize_register", "modify_theme_customize_register" );
function modify_theme_customize_register( $wp_customize ) {

	 $wp_customize->remove_control("header_image");
	 $wp_customize->remove_control("header_video");
	 $wp_customize->remove_control("external_header_video");
	 $wp_customize->remove_section("colors");
	 $wp_customize->remove_section("background_image");
	 $wp_customize->remove_section("static_front_page");
	 $wp_customize->remove_section("theme_options");

}

########################################################################
## to creaete third widget showing in footer
########################################################################

function Footer_widgets_init() {
register_sidebar( array(
        'name'          => __( 'Footer 3', 'csat' ),
        'id'            => 'sidebar-4',
        'description'   => __( 'Add widgets here to appear in your footer.', 'csat' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
  ) );
register_sidebar( array(
        'name'          => __( 'Copyright Text Area', 'csat' ),
        'id'            => 'copyright-text-area',
        'description'   => __( 'Add widgets here to appear in your footer copyright section.', 'csat' ),
        'before_widget' => '<div id="%1$s" class="">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="sub-title">',
        'after_title'   => '</h2>',
    ) );
}
// Register sidebars by running Footer_widgets_init() on the widgets_init hook.
add_action( 'widgets_init', 'Footer_widgets_init' );

########################################################################
## To created custom post type : testimonials post type
########################################################################


add_action('init', 'create_testimonials');

function create_testimonials() {
    register_post_type('testimonials', array(
        'labels' => array(
            'name' => 'Testimonials',
            'singular_name' => 'Testimonial',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Testimonial',
            'edit' => 'Edit',
            'edit_item' => 'Edit Testimonial',
            'new_item' => 'New Testimonial',
            'view' => 'View',
            'view_item' => 'View Testimonial',
            'search_items' => 'Search Testimonials',
            'not_found' => 'No Testimonials found',
            'not_found_in_trash' => 'No Testimonials found in Trash',
        ),
        'query_var' => true,
        'public' => true,
        'menu_position' => 15,
        'supports' => array('title', 'editor'),
        'taxonomies' => array(''),
        'hierarchical' => true,
        'has_archive' => false
            )
    );
}
function creole_testimonials_listing($atts)
{
    $args = array(
                    'post_type' => 'testimonials',
                    'post_status' => 'publish',
                    'orderby' => 'ID',
                    'oder' => 'DESC'
                  );
    
    query_posts($args);
    
    if ( isset($atts['title']) ) $returnHTML .= '<div class="title">'.$atts['title'].'</div>';
    $returnHTML .= '<div class="testimonials">';
    if(have_posts())
    {
        global $wp_query; 
        $found_testimonials = $wp_query->found_posts;
        $returnHTML .=  '<div id="myCarousel" class="carousel slide" data-ride="carousel">';
                    
        if($found_testimonials)
        {
            $returnHTML .= '<ol class="carousel-indicators">';
            
            for($i=0 ; $i<$found_testimonials ; $i++)
            {
                if($i==0)
                    $returnHTML .= '<li data-target="#myCarousel" data-slide-to="'.$i.'" class="active"></li>';
                else
                    $returnHTML .= '<li data-target="#myCarousel" data-slide-to="'.$i.'"></li>';
            }
            
            $returnHTML .= '</ol>';
        }
                              
        $returnHTML .= '<div class="carousel-inner">';
        $i=0;
        while ( have_posts() ) : the_post();
                if($i==0)
                    $returnHTML .= '<div class="item active">';
                else
                    $returnHTML .= '<div class="item">';
                
                    $returnHTML .= '<div class="description">
                                       '. get_the_content() .'   
                                    </div>
                                    <div class="author">'.get_field( "reviewer" ).'</div>
                                  </div>';
                    $i++;
	   endwhile;
        
        $returnHTML .= '</div></div>';
    }
    else 
    {
        
    }
    $returnHTML .= '</div>';
    
    wp_reset_query();
    
    return $returnHTML;
}
add_shortcode('LIST-TESTIMONIALS','creole_testimonials_listing');


function creole_trusted_brand_listing($atts)
{
    $returnHTML = "";
    
    if ( isset($atts['title']) ) $returnHTML .= '<div class="title-center"><div class="title">'.$atts['title'].'</div></div>';
    
    if( have_rows('add_brand_details') ):
        
        $returnHTML .= '<div class="Contant">
                            <div class="brand-list">';
    
                        While ( have_rows('add_brand_details') ) : the_row();
                            $image = get_sub_field('image');
                            $link = get_sub_field('link');
                            
                            $size = 'thumbnail';
                            $returnHTML .= '<div class="text-left img-border-right">';
                                $returnHTML .= '<div class="barnd-logo">';
                                    
                                        if( $link ):
                                            $returnHTML .= '<a href="'.$link.'" target="_blank">';
                                        endif;

                                            $returnHTML .= '<img src="'.$image['url'].'" alt="'.$image['alt'].'" />';
                                        
                                        if( $link ):
                                            $returnHTML .= '</a>';
                                        endif;
                                    $returnHTML .= '</div>';
                                $returnHTML .= '</div>';
                            
                                
                        endwhile;
        $returnHTML .= '</div></div>';
    endif;
    
    return $returnHTML;
}
add_shortcode('LIST-TRUSTEDBRANDS','creole_trusted_brand_listing');

function creole_albert_os_images_listing()
{
    if(wp_is_mobile())
    {
        $returnHTML = "";
        if( have_rows('albert_os_section_images') ):

            $albert_os_section_images_fld = get_field_object('albert_os_section_images');
            $count = (count($albert_os_section_images_fld['value']));

            $returnHTML .=  '<div id="albertOSCarousel" class="carousel slide" data-ride="carousel">';
            if($count)
            {
                $returnHTML .= '<ol class="carousel-indicators">';
                
                for($i=0 ; $i<$count ; $i++)
                {
                    if($i==0)
                        $returnHTML .= '<li data-target="#albertOSCarousel" data-slide-to="'.$i.'" class="active"></li>';
                    else
                        $returnHTML .= '<li data-target="#albertOSCarousel" data-slide-to="'.$i.'"></li>';
                }
                
                $returnHTML .= '</ol>';
            }

            $i=0;
            $returnHTML .= '<div class="carousel-inner">';
            While ( have_rows('albert_os_section_images') ) : the_row();

                $image = get_sub_field('add_image');

                if($i==0)
                    $returnHTML .= '<div class="item active">';
                else
                    $returnHTML .= '<div class="item">';

                        $returnHTML .= '<img src="'.$image['url'].'" alt="'.$image['alt'].'"/>';

                    $returnHTML .= "</div>";

                $i++;
            endwhile;

            $returnHTML .= '</div>
                            <a class="left carousel-control" href="#albertOSCarousel" data-slide="prev">
                                <span class="fa fa-angle-left"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="right carousel-control" href="#albertOSCarousel" data-slide="next">
                                <span class="fa fa-angle-right"></span>
                                <span class="sr-only">Next</span>
                            </a>';

            $returnHTML .= '</div>';
        endif;
        
        return $returnHTML;
    }
    else 
    {
        $returnHTML = "";
        if( have_rows('albert_os_section_images') ):
            //$returnHTML .= "<div class='row'>";

            While ( have_rows('albert_os_section_images') ) : the_row();

                $image = get_sub_field('add_image');

                $returnHTML .= "<div class='col-sm-4'>";
                    $returnHTML .= '<img src="'.$image['url'].'" alt="'.$image['alt'].'" style="width:100%; margin-bottom:20px;" />';
                $returnHTML .= "</div>";
            endwhile;
            //$returnHTML .= "</div>";
        endif;
        return $returnHTML;
           
    }
}

add_shortcode('LIST-ALBERT-OS-IMAGES','creole_albert_os_images_listing');

function creole_sols_3d_sizing_section_images_listing()
{
    if(wp_is_mobile())
    {
        $returnHTML = "";
        if( have_rows('solds_3d_sizing_section_images') ):

            $solds_3d_sizing_section_images_fld = get_field_object('solds_3d_sizing_section_images');
            $count = (count($solds_3d_sizing_section_images_fld['value']));

            $returnHTML .=  '<div id="solsCarousel" class="carousel slide" data-ride="carousel">';
            if($count)
            {
                $returnHTML .= '<ol class="carousel-indicators">';
                
                for($i=0 ; $i<$count ; $i++)
                {
                    if($i==0)
                        $returnHTML .= '<li data-target="#solsCarousel" data-slide-to="'.$i.'" class="active"></li>';
                    else
                        $returnHTML .= '<li data-target="#solsCarousel" data-slide-to="'.$i.'"></li>';
                }
                
                $returnHTML .= '</ol>';
            }

            $i=0;
            $returnHTML .= '<div class="carousel-inner">';
            While ( have_rows('solds_3d_sizing_section_images') ) : the_row();

                $image = get_sub_field('add_images');

                if($i==0)
                    $returnHTML .= '<div class="item active">';
                else
                    $returnHTML .= '<div class="item">';

                        $returnHTML .= '<img src="'.$image['url'].'" alt="'.$image['alt'].'"/>';
                        
                    $returnHTML .= "</div>";

                $i++;
            endwhile;

            $returnHTML .= '</div>
                            <a class="left carousel-control" href="#solsCarousel" data-slide="prev">
                                <span class="fa fa-angle-left"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="right carousel-control" href="#solsCarousel" data-slide="next">
                                <span class="fa fa-angle-right"></span>
                                <span class="sr-only">Next</span>
                            </a>';

            $returnHTML .= '</div>';
        endif;
        
        return $returnHTML;
    }
    else 
    {
        $returnHTML = "";
        if( have_rows('solds_3d_sizing_section_images') ):
            //$returnHTML .= "<div class='row'>";

            While ( have_rows('solds_3d_sizing_section_images') ) : the_row();

                $image = get_sub_field('add_images');

                $returnHTML .= "<div class='col-sm-4'>";
                    $returnHTML .= '<img src="'.$image['url'].'" alt="'.$image['alt'].'" style="width:100%; margin-bottom:20px;" />';
                $returnHTML .= "</div>";
            endwhile;
            //$returnHTML .= "</div>";
        endif;
        return $returnHTML;
    }
}

add_shortcode('SOLS-3D-SIZING-SECTION-IMAGES','creole_sols_3d_sizing_section_images_listing');

function creole_data_card_section_images_listing()
{
    if(wp_is_mobile())
    {
        $returnHTML = "";
        if( have_rows('data_card_section_images') ):

            $data_card_section_images_fld = get_field_object('data_card_section_images');
            $count = (count($data_card_section_images_fld['value']));

            $returnHTML .=  '<div id="datacardCarousel" class="carousel slide" data-ride="carousel">';
            if($count)
            {
                $returnHTML .= '<ol class="carousel-indicators">';
                
                for($i=0 ; $i<$count ; $i++)
                {
                    if($i==0)
                        $returnHTML .= '<li data-target="#datacardCarousel" data-slide-to="'.$i.'" class="active"></li>';
                    else
                        $returnHTML .= '<li data-target="#datacardCarousel" data-slide-to="'.$i.'"></li>';
                }
                
                $returnHTML .= '</ol>';
            }

            $i=0;
            $returnHTML .= '<div class="carousel-inner">';
            While ( have_rows('data_card_section_images') ) : the_row();

                $image = get_sub_field('add_images');

                if($i==0)
                    $returnHTML .= '<div class="item active">';
                else
                    $returnHTML .= '<div class="item">';

                        $returnHTML .= '<img src="'.$image['url'].'" alt="'.$image['alt'].'"/>';
                        
                    $returnHTML .= "</div>";

                $i++;
            endwhile;

            $returnHTML .= '</div>
                            <a class="left carousel-control" href="#datacardCarousel" data-slide="prev">
                                <span class="fa fa-angle-left"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="right carousel-control" href="#datacardCarousel" data-slide="next">
                                <span class="fa fa-angle-right"></span>
                                <span class="sr-only">Next</span>
                            </a>';

            $returnHTML .= '</div>';
        endif;
        
        return $returnHTML;
    }
    else 
    {
        $returnHTML = "";
        if( have_rows('data_card_section_images') ):
            //$returnHTML .= "<div class='row'>";

            While ( have_rows('data_card_section_images') ) : the_row();

                $image = get_sub_field('add_images');

                $returnHTML .= "<div class='col-sm-4'>";
                    $returnHTML .= '<img src="'.$image['url'].'" alt="'.$image['alt'].'" style="width:100%; margin-bottom:20px;" />';
                $returnHTML .= "</div>";
            endwhile;
            //$returnHTML .= "</div>";
        endif;
        return $returnHTML;
    }
}

add_shortcode('DATA-CARD-SECTION-IMAGES-LISTING','creole_data_card_section_images_listing');

########################################################################
## To created custom post type : press_post post type
########################################################################



add_action('init', 'create_press_articles');

function create_press_articles() {
    register_post_type('press-articles', array(
        'labels' => array(
            'name' => 'Press Articles',
            'singular_name' => 'Press Article',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Press Article',
            'edit' => 'Edit',
            'edit_item' => 'Edit Press Article',
            'new_item' => 'New Press Article',
            'view' => 'View',
            'view_item' => 'View Press Article',
            'search_items' => 'Search Press Articles',
            'not_found' => 'No Press Articles found',
            'not_found_in_trash' => 'No Press Articles found in Trash',
        ),
        'query_var' => false,
        'public' => true,
        'menu_position' => 16,
        'supports' => array('title','editor','thumbnail','page-attributes'),
        'taxonomies' => array('category'),
        'hierarchical' => false,
        'has_archive' => false
            )
    );
}

add_action('init', 'creole_register_products');
function creole_register_products() 
{
    register_post_type('products', array(
        'labels' => array(
            'name' => 'Products',
            'singular_name' => 'Product',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Product',
            'edit' => 'Edit',
            'edit_item' => 'Edit Product',
            'new_item' => 'New ProductProductProducts',
            'view' => 'View',
            'view_item' => 'View ProductProducts',
            'search_items' => 'Search Products',
            'not_found' => 'No Product found',
            'not_found_in_trash' => 'No Products found in Trash',
        ),
        'query_var' => false,
        'public' => true,
        'menu_position' => 16,
        'supports' => array('title','editor','thumbnail','page-attributes','excerpt'),
        'hierarchical' => false,
        'has_archive' => false
        )
    );
}



function creole_product_listing($atts)
{
    $returnHTML = "";
    $cnt_args = array(
            'post_type' => 'products',
            'post_status' => 'publish',
            'orderby' => array(
                            'menu_order' => 'ASC',
                            'ID' => 'DESC',
                        ),
            'posts_per_page' =>-1,
            );

    $all_posts = new WP_Query($cnt_args);
    $total_counts = $all_posts->post_count;

    if($all_posts->have_posts())
    {   /* Start the Loop */
      
        $post_ids = array();
        $returnHtml = '<h2 class="section-title title-ul"> Our Products </h2>';
             $returnHtml .= ' <div class="row">';
                while ( $all_posts->have_posts() ) : $all_posts->the_post();
                    $post_id = get_the_id();
                    $post_ids[] = $post_id;
                       $returnHtml .= ' <div class="col-sm-6">';
                        $returnHtml .= '<div class ="display_product_detail" id="'.$post_id.'">';
                            $returnHtml .= '<input type="hidden" value="'.$post_id.'" id ="product_id"/>';
                            $returnHtml .= '<div>'. get_the_post_thumbnail(). '</div>';
                            $returnHtml .= '<div class="title">'. get_the_title(). '</div>';
                            $returnHtml .= '<div class="desc">'. get_the_excerpt(). '</div>';
                        $returnHtml .= '</div>';
                       $returnHtml .= ' </div>';
                endwhile; // End of the loop.
            $returnHtml .= ' </div>'; 



            if(sizeof($post_ids))
            {
                foreach ($post_ids as $key => $value) {
                    $returnHtml .= '<div class="modal fade product-modal-popup" id="myModal_'.$value.'" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                    <div class="modal-header">
                                      <button type="button" class="close" data-dismiss="modal">&times;
                                        </button>
                                    </div>
                                    <div class="modal-body">'; 
                                    $image = get_field('product_model_thumbnail',$value);
                                            $size ='full';
                                            if($image)
                                            {
                                                $returnHtml .= wp_get_attachment_image( $image, $size );
                                            }   
                                    $returnHtml .= '<div class="title">'. get_the_title($value). '</div>';
                                    $returnHtml .= '<div>'. get_content($value). '</div>';
                                    $returnHtml .= '</div>
                            </div>
                       </div></div>';
                }
            }
           return  $returnHtml;
    }
    wp_reset_postdata();
}

add_shortcode('LIST-ORTHOTICS-PRODUCTS','creole_product_listing');

?>