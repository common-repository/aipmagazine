<?php
/**
 * Misc helper functions for AipMagazine
 *
 * @package AipMagazine
 * @since 1.0.0
 */

if ( !function_exists( 'get_newest_aip_magazine_issue_id' ) ) { 

	/**
	 * Get newest AipMagazine issue
	 *
	 * @since 1.0.0
	 *
	 * @param string $orderby 
	 * @return int $id
	 */
	function get_newest_aip_magazine_issue_id( $parent_id , $orderby = 'issue_order' ) {
		
		$issues = array();
		$count = 0;

		$aip_magazine_issues = get_terms( 'aip_magazine_issue', array('hide_empty' => 0) );
						
		foreach ( $aip_magazine_issues as $issue ) {
				
			$issue_meta = get_option( 'aip_magazine_issue_' . $issue->term_id . '_meta' );
			
			// If issue is not a Draft, add it to the archive array;
			if (
                !empty( $issue_meta )
                && !empty( $issue_meta['issue_status'] )
				&& ( 'Draft' !== $issue_meta['issue_status'] /*|| current_user_can( apply_filters( 'see_aip_magazine_draft_issues', 'manage_issues' ) )*/ )
                && (( $parent_id == $issue->parent ) || ( $parent_id == 'all' ))
                ) {
				
				switch( $orderby ) {
					
					case "issue_order":
						if ( !empty( $issue_meta['issue_order'] ) )
							$issues[ $issue_meta['issue_order'] ] = $issue->term_id;
						else
							$issues[ '-' . ++$count ] = $issue->term_id;
							
						break;
						
					case "name":
						$issues[ $issue_meta['name'] ] = $issue->term_id;
						break;
					
					case "term_id":
						$issues[ $issue->term_id ] = $issue->term_id;
						break;
					
				}
					 
			} else {
				$issues[ '-' . ++$count ] = $issue->term_id;
			}
			
		}
		
		krsort( $issues );
		
		return array_shift( $issues );
		
	}
	
}

if ( !function_exists( 'get_aip_magazine_issue_meta' ) ) { 
	
	/**
	 * Get issue meta information, assumes latest issue if no id supplied
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Issue ID 
	 * @return mixed Value set for the issue meta option.
	 */
	function get_aip_magazine_issue_meta( $parent_id, $id = false ) {
	
		if ( !$id ) {
				
			return get_option( 'aip_magazine_issue_' . get_newest_aip_magazine_issue_id($parent_id) . '_meta' );
			
		} else {
		
			return get_option( 'aip_magazine_issue_' . $id . '_meta' );
			
		}
		
	}

}

if ( !function_exists( 'get_aip_magazine_issue_cover' ) ) { 
	
	/**
	 * Get issue cover image, assumes latest issue if no id supplied
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Issue ID 
	 * @return string URL of cover image
	 */
	function get_aip_magazine_issue_cover( $parent_id, $id = false ) {
	
		if ( !$id ) {
					
			$issue_meta = get_option( 'aip_magazine_issue_' . get_newest_aip_magazine_issue_id($parent_id) . '_meta' );
			
			return $issue_meta['cover_image'];
			
		} else {
	
			$issue_meta = get_option( 'aip_magazine_issue_' . $id . '_meta' );
			
			return $issue_meta['cover_image'];
			
		}
		
	}

}

if ( !function_exists( 'get_aip_magazine_issue_slug' ) ) { 
	
	/**
	 * Get issue slug, assumes latest issue if no id supplied
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Issue ID 
	 * @return string issue slug
	 */
	function get_aip_magazine_issue_slug( $parent_id, $id = false  ) {
	
		if ( !$id ) {
			
			$issue = get_term_by( 'id', get_newest_aip_magazine_issue_id($parent_id), 'aip_magazine_issue' );
						
		} else {
	
			$issue = get_term_by( 'id', $id, 'aip_magazine_issue' );
			
		}
		
		return ( ( is_object( $issue ) && !empty( $issue->slug ) ) ? $issue->slug : '' );
		
	}

}

if ( !function_exists( 'get_aip_magazine_issue_title' ) ) { 
	
	/**
	 * Get issue title, assumes latest issue if no id supplied
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Issue ID 
	 * @return string issue name
	 */
	function get_aip_magazine_issue_title( $parent_id, $id = false ) {
	
		if ( !$id ) {
	
			$issue = get_term_by( 'id', get_newest_aip_magazine_issue_id($parent_id), 'aip_magazine_issue' );
			
			return $issue->name;
			
		} else {
	
			$issue = get_term_by( 'id', $id, 'aip_magazine_issue' );
			
			return $issue->name;
			
		}
		
	}

}

if ( !function_exists( 'get_active_aip_magazine_issue' ) ) { 

	/**
	 * Gets active issue, set by latest issue or by cookie if user selects a specific issue
	 *
	 * @since 1.0.0
	 *
	 * @return string issue slug
	 */
	function get_active_aip_magazine_issue($parent_id, $shortCode=false) {
	
		$issue_slug = false;
	
		if ( !empty( $_COOKIE['aip_magazine_issue'] ) && $shortCode) {
			$issue = get_term_by( 'slug', sanitize_key($_COOKIE['aip_magazine_issue']), 'aip_magazine_issue' );
			if ( !empty ( $issue ) ) {
				$issue_meta = get_option( 'aip_magazine_issue_' . $issue->term_id . '_meta' );
				if ( !empty( $issue_meta ) && !empty( $issue_meta['issue_status'] ) 
					&& ( 'Live' === $issue_meta['issue_status'] || current_user_can( apply_filters( 'see_aip_magazine_draft_issues', 'manage_issues' ) ) ) ) {
					$issue_slug = sanitize_key($_COOKIE['aip_magazine_issue']);
				}
			}
		} else if ( !empty( $_GET['issue'] ) ) {
			$issue = get_term_by( 'slug', sanitize_key($_GET['issue']), 'aip_magazine_issue' );
			if ( !empty ( $issue ) ) {
				$issue_meta = get_option( 'aip_magazine_issue_' . $issue->term_id . '_meta' );
				if ( !empty( $issue_meta ) && !empty( $issue_meta['issue_status'] ) 
					&& ( 'Live' === $issue_meta['issue_status'] || current_user_can( apply_filters( 'see_aip_magazine_draft_issues', 'manage_issues' ) ) ) ) {
					$issue_slug = sanitize_key($_GET['issue']);
				}
			}
		}
		
		if ( empty( $issue_slug ) ) {
			$issue_slug = get_aip_magazine_issue_slug($parent_id);
		}
		
		return $issue_slug;
	}

}

if ( !function_exists( 'set_aip_magazine_cookie' ) ) { 

	/**
	 * Sets AipMagazine issue cookie
	 *
	 * @since 1.0.0
	 */
	function set_aip_magazine_cookie() {
		
		if ( !empty( $_GET['issue'] ) ) {
		
			$_COOKIE['aip_magazine_issue'] = sanitize_key($_GET['issue']);
			setcookie( 'aip_magazine_issue', sanitize_key($_GET['issue']), time() + 3600, '/' );
			
		} else {
		
			global $post;
			
			$aip_magazine_settings = get_aip_magazine_settings();
				
			if ( is_page( $aip_magazine_settings['page_for_articles_active_issue'] ) ) {

                $_COOKIE['aip_magazine_issue'] = get_aip_magazine_issue_slug('all');
				setcookie( 'aip_magazine_issue', sanitize_key($_COOKIE['aip_magazine_issue']), time() + 3600, '/' );

            } else if ( !empty( $post->post_type ) && 'aip_article' != $post->post_type ) {
			
				unset( $_COOKIE['aip_magazine_issue'] );
				setcookie( 'aip_magazine_issue', '', 1, '/' );

            } else if ( is_single() && !empty( $post->post_type ) && 'aip_article' == $post->post_type ) {
			
				$terms = wp_get_post_terms( $post->ID, 'aip_magazine_issue' );
				if ( !empty( $terms ) ) {
					$_COOKIE['aip_magazine_issue'] = $terms[0]->slug;
					setcookie( 'aip_magazine_issue', sanitize_key($_COOKIE['aip_magazine_issue']), time() + 3600, '/' );
				}		
				
			} else if ( taxonomy_exists( 'aip_magazine_issue' ) ) {
				
				$_COOKIE['aip_magazine_issue'] = get_query_var( 'aip_magazine_issue' );
				setcookie( 'aip_magazine_issue', sanitize_key($_COOKIE['aip_magazine_issue']), time() + 3600, '/' );

			}
			
		}
	
	}
	add_action( 'wp', 'set_aip_magazine_cookie' );

}

if ( !function_exists( 'aip_magazine_replacements_args' ) ) {

	/**
	 * Replaces variables with WordPress content
	 *
	 * @since 1.0.0
	 *
	 * @param int $id User ID
	 */
	function aip_magazine_replacements_args( $string, $post ) {
		
		$aip_magazine_settings = get_aip_magazine_settings();
		
		if ( !empty( $aip_magazine_settings['use_wp_taxonomies'] ) ) {
			
			$tags = 'post_tag';
			$cats = 'category';	
			
		} else {

			$tags = 'aip_magazine_issue_tags';
			$cats = 'aip_magazine_issue_categories';
			
		}
		
		$string = str_ireplace( '%TITLE%', get_the_title(), $string );
		$string = str_ireplace( '%URL%', apply_filters( 'aip_magazine_article_url', get_permalink( $post->ID ), $post->ID ), $string );
		
		if ( preg_match( '/%CATEGORY\[?(\d*)\]?%/i', $string, $matches ) ) {
			
			$post_cats = get_the_terms( $post->ID, $cats );
			$categories = '';
			
			if ( $post_cats && !is_wp_error( $post_cats ) ) :
			
				if ( !empty( $matches[1] ) )
					$max_cats = $matches[1];
				else
					$max_cats = 0;
					
				$cat_array = array();

				$count = 1;
				foreach ( $post_cats as $post_cat ) {
					
					$cat_array[] = $post_cat->name;
					
					if ( 0 != $max_cats && $max_cats <= $count )
						break;
						
					$count++;
					
				}
						
				$categories = join( ", ", $cat_array );
					
			endif;
				
			$string = preg_replace( '/%CATEGORY\[?(\d*)\]?%/i', $categories, $string );	
					
		}
		
		if ( preg_match( '/%TAG\[?(\d*)\]?%/i', $string, $matches ) ) {
			
			$post_tags = get_the_terms( $post->ID, $tags );
			$tag_string = '';
			
			if ( $post_tags && !is_wp_error( $post_tags ) ) :
			
				if ( !empty( $matches[1] ) )
					$max_tags = $matches[1];
				else
					$max_tags = 0;	
					
				$cat_array = array();

				$count = 1;
				foreach ( $post_tags as $post_tag ) {
					
					$cat_array[] = $post_tag->name;
					
					if ( 0 != $max_tags && $max_tags <= $count )
						break;
						
					$count++;
					
				}
						
				$tag_string = join( ", ", $cat_array );
					
			endif;
				
			$string = preg_replace( '/%TAG\[?(\d*)\]?%/i', $tag_string, $string );	
					
		}
		
		if ( preg_match( '/%TEASER%/i', $string, $matches ) ) {
			
			if ( $teaser = get_post_meta( $post->ID, '_teaser_text', true ) ) 
				$string = preg_replace( '/%TEASER%/i', $teaser, $string );	
			else
				$string = preg_replace( '/%TEASER%/i', '%EXCERPT%', $string );	// If no Teaser Text exists, try to get an excerpt
					
		}
		
		if ( preg_match( '/%EXCERPT\[?(\d*)\]?%/i', $string, $matches ) ) {
			
			if ( empty( $post->post_excerpt ) )
				$excerpt = get_the_content();
			else
				$excerpt = $post->post_excerpt;
			
			$excerpt = strip_shortcodes( $excerpt );
			$excerpt = apply_filters( 'the_content', $excerpt );
			$excerpt = str_replace( ']]>', ']]&gt;', $excerpt );
			
			if ( !empty( $matches[1] ) )
				$excerpt_length = $matches[1];
			else
				$excerpt_length = apply_filters('excerpt_length', 55);
					
			$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
			$excerpt = wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );
				
			$string = preg_replace( '/%EXCERPT\[?(\d*)\]?%/i', $excerpt, $string );	
					
		}
		
		if ( preg_match( '/%CONTENT%/i', $string, $matches ) ) {
		
			$content = get_the_content();
			$content = apply_filters( 'the_content', $content );
    			$content = str_replace( ']]>', ']]&gt;', $content );
			$string = preg_replace( '/%CONTENT%/i', $content, $string );	
					
		}
		
		if ( preg_match( '/%FEATURE_IMAGE%/i', $string, $matches ) ) {
		
			$image = get_the_post_thumbnail( $post->ID );
			$string = preg_replace( '/%FEATURE_IMAGE%/i', $image, $string );	
					
		}
		
		if ( preg_match( '/%AIPMAGAZINE_FEATURE_THUMB%/i', $string, $matches ) ) {
		
			$image = get_the_post_thumbnail( $post->ID, 'aip_magazine-featured-thumb-image' );
			$string = preg_replace( '/%AIPMAGAZINE_FEATURE_THUMB%/i', $image, $string );	
					
		}
		
		if ( preg_match( '/%BYLINE%/i', $string, $matches ) ) {

			$author_name = get_aip_magazine_author_name( $post );
			
			$byline = sprintf( __( 'By %s', 'aip_magazine' ), apply_filters( 'aip_magazine_author_name', $author_name, $post->ID ) );
				
			$string = preg_replace( '/%BYLINE%/i', $byline, $string );	
					
		}

		if ( preg_match( '/%DATE%/i', $string, $matches ) ) {

			$post_date = get_the_date( '', $post->ID );
			$string = preg_replace( '/%DATE%/i', $post_date, $string );	
					
		}
		
		$string = apply_filters( 'aip_magazine_custom_replacement_args', $string, $post );
		
		return stripcslashes( $string );
		
	}

}

if ( !function_exists( 'get_aip_magazine_authors' ) ) {

	/**
	 * Function to get AipMagazine Article's Authors
	 *
	 * @since 1.0.0
	 *
	 * @param object WordPress Post/Article object
	 * @param $string value to show or hide link in output,
	 * @return string Value set for the aip_magazine options.
	 */
	function get_aip_magazine_authors( $article ) {

		// restituisco gli autori separati da ,
		$authors ='';
		if ( get_post_meta( $article->ID, '_autori', true )!=null ){
			$authors_array = explode( ", ", get_post_meta( $article->ID, '_autori', true ) );
			$authors = implode(', ', $authors_array);
		}

		return $authors;

	}

}

if ( !function_exists( 'get_aip_magazine_author_name' ) ) {

	/**
	 * Function to get Article's Author Name
	 *
	 * @since 1.0.0
	 *
	 * @param object WordPress Post/Article object
	 * @param $string value to show or hide link in output, 
	 * @return string Value set for the aip_magazine options.
	 */
	function get_aip_magazine_author_name( $article, $hide_link = false ) {
		
		$aip_magazine_settings = get_aip_magazine_settings();
	
		if ( !empty( $aip_magazine_settings['aip_magazine_author_name'] ) ) {
			
			$author_name = get_post_meta( $article->ID, '_aip_magazine_author_name', true );
		
		} else {
		
			if ( 'user_firstlast' == $aip_magazine_settings['display_byline_as'] ) {
				
				if ( ( $first_name = get_the_author_meta( 'user_firstname', $article->post_author ) ) && ( $last_name = get_the_author_meta( 'user_lastname', $article->post_author ) ) )
					$author_name = $first_name . ' ' . $last_name;
				else
					$author_name = '';
			
			} else {
				
				$author_name = get_the_author_meta( $aip_magazine_settings['display_byline_as'], $article->post_author );
						
			}
			
			$author_name = ( !empty( $author_name ) ) ? $author_name : get_the_author_meta( 'display_name', $article->post_author );

			if ( !$hide_link ) {
				$author_name = '<a class="url fn n" href="' . esc_url( get_author_posts_url( $article->post_author ) ) . '" title="' . esc_attr( $author_name ) . '" rel="me">' . $author_name . '</a>';
			} 
			
		}
		
		return $author_name;
		
	}
	
}

if ( !function_exists( 'get_aip_magazine_settings' ) ) {

	/**
	 * Helper function to get AipMagazine settings for current site
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Value set for the aip_magazine options.
	 */
	function get_aip_magazine_settings() {
	
		global $dl_plugin_aip_magazine;
		
		return $dl_plugin_aip_magazine->get_settings();
		
	}
	
}

if ( !function_exists( 'update_aip_magazine_settings' ) ) {

	/**
	 * Helper function to get AipMagazine settings for current site
	 *
	 * @since 1.0.0
	 *
	 * @return mixed Value set for the aip_magazine options.
	 */
	function update_aip_magazine_settings( $settings ) {
	
		global $dl_plugin_aip_magazine;
		
		$dl_plugin_aip_magazine->update_settings( $settings );
		
	}
	
}

if ( !function_exists( 'default_issue_content_filter' ) ) {

	/**
	 * Default content filter, sets AipMagazine Page for Articles to default shortcode content if no content exists for page
	 *
	 * @since 1.0.7
	 *
	 * @return string new content.
	 */
	function default_issue_content_filter( $content ) {
		
		global $post;
		
		$aip_magazine_settings = get_aip_magazine_settings();
		
		if ( !empty( $post ) ) {
			if ( $post->ID == $aip_magazine_settings['page_for_articles_active_issue'] /*&& empty( $content )*/ ) {
				$content .= '[aip_magazine_featured_thumbnails max_images="3"] [aip_magazine_articles]';

			} else if ( $post->ID == $aip_magazine_settings['page_for_archives']){// && !empty( $content ) ) {
				$content .= '[aip_magazine_archives orderby="issue_order"]';
			}else if ( $post->ID == $aip_magazine_settings['page_for_articles']){// && !empty( $content ) ) {
                $content .= '[aip_magazine_articles issue="'.sanitize_key($_REQUEST['issue']).'"]';
            }

		}
		
		return $content;
		
	}
	add_filter( 'the_content', 'default_issue_content_filter', 5 );
	
}


if ( !function_exists( 'aip_magazine_api_request' ) ) { 

	/**
	 * Helper function used to send API requests to AipMagazine.com
	 *
	 * HT: Glenn Ansley @ iThemes.com
	 *
	 * @since 1.2.0
	 *
	 * @param string $action Action to pass to API request
	 * @param array $args Arguments to pass to API request
	 */
    function aip_magazine_api_request( $action, $args ) { 
	
		global $dl_plugin_aip_magazine;
	
		return $dl_plugin_aip_magazine->aip_magazine_api_request( $action, $args );
	
    }   
	
}

if ( !function_exists( 'wp_print_r' ) ) { 

	/**
	 * Helper function used for printing out debug information
	 *
	 * HT: Glenn Ansley @ iThemes.com
	 *
	 * @since 1.1.6
	 *
	 * @param int $args Arguments to pass to print_r
	 * @param bool $die TRUE to die else FALSE (default FALSE)
	 */
    function wp_print_r( $args, $die = false ) { 
	
        $echo = '<pre>' . print_r( $args, true ) . '</pre>';
		
        if ( $die ) die( $echo );
        	else echo $echo;
		
    }   
	
}

if ( !function_exists( 'aip_magazine_dropdown_categories' ) ) {
	
	/**
	 * Display or retrieve the HTML dropdown list of article categories.
	 * Adapted from WordPress' "wp_dropdown_categories"
	 *
	 * The list of arguments is below:
	 *     'show_option_all' (string) - Text to display for showing all categories.
	 *     'show_option_none' (string) - Text to display for showing no categories.
	 *     'orderby' (string) default is 'ID' - What column to use for ordering the
	 * categories.
	 *     'order' (string) default is 'ASC' - What direction to order categories.
	 *     'show_count' (bool|int) default is 0 - Whether to show how many posts are
	 * in the category.
	 *     'hide_empty' (bool|int) default is 1 - Whether to hide categories that
	 * don't have any posts attached to them.
	 *     'child_of' (int) default is 0 - See {@link get_categories()}.
	 *     'exclude' (string) - See {@link get_categories()}.
	 *     'echo' (bool|int) default is 1 - Whether to display or retrieve content.
	 *     'depth' (int) - The max depth.
	 *     'tab_index' (int) - Tab index for select element.
	 *     'name' (string) - The name attribute value for select element. Defaults to aip_magazine_issue_cat.
	 *     'id' (string) - The ID attribute value for select element. Defaults to name if omitted.
	 *     'class' (string) - The class attribute value for select element.
	 *     'selected' (int) - Which category ID is selected.
	 *     'taxonomy' (string) - The name of the taxonomy to retrieve. Defaults to aip_magazine_issue_categories.
	 *
	 * The 'hierarchical' argument, which is disabled by default, will override the
	 * depth argument, unless it is true. When the argument is false, it will
	 * display all of the categories. When it is enabled it will use the value in
	 * the 'depth' argument.
	 *
	 * @since 1.2.6 
	 *
	 * @param string|array $args Optional. Override default arguments.
	 * @return string HTML content only if 'echo' argument is 0.
	 */
	function aip_magazine_dropdown_categories( $args = '' ) {
		$defaults = array(
			'show_option_all' => '',
			'show_option_none' => '',
			'orderby' => 'id', 
			'order' => 'ASC',
			'show_count' => 0,
			'hide_empty' => 1, 
			'child_of' => 0,
			'exclude' => '', 
			'echo' => 1,
			'selected' => 0, 
			'hierarchical' => 0,
			'name' => 'aip_magazine_issue_cat', 
			'id' => '',
			'class' => 'postform', 
			'depth' => 0,
			'tab_index' => 0, 
			'taxonomy' => 'aip_magazine_issue_categories',
			'hide_if_empty' => false
		);
	
		$defaults['selected'] = ( is_category() ) ? get_query_var( 'cat' ) : 0;
	
		// Back compat.
		if ( isset( $args['type'] ) && 'link' == $args['type'] ) {
			_deprecated_argument( __FUNCTION__, '3.0', '' );
			$args['taxonomy'] = 'link_category';
		}
	
		$r = wp_parse_args( $args, $defaults );
	
		if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
			$r['pad_counts'] = true;
		}
	
		extract( $r );
	
		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 )
			$tab_index_attribute = " tabindex=\"$tab_index\"";
	
		$categories = get_terms( $taxonomy, $r );
		$name = esc_attr( $name );
		$class = esc_attr( $class );
		$id = $id ? esc_attr( $id ) : $name;
	
		if ( ! $r['hide_if_empty'] || ! empty($categories) )
			$output = "<select name='$name' id='$id' class='$class' $tab_index_attribute>\n";
		else
			$output = '';
	
		if ( empty($categories) && ! $r['hide_if_empty'] && !empty($show_option_none) ) {
			$show_option_none = apply_filters( 'list_cats', $show_option_none );
			$output .= "\t<option value='-1' selected='selected'>$show_option_none</option>\n";
		}
	
		if ( ! empty( $categories ) ) {
	
			if ( $show_option_all ) {
				$show_option_all = apply_filters( 'list_cats', $show_option_all );
				$selected = ( '0' === strval($r['selected']) ) ? " selected='selected'" : '';
				$output .= "\t<option value='0'$selected>$show_option_all</option>\n";
			}
	
			if ( $show_option_none ) {
				$show_option_none = apply_filters( 'list_cats', $show_option_none );
				$selected = ( '-1' === strval($r['selected']) ) ? " selected='selected'" : '';
				$output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
			}
	
			if ( $hierarchical )
				$depth = $r['depth'];  // Walk the full depth.
			else
				$depth = -1; // Flat.
	
			$output .= walk_aip_magazine_category_dropdown_tree( $categories, $depth, $r );
		}
	
		if ( ! $r['hide_if_empty'] || ! empty($categories) )
			$output .= "</select>\n";
	
		$output = apply_filters( 'aip_magazine_dropdown_cats', $output );
	
		if ( $echo )
			echo $output;
	
		return $output;
	}

}

if ( !function_exists( 'walk_aip_magazine_category_dropdown_tree' ) ) {
		
	/**
	 * Retrieve HTML dropdown (select) content for category list.
	 * Adapted from WordPress' "walk_category_dropdown_tree"
	 *
	 * @uses Walker_AipMagazineCategoryDropdown to create HTML dropdown content.
	 * @since 1.2.6 
	 * @see Walker_AipMagazineCategoryDropdown::walk() for parameters and return description.
	 */
	function walk_aip_magazine_category_dropdown_tree() {
		$args = func_get_args();
		// the user's options are the third parameter
		if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
			$walker = new Walker_AipMagazineCategoryDropdown;
		else
			$walker = $args[2]['walker'];
	
		return call_user_func_array(array( &$walker, 'walk' ), $args );
	}

}

if ( !function_exists( 'get_aip_magazine_article_excerpt' ) ) { 
	
	/**
	 * Get article excerpt by id, for use outside of the loop
	 *
	 * @since 1.2.12
	 *
	 * @param int $id Article ID 
	 * @return excerpt for the article
	 */
	function get_aip_magazine_article_excerpt( $id = false ) {
	
		if ( !$id ) {
				
			return;
			
		} else {

			$the_article = get_post($id);
			$the_excerpt = $the_article->post_excerpt;

			return $the_excerpt;
			
		}
		
	}

}
