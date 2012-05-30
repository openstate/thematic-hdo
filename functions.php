<?php
    
    // Unhook default Thematic functions
    function unhook_thematic_functions() {
        // Unhook header div actions
        remove_action('thematic_header','thematic_blogdescription',5);
        remove_action('thematic_header','thematic_blogtitle',3);
        // Unhook footer div actions
        remove_action('thematic_footer', 'thematic_siteinfo', 30);
    }
    add_action('init','unhook_thematic_functions');
    
    // Unhook default form styling
    function unhook_wp_style() {
        wp_deregister_style( 'contact-form-7' );
    }
    add_action('wp_print_styles','unhook_wp_style',100);
    
    // Render custom header
    function hdo_thematic_blogtitle() { ?>
        <div id="blog-title">
            <span>
                <a href="<?= home_url() ?>" title="<?= get_bloginfo('name') ?>" rel="home">
                    <img src="<?= get_stylesheet_directory_uri() . '/images/HDO_logo.png' ?>" 
                    alt="<?= get_bloginfo('description') ?>" />
                </a>
            </span>
        </div>
    <?php }
    add_action('thematic_header','hdo_thematic_blogtitle',3);
    
    // Render social access menu
    function hdo_social_access() { ?>
        <div id="social-access">
            <p>Volg ons via:</p>
            <ul>    
                <li>
                    <a href="http://www.facebook.com/Hackdeoverheid">
                        <img src="<?= get_stylesheet_directory_uri() . '/images/facebook.png' ?>" />
                    </a>
                </li>
                <li>
                    <a href="http://twitter.com/#!/hackdeoverheid">
                        <img src="<?= get_stylesheet_directory_uri() . '/images/twitter.png' ?>" />
                    </a>
                </li>
                <li>
                    <a href="http://www.hackdeoverheid.nl/feed">
                        <img src="<?= get_stylesheet_directory_uri() . '/images/rss.png' ?>" />
                    </a>
                </li>
            </ul>
        </div>
    <?php }
    add_action('thematic_header','hdo_social_access',10);
    
    // Render introduction block..
    function hdo_thematic_abovecontent() {
        
        $content = '';
        
        // ..on front page
        if ( is_home() || is_front_page() ) {
            $the_page = get_page_by_title('Home');
            $content = $the_page->post_content;
        }
        // .. on category pages
        elseif ( is_category() ) {
            $thisCat = get_category(get_query_var('cat'),false);
            $the_page = get_page_by_title($thisCat->name);
            $content = $the_page->post_content;
        }
        
        echo $content;
    }
    add_action('thematic_abovecontent', 'hdo_thematic_abovecontent', 1);
    
    // Insert index title on front page
    function hdo_thematic_abovecontent_title() {
        $content = '';
        if (is_home() || is_front_page()) {
            $content .= '<p class="index-title">';
			$content .= 'Recente blogposts';
			$content .= '</p>' . "\n";
        }
        echo $content;
    }
    add_action('thematic_abovecontent', 'hdo_thematic_abovecontent_title', 2);
    
    //Filter no-frontpage category from index loop
    function hdo_exclude_category( ){
      global $wp_query;
      $excluded_id = get_cat_ID('no-frontpage');

      // only exclude on home page
      if( is_home()) {
         $wp_query->query_vars['cat'] = '-' . $excluded_id;
      }
    }
    add_action('pre_get_posts', 'hdo_exclude_category' );
    
    // Show alternate titles on category pages
    function hdo_thematic_page_title($content) {
		
		global $post;
		
		if (is_category()) {
		        $content = '';
				$content .= '<h1 class="page-title">';
				$content .= __('Recente Blogposts:', 'thematic');
				$content .= ' <span>';
				$content .= single_cat_title('', FALSE);
				$content .= '</span></h1>' . "\n";
				$content .= '<div class="archive-meta">';
				if ( !(''== category_description()) ) : $content .= apply_filters('archive_meta', category_description()); endif;
				$content .= '</div>';
		}
		$content .= "\n";
		echo apply_filters('hdo_thematic_page_title', $content);
	}
	add_filter('thematic_page_title', 'hdo_thematic_page_title');
    
    // Show excerpt instead of full posts on front page
    function hdo_thematic_content($post) {
    	if (is_home() || is_front_page()) {
    	    $post = 'excerpt';
    	}
    	return apply_filters('hdo_thematic_content', $post);
    }
    add_filter('thematic_content', 'hdo_thematic_content');
    
    // Shorten default excerpt length
    function hdo_get_the_excerpt($excerpt) {
    	
    	$charlength = 140;

    	if ( mb_strlen( $excerpt ) > $charlength ) {
    		$subex = mb_substr( $excerpt, 0, $charlength - 5 );
    		$exwords = explode( ' ', $subex );
    		$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
    		if ( $excut < 0 ) {
    			return apply_filters('hdo_get_the_excerpt', mb_substr( $subex, 0, $excut ) . '[...]' );
    		} else {
    			return apply_filters('hdo_get_the_excerpt', $subex . '[...]');
    		}
    	} else {
    		return apply_filters('hdo_get_the_excerpt', $excerpt);
    	}
    }
    add_filter('get_the_excerpt', 'hdo_get_the_excerpt');
    
    // Restucture index loop
    function childtheme_override_content(){
        global $thematic_content_length;
	
		if ( strtolower($thematic_content_length) == 'full' ) {
			$post = get_the_content(more_text());
			$post = apply_filters('the_content', $post);
			$post = str_replace(']]>', ']]&gt;', $post);
		} elseif ( strtolower($thematic_content_length) == 'excerpt') {
			$post = '';
			$post .= get_the_excerpt();
			$post = apply_filters('the_excerpt',$post);
		} elseif ( strtolower($thematic_content_length) == 'none') {
		} else {
			$post = get_the_content(more_text());
			$post = apply_filters('the_content', $post);
			$post = str_replace(']]>', ']]&gt;', $post);
		}
		echo apply_filters('thematic_post', $post);
    }
    
    function childtheme_override_index_loop() {
		
		global $options, $blog_id;
		
		foreach ($options as $value) {
		    if (get_option( $value['id'] ) === FALSE) { 
		        $$value['id'] = $value['std']; 
		    } else {
		    	if (THEMATIC_MB) 
		    	{
		        	$$value['id'] = get_option($blog_id,  $value['id'] );
		    	}
		    	else
		    	{
		        	$$value['id'] = get_option( $value['id'] );
		    	}
		    }
		}
		
		/* Count the number of posts so we can insert a widgetized area */ $count = 1;
		while ( have_posts() ) : the_post();
		
				thematic_abovepost(); ?>

				<div id="post-<?php the_ID();
					echo '" ';
					if (!(THEMATIC_COMPATIBLE_POST_CLASS)) {
						post_class();
						echo '>';
					} else {
						echo 'class="';
						thematic_post_class();
						echo '">';
					}
					
					echo '<div id="post-entry-left">';
					
					//insert thumbnail		
					$size = apply_filters( 'thematic_post_thumb_size' , array(100,100) );
					$attr = apply_filters( 'thematic_post_thumb_attr', array('title'	=> 'Permalink to ' . $post_title));
					if ( has_post_thumbnail() ) {
    					$thumb = '<a class="entry-thumb" href="' . get_permalink() . '" title="Permalink to ' . get_the_title() . '" >' . get_the_post_thumbnail(get_the_ID(), $size, $attr) . '</a>';
    					echo $thumb;
    				}
    				
    				echo '</div>';
    				echo '<div id="post-entry-right">';
					
     				echo thematic_postheader_posttitle(); ?>
					<div class="entry-content">
<?php thematic_content(); ?>

					<?php wp_link_pages('before=<div class="page-link">' .__('Pages:', 'thematic') . '&after=</div>') ?>
					</div><!-- .entry-content -->
					<?php echo thematic_postheader_postmeta();
					thematic_postfooter(); ?>
				    </div>
				</div><!-- #post -->

			<?php 
				
				thematic_belowpost();
				
				comments_template();

				if ($count==$thm_insert_position) {
						get_sidebar('index-insert');
				}
				$count = $count + 1;
		endwhile;
	}
	
	// Use index loop on category pages
	function childtheme_override_category_loop() {
	    thematic_indexloop();
	}
    
    // Filter author and seperators from post-meta block
    function hdo_thematic_postmeta_entrydate() {
	
	    $entrydate .= '<span class="entry-date"><abbr class="published" title="';
	    $entrydate .= get_the_time(thematic_time_title()) . '">';
	    $entrydate .= get_the_time(thematic_time_display());
	    $entrydate .= '</abbr></span>';
	    
	    return apply_filters('thematic_post_meta_entrydate', $entrydate);  
	}   
    function hdo_thematic_postheader_postmeta() {
    
        $postmeta = '<div class="entry-meta">';
	    $postmeta .= hdo_thematic_postmeta_entrydate();	                   
	    $postmeta .= "</div><!-- .entry-meta -->\n";
	    
	    return apply_filters('hdo_thematic_postheader_postmeta',$postmeta);     
    }    
    add_filter('thematic_postheader_postmeta','hdo_thematic_postheader_postmeta');
    
    // Filter category, edit link and seperators from post-utility block
    function childtheme_override_postfooter_postcomments() {
        if (comments_open()) {
	        $postcommentnumber = get_comments_number();
	        if ($postcommentnumber > '1') {
	            $postcomments = ' <span class="comments-link"><a href="' . apply_filters('the_permalink', get_permalink()) . '#comments" title="' . __('Comment on ', 'thematic') . the_title_attribute('echo=0') . '">';
	            $postcomments .= get_comments_number() . __(' Comments', 'thematic') . '</a></span>';
	        } elseif ($postcommentnumber == '1') {
	            $postcomments = ' <span class="comments-link"><a href="' . apply_filters('the_permalink', get_permalink()) . '#comments" title="' . __('Comment on ', 'thematic') . the_title_attribute('echo=0') . '">';
	            $postcomments .= get_comments_number() . __(' Comment', 'thematic') . '</a></span>';
	        } elseif ($postcommentnumber == '0') {
	            $postcomments = ' <span class="comments-link"><a href="' . apply_filters('the_permalink', get_permalink()) . '#comments" title="' . __('Comment on ', 'thematic') . the_title_attribute('echo=0') . '">';
	            $postcomments .= __('Leave a comment', 'thematic') . '</a></span>';
	        }
	    } else {
	        $postcomments = ' <span class="comments-link comments-closed-link">' . __('Comments closed', 'thematic') .'</span>';
        }
    return $postcomments;	    
    }
    function hdo_thematic_postfooter($postfooter) {
        
        if ($post->post_type != 'page' && !is_single()) {
            $postfooter = '<div class="entry-utility">' . thematic_postfooter_postcomments();
            $postfooter .= "</div><!-- .entry-utility -->\n";
        }
            
        return apply_filters('hdo_thematic_postfooter',$postfooter);     
    }
    add_filter('thematic_postfooter','hdo_thematic_postfooter');
    
    // Increase featured image thumbnail size
    function hdo_thematic_post_thumb_size() {
        return apply_filters('hdo_thematic_post_thumb_size', array(300,150));
    }
    add_filter('thematic_post_thumb_size','hdo_thematic_post_thumb_size');
    
    // Add footer site-info
    function hdo_thematic_siteinfo(){ 
        $pg_id = get_page_by_title( 'Contact' );
        $pg_uri = get_page_uri ( $pg_id );
    ?>
        <img src="<?= get_stylesheet_directory_uri() . '/images/HDO_logo_klein.png' ?>" 
                    alt="Hack de Overheid" />
        <p>&copy; <?php echo date("Y"); ?></p>
        <div id="contact-link">
            <a href="<?= $pg_uri ?>">Contact</a>
        </div>
    <?php
    }
    add_action('thematic_footer', 'hdo_thematic_siteinfo', 30);
            
?>