<?php
/**
 * Registers AipMagazine Widgets
 *
 * @package AipMagazine
 * @since 1.0.0
 */

/**
 * Register our widgets classes with WP
 *
 * @since 1.0.0
 */
function register_aip_magazine_widgets() {
	
	$settings = get_aip_magazine_settings();
	
	register_widget( 'AipMagazine_Active_Issue' );
	register_widget( 'AipMagazine_Article_List' );
    register_widget( 'AipMagazine_Article_Categories' );
		
}
  add_action( 'widgets_init', 'register_aip_magazine_widgets' );

/**
 * This class registers and returns the Cover Image Widget
 *
 * @since 1.0.0
 */
class AipMagazine_Active_Issue extends WP_Widget {
	
	/**
	 * Set's widget name and description
	 *
	 * @since 1.0.0
	 */
	function AipMagazine_Active_Issue() {
		
		$widget_ops = array( 'classname' => 'aip_magazine_active_issue', 'description' => __( 'Displays the active AipMagazine Issue details', 'aip_magazine' ) );
        parent::__construct( 'AipMagazine_Active_Issue', __( 'AipMagazine Active Issue', 'aip_magazine' ), $widget_ops );
	
	}
	
	/**
	 * Displays the widget on the front end
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		
		extract( $args );
	
		$aip_magazine_settings = get_aip_magazine_settings();
		$issue = get_active_aip_magazine_issue(sanitize_text_field($instance['journal_displayed']));
        $get_term_by = get_term_by( 'slug', $issue, 'aip_magazine_issue' );

        if ( !empty($get_term_by)) {
			$term = get_term_by( 'slug', $issue, 'aip_magazine_issue' );
			$meta_options = get_option( 'aip_magazine_issue_' . $term->term_id . '_meta' );

			$title = apply_filters('active_issue_widget_title', empty( $instance['title'] ) ? '' : sanitize_text_field($instance['title']), $instance, $this->id_base);

			$out = '';

            if ( !empty( $meta_options['external_link'] ) ){
				$issue_url = $meta_options['external_link'];
            }else {
				if ( 0 == $aip_magazine_settings['page_for_articles_active_issue'] ) {
					$article_page = get_bloginfo('wpurl') . '/' . apply_filters('aip_magazine_page_for_articles', 'article/');
				}
				else {
					$article_page = get_page_link($aip_magazine_settings['page_for_articles_active_issue']);
				}

				$page_for_articles = $aip_magazine_settings['page_for_articles'];
				if ($page_for_articles == 0){
					$issue_url = get_term_link($term->slug, $term->taxonomy);
				}
				else {
					$issue_url = get_page_link($aip_magazine_settings['page_for_articles']) . '?issue=' . $issue;
				}

				if ( !empty( $aip_magazine_settings['use_issue_tax_links'] ) || is_wp_error( $issue_url ) ) {
					$issue_url = add_query_arg( 'issue', $issue, $article_page );
				}
            }

			if ( 'on' == $instance['display_issue_name'] ) {
				if ( !empty( $meta_options['issue_status'] ) )  {
					if ( 'PDF Archive' == $meta_options['issue_status'] ) {
						$out .= '<p class="aip_magazine_widget_issue_name"><a href="'.apply_filters( 'aip_magazine_pdf_attachment_url', wp_get_attachment_url( $meta_options['pdf_version'] ), $meta_options['pdf_version'] ).'" target="_blank" >' . $term->name . '</a></p>';
					}else{
						if ( !empty( $meta_options['external_link'] ) ) {
							$out .= '<p class="aip_magazine_widget_issue_name"><a href="' .  esc_url($issue_url) . '" target="_blank">' . $term->name . '</a></p>';
                        }else{
							$out .= '<p class="aip_magazine_widget_issue_name"><a href="' . apply_filters( 'aip_magazine_issue_url', $issue_url, $issue, $meta_options ) . '">' . $term->name . '</a></p>';
                        }
					}
				}
			}

			if ( 'on' == $instance['display_issue_cover'] ) {
				if ( !empty( $meta_options['cover_image'] ) ){
					if ( 'PDF Archive' == $meta_options['issue_status'] ) {
						if ( !empty( $meta_options['pdf_version'] ) ){
							$out .= '<p class="aip_magazine_widget_issue_cover_image"><a href="'.apply_filters( 'aip_magazine_pdf_attachment_url', wp_get_attachment_url( $meta_options['pdf_version'] ), $meta_options['pdf_version'] ).'" target="_blank"  style="float: left;">' . wp_get_attachment_image( $meta_options['cover_image'], 'aip_magazine-cover-image' ) . '</a></p>';
						}else{
							$out .= '<p class="aip_magazine_widget_issue_cover_image">' . wp_get_attachment_image( $meta_options['cover_image'], 'aip_magazine-cover-image' ) . '</p>';
						}
					}else{
						if ( !empty( $meta_options['external_link'] ) ) {
							$out .= '<p class="aip_magazine_widget_issue_cover_image"><a href="' . esc_url($issue_url ). '" target="_blank" style="float: left;">' . wp_get_attachment_image( $meta_options['cover_image'], 'aip_magazine-cover-image' ) . '</a></p>';
						}else{
							$out .= '<p class="aip_magazine_widget_issue_cover_image"><a href="' . apply_filters( 'aip_magazine_issue_url', $issue_url, $issue, $meta_options ) . '" style="float: left;">' . wp_get_attachment_image( $meta_options['cover_image'], 'aip_magazine-cover-image' ) . '</a></p>';
						}
					}
				}else{
					if ( !empty( $meta_options['issue_status'] ) )  {
						if ( ( 'PDF Archive' == $meta_options['issue_status'] ) || ( 'Solo PDF' == $meta_options['issue_status'] ) || ( 'Sólo PDF' == $meta_options['issue_status'] ) ){
							if ( !empty( $meta_options['pdf_version'] ) ){
								$out .= '<p class="aip_magazine_widget_issue_cover_image"><a href="'.apply_filters( 'aip_magazine_pdf_attachment_url', wp_get_attachment_url( $meta_options['pdf_version'] ), $meta_options['pdf_version'] ).'" target="_blank" ><img src="' . $aip_magazine_settings['default_issue_image'] . '" /></a></p>';
							}else{
								$out .= '<p class="aip_magazine_widget_issue_cover_image"><img src="' . $aip_magazine_settings['default_issue_image'] . '" /></p>';
							}
						}else{
							$out .= '<p class="aip_magazine_widget_issue_cover_image"><img src="' . $aip_magazine_settings['default_issue_image'] . '" /></p>';
						}
					}
				}
			}

			if ( 'on' == $instance['display_pdf_link'] ) {
				if ( !empty( $meta_options['pdf_version'] ) ) {
					$out .= '<p><a class="aip_magazine_widget_issue_pdf_link" target="_blank" href="' . apply_filters( 'aip_magazine_pdf_attachment_url', wp_get_attachment_url( $meta_options['pdf_version'] ), $meta_options['pdf_version'] ) . '">' . $aip_magazine_settings['pdf_title'] . '</a></p>';
                }else if ( !empty( $meta_options['external_pdf_link'] ) ) {
					$out .= '<p><a class="aip_magazine_widget_issue_pdf_link" target="_blank" href="' . apply_filters( 'aip_magazine_pdf_link_url', $meta_options['external_pdf_link'] ) . '">' . $aip_magazine_settings['pdf_title'] . '</a></p>';
                }
			}

			if ( ! empty( $out ) ) {

				echo $before_widget;
				if ( $title) {
					echo $before_title . $title . $after_title;
				}
				echo '<div class="aip_magazine_active_list_widget">';
				echo $out;
				echo '</div>';
				echo $after_widget;

			}


		}

	
	}

	/**
	 * Saves the widgets options on submit
	 *
	 * @since 1.0.0
	 * 
	 * @param array $new_instance
	 * @param array $old_isntance
	 */
	function update( $new_instance, $old_instance ) {
		
		$instance 							= $old_instance;
		$instance['title'] 					= $new_instance['title'];
		$instance['journal_displayed'] 		= $new_instance['journal_displayed'];
		$instance['display_issue_name'] 	= ( 'on' == $new_instance['display_issue_name'] ) ? 'on' : 'off';
		$instance['display_issue_cover'] 	= ( 'on' == $new_instance['display_issue_cover'] ) ? 'on' : 'off';
		$instance['display_pdf_link'] 		= ( 'on' == $new_instance['display_pdf_link'] ) ? 'on' : 'off';
	
		return $instance;
	
	}

	/**
	 * Displays the widget options in the dashboard
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance
	 */
	function form( $instance ) {
		
		$available_issues = get_terms( 'aip_magazine_issue', array( 'hide_empty' => false ) );
			
		//Defaults
		$defaults = array(
			'journal_displayed'	=> 'none',
			'display_issue_name'	=> 'on',
			'display_issue_cover'	=> 'on',
			'display_pdf_link'		=> 'on'
		);
		
		extract( wp_parse_args( (array) $instance, $defaults ) );
		
		if ( !empty( $available_issues ) ) :
		
			?>

			<p>
	        	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'aip_magazine' ); ?></label>
	            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( strip_tags( $title ) ); ?>" />
	        </p>
			
			<?php
			$aip_magazine_settings = get_aip_magazine_settings();
			$journals = get_terms( 'aip_magazine_issue_journals' );
			?> 
			
			<p>
	        	<label for="<?php echo $this->get_field_id('journal_displayed'); ?>"><?php _e( 'Select Journal to Display:', 'aip_magazine' ); ?></label><br />
                <select id="<?php echo $this->get_field_id('journal_displayed'); ?>" name="<?php echo $this->get_field_name('journal_displayed'); ?>">
				<option value="all" <?php selected( 'all', $journal_displayed ); ?>><?php _e( 'All Journals', 'aip_magazine' ); ?></option>
				<?php foreach ( $journals as $journal ) { ?>
					<option value="<?php echo esc_attr($journal->term_id); ?>" <?php selected( $journal->term_id, $journal_displayed ); ?> disabled><?php echo $journal->name; ?></option>
                <?php } ?>
                </select>
	        </p>
			
			
			<p>
	        	<label for="<?php echo $this->get_field_id('display_issue_name'); ?>"><?php _e( 'Display Issue Title', 'aip_magazine' ); ?></label>
                <input class="checkbox" id="<?php echo $this->get_field_id('display_issue_name'); ?>" name="<?php echo $this->get_field_name('display_issue_name'); ?>" type="checkbox" value="on" <?php checked( 'on' == $display_issue_name ) ?> />
	        </p>
            
			<p>
	        	<label for="<?php echo $this->get_field_id('display_issue_cover'); ?>"><?php _e( 'Display Issue Cover Image', 'aip_magazine' ); ?></label>
                <input class="checkbox" id="<?php echo $this->get_field_id('display_issue_cover'); ?>" name="<?php echo $this->get_field_name('display_issue_cover'); ?>" type="checkbox" value="on" <?php checked( 'on' == $display_issue_cover ) ?> />
	        </p>
            
			<p>
	        	<label for="<?php echo $this->get_field_id('display_pdf_link'); ?>"><?php _e( 'Display Issue PDF Link', 'aip_magazine' ); ?></label>
                <input class="checkbox" id="<?php echo $this->get_field_id('display_pdf_link'); ?>" name="<?php echo $this->get_field_name('display_pdf_link'); ?>" type="checkbox" value="on" <?php checked( 'on' == $display_pdf_link ) ?> />
	        </p>
        	<?php 
        
        else : 
        
            _e( 'You have to create a issue before you can use this widget.', 'aip_magazine' );
        
        endif;
	
	}

}
 
/**
 * This class registers and returns the Cover Image Widget
 *
 * @since 1.0.0
 */
class AipMagazine_Article_List extends WP_Widget {
	
	/**
	 * Set's widget name and description
	 *
	 * @since 1.0.0
	 */
	function AipMagazine_Article_List() {
		
		$widget_ops = array( 'classname' => 'aip_magazine_article_list', 'description' => __( 'Sidebar widget to display the current articles.', 'aip_magazine' ) );
		$control_ops = array('width' =>400, 'height' => 350);
        parent::__construct( 'AipMagazine_Article_List', __( 'AipMagazine Article List', 'aip_magazine' ), $widget_ops, $control_ops );
	
	}
	
	/**
	 * Displays the widget on the front end
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $atts, $instance ) {
		
		global $post;
		
		if ( !empty( $post->ID ) )
			$current_post_id = $post->ID;
		else
			$current_post_id = 0;
	
		$aip_magazine_settings = get_aip_magazine_settings();
			
		$out = '';
		
		extract( $atts );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : sanitize_text_field($instance['title']), $instance, $this->id_base);
		
		$args = array(
			'posts_per_page'    => empty( $instance['posts_per_page'] ) ? -1 : sanitize_text_field($instance['posts_per_page']),
            'post_type'			=> 'aip_article',
			'orderby'			=> empty( $instance['orderby'] ) ? 'menu_order' : sanitize_text_field($instance['orderby']),
			'order' 			=> empty( $instance['order'] ) ? 'DESC' : sanitize_text_field($instance['order']),
		);
		
		$aip_magazine_issue = array(
			'taxonomy' 	=> 'aip_magazine_issue',
			'field' 	=> 'slug',
			'terms' 	=> get_active_aip_magazine_issue(sanitize_text_field($instance['journal_displayed']))
		);
		
		//filtering on journal
        if ( !empty( $instance['journal_displayed'] ) && 'none' != $instance['journal_displayed'] ) {

            $journal = array(
                'taxonomy' 	=> 'aip_magazine_issue_journals',
                'field' 	=> 'term_id',
                'terms' 	=> (array)sanitize_text_field($instance['journal_displayed'])
            );

        } else {

            $journal = array ();

        }
		
		//filtering on issues
		if ( !empty( $instance['issue_displayed'] ) && 'all' != $instance['issue_displayed'] ) {
		
			$issue = array(
				'taxonomy' 	=> 'aip_magazine_issue',
				'field' 	=> 'slug',
				'terms' 	=> (array)sanitize_text_field($instance['issue_displayed'])
			);
			
		} else {

            $issue = array(
                'taxonomy' 	=> 'aip_magazine_issue',
                'field' => 'term_id',
                'terms' => array( 0 ),
                'operator' => 'NOT IN'
            );
			
		}
		
		//filtering on categories
		if ( !empty( $instance['article_category'] ) && 'all' != $instance['article_category'] ) {
		
			$category = array(
				'taxonomy' 	=> 'aip_magazine_issue_categories',
				'field' 	=> 'slug',
				'terms' 	=> (array)sanitize_key($instance['article_category'])
			);
			
		} else {

            $category = array(
                'taxonomy' 	=> 'aip_magazine_issue_categories',
                'field' => 'term_id',
                'terms' => array( 0 ),
                'operator' => 'NOT IN'

            );
			
		}

        $args['tax_query'] = array(
            'relation'	=> 'AND',
            $journal,
            $issue,
            $category
        );

		$articles = new WP_Query( $args );
		
		if ( $articles->have_posts() ) : 
		
			while ( $articles->have_posts() ) : $articles->the_post();
				
				$out .= '<div class="article_list">';
			
				$out .= "\n\n";
			
				if ( $current_post_id == $post->ID )
					$out .= '<div id="current_article">';
				
				$out .= aip_magazine_replacements_args( sanitize_textarea_field($instance['article_format']), $post );
			
				if ( $current_post_id == $post->ID )
					$out .= '</div>';
					
				$out .= '</div>';
			
            endwhile;
		
		endif;

		if ( !empty( $out ) ) {
			
			echo $before_widget;
			
			if ( $title)
				echo $before_title . $title . $after_title;
				
			echo '<div class="aip_magazine_article_list_widget">';
			echo $out; 
			echo '</div>';
			echo $after_widget;	
		
		}

		wp_reset_query();
	
	}

	/**
	 * Save's the widgets options on submit
	 *
	 * @since 1.0
	 
	 * @param array $new_instance
	 * @param array $old_instance
	 */
	function update( $new_instance, $old_instance ) {
		
		$instance 						= $old_instance;
		$instance['title'] 				= $new_instance['title'];
		$instance['journal_displayed'] 	= $new_instance['journal_displayed'];
		$instance['issue_displayed'] 	= $new_instance['issue_displayed'];
		$instance['posts_per_page'] 	= $new_instance['posts_per_page'];
		$instance['article_format'] 	= $new_instance['article_format'];
		$instance['article_category'] 	= $new_instance['article_category'];
		$instance['orderby'] 			= $new_instance['orderby'];
		$instance['order'] 				= $new_instance['order'];
		return $instance;
	
	}

	/**
	 * Displays the widget options in the dashboard
	 *
	 * @since 1.0
	 
	 * @param array $instance
	 */
	function form( $instance ) {
		
		$available_issues = get_terms( 'aip_magazine_issue', array( 'hide_empty' => false ) );

		//Defaults
		$defaults = array(
			'title'				=> '',
			'journal_displayed'	=> 'none',
			'issue_displayed'	=> 'none',
			'article_format'	=> 	'<p class="aip_magazine_widget_category">%CATEGORY[1]%</p>' . "\n" .
									'<p><a class="aip_magazine_widget_link" href="%URL%">%TITLE%</a></p>' . "\n" .
									'<p class="aip_magazine_widget_teaser">%TEASER%</p>' . "\n",
			'article_category'	=> 	'all',
			'posts_per_page'	=> '-1',
			'orderby'			=> 	'menu_order',
			'order'				=> 	'DESC',
		);
		
		extract( wp_parse_args( (array) $instance, $defaults ) );
		
		if ( !empty( $available_issues ) ) :
			?>
			<p>
	        	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'aip_magazine' ); ?></label>
	            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( strip_tags( $title ) ); ?>" />
	        </p>
            
            <?php
			$journals = get_terms( 'aip_magazine_issue_journals', array( 'hide_empty' => true )  );
			?> 
			
			<p>
	        	<label for="<?php echo $this->get_field_id('journal_displayed'); ?>"><?php _e( 'Select Journal to Display:', 'aip_magazine' ); ?></label><br />
                <select class="widefat journal-select" id="<?php echo $this->get_field_id('journal_displayed'); ?>" name="<?php echo $this->get_field_name('journal_displayed'); ?>">
                <option value="all" <?php selected( 'all', $journal_displayed ); ?>><?php _e( 'All Journals', 'aip_magazine' ); ?></option>
                    <?php foreach ( $journals as $journal ) { ?>
					<option value="<?php echo esc_attr($journal->term_id); ?>" <?php selected( $journal->term_id, $journal_displayed ); ?> disabled><?php echo $journal->name; ?></option>
                <?php } ?>
                </select>
	        </p>
            
			<?php
			$issues = get_terms( 'aip_magazine_issue', array( 'hide_empty' => true ) );
			?>
			<p>
	        	<label for="<?php echo $this->get_field_id('issue_displayed'); ?>"><?php _e( 'Select Issue to Display:', 'aip_magazine' ); ?></label><br />
                <select class="widefat issue-select" id="<?php echo $this->get_field_id('issue_displayed'); ?>" name="<?php echo $this->get_field_name('issue_displayed'); ?>">
				<option value="all" <?php selected( 'all', $issue_displayed ); ?>>
				<?php _e( 'All Issues', 'aip_magazine' ); ?></option>
				<?php foreach ( $issues as $issue ) { ?>
					<option data-journal-id="<?php echo $issue->parent; ?>" value="<?php echo esc_attr($issue->slug); ?>" <?php selected( $issue->slug, $issue_displayed ); ?>><?php echo $issue->name; ?></option>
                <?php } ?>
                </select>
	        </p>
			<?php
			
			$categories = get_terms( 'aip_magazine_issue_categories' , array( 'hide_empty' => true )  );
			?>  
			<p>
	        	<label for="<?php echo $this->get_field_id('article_category'); ?>"><?php _e( 'Select Category to Display:', 'aip_magazine' ); ?></label><br />
                <select class="widefat category-select" id="<?php echo $this->get_field_id('article_category'); ?>" name="<?php echo $this->get_field_name('article_category'); ?>">
				<option value="all" <?php selected( 'all', $article_category ); ?>><?php _e( 'All Categories', 'aip_magazine' ); ?></option>
				<?php foreach ( $categories as $cat ) { ?>
					<option data-journal-id="<?php echo $cat->parent; ?>" value="<?php echo esc_attr($cat->slug); ?>" <?php selected( $cat->slug, $article_category ); ?>><?php echo $cat->name; ?></option>
                <?php } ?>
                </select>
	        </p>
			
			<p>
	        	<label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e( 'Number of Articles to Show:', 'aip_magazine' ); ?></label> 
	            <input id="<?php echo $this->get_field_id('posts_per_page'); ?>" name="<?php echo $this->get_field_name('posts_per_page'); ?>" type="text" value="<?php echo esc_attr( strip_tags( $posts_per_page ) ); ?>" maxlength="4" size="4" /> 
                <small>-1 = All Articles</small>
	        </p>
            
            <p>
				<?php
                $orderby_options = array( 
                    'none' 				=> __( 'None', 'aip_magazine' ), 
                    'ID' 				=> __( 'Article ID', 'aip_magazine' ), 
                    'author' 			=> __( 'Article Author', 'aip_magazine' ), 
                    'title' 			=> __( 'Article Title', 'aip_magazine' ), 
                    'name' 				=> __( 'Article Name', 'aip_magazine' ), 
                    'date'				=> __( 'Article Publish Date', 'aip_magazine' ), 
                    'modified'			=> __( 'Article Modified Date', 'aip_magazine' ), 
                    'menu_order'		=> __( 'Article Order', 'aip_magazine' ), 
                    'rand'				=> __( 'Random Order', 'aip_magazine' ), 
                    'comment_count' 	=> __( 'Comment Count', 'aip_magazine' )
                );
                ?>
            
                <label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e( 'Select Sort Order:', 'aip_magazine' ); ?></label><br />
                <select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
                <?php foreach ( $orderby_options as $orderby_key => $orderby_title ) { ?>
                    <option value="<?php echo esc_attr($orderby_key); ?>" <?php selected( $orderby_key, $orderby ); ?>><?php echo $orderby_title; ?></option>
                <?php } ?>
                </select>
            </p>
            
            <p>
				<?php
                $order_options = array( 
                    'DESC' 	=> __( 'Descending', 'aip_magazine' ), 
                    'ASC' 	=> __( 'Ascending', 'aip_magazine' ), 
                );
                ?>
            
                <label for="<?php echo $this->get_field_id('order'); ?>"><?php _e( 'Select Order Direction:', 'aip_magazine' ); ?></label><br />
                <select id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
                <?php foreach ( $order_options as $order_key => $order_title ) { ?>
                    <option value="<?php echo esc_attr($order_key); ?>" <?php selected( $order_key, $order ); ?>><?php echo $order_title; ?></option>
                <?php } ?>
                </select>
            </p>
            
			<p>
	        	<label for="<?php echo $this->get_field_id('article_format'); ?>"><?php _e( 'Article Format:', 'aip_magazine' ); ?></label><br />
                <textarea id="<?php echo $this->get_field_id('article_format'); ?>" name="<?php echo $this->get_field_name('article_format'); ?>" cols="40" rows="16"><?php echo $article_format; ?></textarea>
	        </p>
            <p><a href="<?php echo admin_url( 'edit.php?post_type=aip_article&page=aip_magazine-help'  ) ; ?>"><?php _e( 'See AipMagazine Help for details on article formatting', 'aip_magazine' ); ?></a></p>
            <?php
        
        else : 
        
            _e( 'You have to create a issue before you can use this widget.', 'aip_magazine' );
        
        endif;
	
	}

}


/**

Article Categories widget class
 *
@since 1.0.0
 */
class AipMagazine_Article_Categories extends WP_Widget {

	function __construct()
	{ $widget_ops = array( 'classname' => 'aip_magazine_widget_categories', 'description' => __( 'A list or dropdown of Article categories', 'aip_magazine' ), ); parent::__construct('AipMagazine_Article_Categories', __( 'AipMagazine Article Categories', 'aip_magazine' ), $widget_ops); }

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Article Categories', 'aip_magazine' ) : sanitize_text_field($instance['title']), $instance, $this->id_base);

		$parent = empty( $instance['journal_displayed'] ) ? '0' : sanitize_text_field($instance['journal_displayed']);
		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] ) ? '1' : '0';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h, 'parent' => $parent);

		if ( $d ) {
			$cat_args['show_option_none'] = __( 'Select Article Category', 'aip_magazine' );
			$cat_args['name'] = '';
			$cat_args['id'] = 'aip_magazine_issue_categories';
			aip_magazine_dropdown_categories( apply_filters('aip_magazine_widget_categories_dropdown_args', $cat_args) );
			?>

            <script type='text/javascript'>
                /* <![CDATA[ */
                var aip_magazine_cat_dropdown = document.getElementById("aip_magazine_issue_categories");
                function onAipMagazineCatChange() {
                    if ( aip_magazine_cat_dropdown.options[aip_magazine_cat_dropdown.selectedIndex].value != 0
                        && aip_magazine_cat_dropdown.options[aip_magazine_cat_dropdown.selectedIndex].value != -1 )
                    { location.href = "<?php echo home_url(); ?>/?aip_magazine_issue_categories="+aip_magazine_cat_dropdown.options[aip_magazine_cat_dropdown.selectedIndex].value; }

                }
                aip_magazine_cat_dropdown.onchange = onAipMagazineCatChange;
                /* ]]> */
            </script>

			<?php
		} else
		{ ?> <ul> <?php $cat_args['title_li'] = ''; $cat_args['taxonomy'] = 'aip_magazine_issue_categories'; wp_list_categories(apply_filters('aip_magazine_widget_categories_args', $cat_args)); ?> </ul> <?php }

		echo $after_widget;
	}

	/**

	Save's the widgets options on submit
	 *
	@since 2.8.0

	@param array $new_instance
	@param array $old_instance
	 */
	function update( $new_instance, $old_instance ) { $instance = $old_instance; $instance['title'] = strip_tags($new_instance['title']); $instance['count'] = !empty($new_instance['count']) ? 1 : 0; $instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0; $instance['journal_displayed'] = $new_instance['journal_displayed']; return $instance; }

	/**
	Displays the widget options in the dashboard
	 *
	@since 1.0
	@param array $instance
	@return string|void
	 */
	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array( 'title' => '','journal_displayed' =>'none') );
		$title = sanitize_text_field($instance['title']) ;
		$count = !empty($instance['count']) ? (bool) $instance['count'] :false;
		$dropdown = !empty( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
		$journal_displayed = sanitize_text_field($instance['journal_displayed']);
		?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'aip_magazine' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<?php
		$journals = get_terms( 'aip_magazine_issue_journals' );
		?>

        <p>
            <label for="<?php echo $this->get_field_id('journal_displayed'); ?>"><?php _e( 'Select Journal to Display:', 'aip_magazine' ); ?></label><br />
            <select id="<?php echo $this->get_field_id('journal_displayed'); ?>" name="<?php echo $this->get_field_name('journal_displayed'); ?>">
                <option value="all" <?php selected( 'all', $journal_displayed ); ?>><?php _e( 'All Journals', 'aip_magazine' ); ?></option>
				<?php foreach ( $journals as $journal )
				{ ?> <option value="<?php echo esc_attr($journal->term_id); ?>" <?php selected( $journal->term_id, $journal_displayed ); ?> disabled><?php echo $journal->name;?></option> <?php }

				?>
            </select>
        </p>

        <p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
        <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown', 'aip_magazine' ); ?></label><br />

        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
        <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show product counts', 'aip_magazine' ); ?></label><br />

		<?php
	}

}
