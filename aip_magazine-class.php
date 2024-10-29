<?php
/**
 * Registers AipMagazine class for setting up AipMagazine
 *
 * @package AipMagazine
 * @since 1.0.0
 */

if ( ! class_exists( 'AipMagazine' ) ) {
	
	/**
	 * This class registers the main aip_magazine functionality
	 *
	 * @since 1.0.0
	 */
	class AipMagazine {
		
		/**
		 * Class constructor, puts things in motion
		 *
		 * @since 1.0.0
		 *
		 * @todo Move the the_author filter to a more appopriate place
		 * @todo Move the pre_get_posts filter to a more appopriate place
		 */
		function AipMagazine() {
			
			$settings = $this->get_settings();
			
			add_image_size( 'aip_magazine-cover-image', apply_filters( 'aip_magazine-cover-image-width', $settings['cover_image_width'] ), apply_filters( 'aip_magazine-cover-image-height', $settings['cover_image_height'] ), true );
			add_image_size( 'aip_magazine-featured-rotator-image', apply_filters( 'aip_magazine-featured-rotator-image-width', $settings['featured_image_width'] ), apply_filters( 'aip_magazine-featured-rotator-image-height', $settings['featured_image_height'] ), true );
			add_image_size( 'aip_magazine-featured-thumb-image', apply_filters( 'aip_magazine-featured-thumb-image-width', $settings['featured_thumb_width'] ), apply_filters( 'aip_magazine-featured-thumb-image-height', $settings['featured_thumb_height'] ), true );
		
			add_action( 'admin_init', array( $this, 'upgrade' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_notices', array( $this, 'aip_magazine_notification' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_wp_enqueue_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'admin_wp_print_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
			
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );



			add_action( 'wp_ajax_aip_magazine_process_notice_link', array( $this, 'ajax_process_notice_link' ) );

			if ( !empty( $settings['aip_magazine_author_name'] ) && !is_admin() ) 
				add_filter( 'the_author', array( $this, 'the_author' ) );
			
			if ( !empty( $settings['use_wp_taxonomies'] ) ) 
				add_action( 'pre_get_posts', array( $this, 'add_aip_magazine_articles_to_tag_query' ) );
				
			if ( !is_admin() )
				add_action( 'pre_get_posts', array( $this, 'remove_draft_issues_from_main_query' ) );

            /*classe che implementa il filtro di ricerca per le riviste*/
            if( ! class_exists( 'Aip_Magazine_Parent_Category_Filter' ) ) {
                require_once('aip_magazine-parent-category-filter.php');
            }
            $pcf = new Aip_Magazine_Parent_Category_Filter();
            $pcf->register_hooks();
		}
		
		
		/**
		 * Runs activation routines when AipMagazine is activated
		 *
		 * @since 1.0.0
		 *
		 */
		function activation() {
			
			add_option( 'aip_magazine_flush_rewrite_rules', 'true' );
			
		}
	

			
		/**
		 * Initialize AipMagazine Admin Menu
		 *
		 * @since 1.0.0
		 */
		function admin_menu() {

            add_submenu_page( 'edit.php?post_type=aip_article', __( 'Settings', 'aip_magazine' ), __( 'Settings', 'aip_magazine' ), apply_filters( 'manage_aip_magazine_settings', 'manage_aip_magazine_settings' ), 'aip_magazine', array( $this, 'settings_page' ) );

            add_submenu_page( 'edit.php?post_type=aip_article', __( 'Help', 'aip_magazine' ), __( 'Help', 'aip_magazine' ), apply_filters( 'manage_aip_magazine_settings', 'manage_aip_magazine_settings' ), 'aip_magazine-help', array( $this, 'help_page' ) );

        }
		

		
		/**
		 * Replaces Author Name with AipMagazine setting, if it is set
		 * Otherwise, uses WordPress's Author name
		 *
		 * @since 1.0.0
		 *
		 * @param string $wp_author WordPress Author name
		 * @return string Author Name
		 */
		function the_author( $wp_author ) {
		
			global $post;
			
			if ( !empty( $post->ID ) ) {
					
				if ( $author_name = get_post_meta( $post->ID, '_aip_magazine_author_name', true ) )
					return $author_name;
				else
					return $wp_author;
				
			}
			
			return $wp_author;
			
		}
	
		/**
		 * Modifies WordPress query to include Articles in Tag/Category queries
		 *
		 * @since 1.0.0
		 *
		 * @param object $query WordPress Query Object
		 */
		function add_aip_magazine_articles_to_tag_query( $query ) {
		
		   if ( $query->is_main_query()
			 && ( $query->is_tag() || $query->is_category() ) )
               $query->set( 'post_type', array( 'post', 'aip_article' ) );


        }
		
		/**
		 * Modifies WordPress query to remove draft Issues from queries
		 * Except for users with permission to see drafts
		 *
		 * @since 1.2.0
		 *
		 * @param object $query WordPress Query Object
		 */
		function remove_draft_issues_from_main_query( $query ) {
						
			if ( !is_admin() && $query->is_main_query()
				&& !current_user_can( apply_filters( 'see_aip_magazine_draft_issues', 'manage_issues' ) ) ) {
				
				$term_ids = get_aip_magazine_draft_issues();	
				
				$draft_issues = array(
					'taxonomy' => 'aip_magazine_issue',
					'field' => 'id',
					'terms' => $term_ids,
					'operator' => 'NOT IN',
				);
				
				if ( !$query->is_tax() ) {
					
					$query->set( 'tax_query', array(
							$draft_issues,
						) 
					);
				
				} else {
				
					$term_ids = get_aip_magazine_draft_issues();	
				
					$tax_query = $query->tax_query->queries;
					$tax_query[] = $draft_issues;
					$tax_query['relation'] = 'AND';
				
					$query->set( 'tax_query', $tax_query );
					
				}
							
			}
			
		}
		
		/**
		 * Enqueues styles used by AipMagazine WordPress Dashboard
		 *
		 * @since 1.0.0
		 * @uses wp_enqueue_style() to enqueue CSS files
		 */
		function admin_wp_print_styles() {
		
			global $hook_suffix;
			
			if ( 'article_page_aip_magazine' == $hook_suffix
                || ( 'edit.php' == $hook_suffix && !empty( $_GET['post_type'] ) && 'aip_article' == $_GET['post_type'] ) || ( 'edit-tags.php' == $hook_suffix && !empty( $_GET['post_type'] ) && 'aip_article' == $_GET['post_type'] ) )
                wp_enqueue_style( 'aip_magazine_admin_style', AIPMAGAZINE_URL . '/css/aip_magazine-admin.css', '', AIPMAGAZINE_VERSION );
			
		}
	
		/**
		 * Enqueues scripts used by AipMagazine WordPress Dashboard
		 *
		 * @since 1.0.0
		 * @uses wp_enqueue_script() to enqueue JS files
		 */
		function admin_wp_enqueue_scripts( $hook_suffix ) {
			

			
			// Hack for edit-tags to include the "enctype=multipart/form-data" argument in the edit tags HTML form, 
		 	// for uploading issue cover images
			if ( ('edit-tags.php'==$hook_suffix || 'term.php' == $hook_suffix) && !empty( $_GET['taxonomy'] ) && 'aip_magazine_issue' == $_GET['taxonomy'])
				  wp_enqueue_script( 'aip_magazine_issue-custom-tax-hacks', AIPMAGAZINE_URL . '/js/aip_magazine_issue-custom-tax-hacks.js', array( 'jquery' ), AIPMAGAZINE_VERSION );


			if ( 'post.php' == $hook_suffix )
				wp_enqueue_script( 'aip_magazine_issue-edit-article-hacks', AIPMAGAZINE_URL . '/js/aip_magazine_issue-edit-article-hacks.js', array( 'jquery' ), AIPMAGAZINE_VERSION );

            if ( 'widgets.php'== $hook_suffix )
                wp_enqueue_script( 'aip_magazine_issue-widgets', AIPMAGAZINE_URL . '/js/aip_magazine_issue-widgets.js', array( 'jquery' ), AIPMAGAZINE_VERSION );

            if ( 'aip_article_page_aip_magazine' == $hook_suffix )
				wp_enqueue_script( 'aip_magazine-admin', AIPMAGAZINE_URL . '/js/aip_magazine-admin.js', array( 'jquery' ), AIPMAGAZINE_VERSION );
				wp_enqueue_media();

			wp_enqueue_script( 'aip_magazine-script', AIPMAGAZINE_URL . 'js/script.js', array( 'jquery' ), AIPMAGAZINE_VERSION );

			wp_localize_script( 'aip_magazine-script', 'aip_magazine_ajax',
            array( 
            	'ajaxurl' => admin_url( 'admin-ajax.php' ),
            	'noticeNonce' => wp_create_nonce( 'aip_magazine-notice-nonce')
             ) );

		}
			
		/**
		 * Enqueues styles and scripts used by AipMagazine on the frontend
		 *
		 * @since 1.0.0
		 * @uses wp_enqueue_script() to enqueue JS files
		 * @uses wp_enqueue_style() to enqueue CSS files
		 */
		function frontend_scripts() {
			
			$settings = $this->get_settings();
			
			if ( apply_filters( 'enqueue_aip_magazine_styles', 'true' ) ) {
		
				switch( $settings['css_style'] ) {
					
					case 'none' :
						break;
					
					case 'default' :
					default : 
						wp_enqueue_style( 'aip_magazine_style', AIPMAGAZINE_URL . '/css/aip_magazine.css', '', AIPMAGAZINE_VERSION );
						break;
						
				}
			
			}
			
			wp_enqueue_script( 'jquery-aip_magazine-flexslider', AIPMAGAZINE_URL . '/js/swiper.min.js', array( 'jquery' ), AIPMAGAZINE_VERSION );
			wp_enqueue_style( 'jquery-aip_magazine-flexslider', AIPMAGAZINE_URL . '/css/swiper.css', '', AIPMAGAZINE_VERSION );
		
		}
		
		/**
		 * Gets AipMagazine settings
		 *
		 * @since 1.0.0
		 *
		 * @return array AipMagazine settings, merged with defaults.
\		 */
		function get_settings() {
			
			$defaults = array(
                                'page_for_articles' => 0,
                                'page_for_articles_active_issue'     => 0,
								'page_for_archives'		=> 0,
                                'page_of_journals_articles_active_issue'      => 0,
                                'page_of_journals_articles_archives'      => 0,
                                'pdf_title'				=> __( 'Download PDF', 'aip_magazine' ),
								'pdf_only_title'		=> __( 'Download PDF', 'aip_magazine' ),
								'pdf_open_target'		=> '_blank',
								'cover_image_width'		=> 200,
								'cover_image_height'	=> 268,
								'featured_image_width'	=> 600,
								'featured_image_height'	=> 338,
								'featured_thumb_width'	=> 160,
								'featured_thumb_height'	=> 120,
								'default_issue_image'	=> apply_filters( 'aip_magazine_default_issue_image', AIPMAGAZINE_URL . '/images/archive-image-unavailable.jpg' ),
								'custom_image_used'		=> 0,
								'display_byline_as'		=> 'user_firstlast',
								'aip_magazine_author_name'	=> '',
								'use_wp_taxonomies'		=> '',
                                'use_issue_tax_links'   => '',
								'article_format'		=> 	'<p class="aip_magazine_article_category">%CATEGORY[1]%</p>' . "\n" .
															'<p><a class="aip_magazine_article_link" href="%URL%">%TITLE%</a></p>' . "\n" .
															'<p class="aip_magazine_article_content">%EXCERPT%</p>' . "\n" .
															'<p class="aip_magazine_article_byline">%BYLINE%</p>' . "\n",
								'css_style'				=> 'default',
								'show_rotator_control'	=> '',
								'show_rotator_direction' => '',
								'animation_type'		=> 'slide',



							);
		
			$defaults = apply_filters( 'aip_magazine_default_settings', $defaults );
		
			$settings = get_option( 'aip_magazine' );
			
			return wp_parse_args( $settings, $defaults );
			
		}
		
		/**
		 * Update AipMagazine settings
		 *
		 * @since 1.2.0
		 *
		 * @param array AipMagazine settings
\		 */
		function update_settings( $settings ) {
			

			update_option( 'aip_magazine', $settings );
			
		}
		
		/**
		 * Outputs the AipMagazine settings page
		 *
		 * @since 1.0
		 * @todo perform the save function earlier
		 */
		function settings_page() {
			
			// Get the user options
			$settings = $this->get_settings();
			
			if ( !empty( $_REQUEST['remove_default_issue_image'] ) ) {
				
				wp_delete_attachment( sanitize_text_key($_REQUEST['remove_default_issue_image']) );
				
				unset( $settings['default_issue_image'] );
				unset( $settings['custom_image_used'] );
				
				$this->update_settings( $settings );
					
				$settings = $this->get_settings();
			
			}
			
			if ( !empty( $_REQUEST['update_aip_magazine_settings'] ) ) {

                if ( isset( $_REQUEST['page_for_articles'] ) )
                    $settings['page_for_articles'] = sanitize_text_field($_REQUEST['page_for_articles']);

				if ( isset( $_REQUEST['page_for_articles_active_issue'] ) )
					$settings['page_for_articles_active_issue'] = sanitize_text_field($_REQUEST['page_for_articles_active_issue']);

				if ( isset( $_REQUEST['page_for_archives'] ) )
					$settings['page_for_archives'] = sanitize_text_field($_REQUEST['page_for_archives']);

                if ( isset( $_REQUEST['page_of_journals_articles_active_issue'] ) ){
                    $settings['page_of_journals_articles_active_issue'] = sanitize_text_field($_REQUEST['page_of_journals_articles_active_issue']);
                }
                if ( isset( $_REQUEST['page_of_journals_articles_archives'] ) ){
                    $settings['page_of_journals_articles_archives'] = sanitize_text_field($_REQUEST['page_of_journals_articles_archives']);
                }
				if ( !empty( $_REQUEST['css_style'] ) )
					$settings['css_style'] = sanitize_text_field($_REQUEST['css_style']);
				
				if ( !empty( $_REQUEST['pdf_title'] ) )
					$settings['pdf_title'] = sanitize_text_field($_REQUEST['pdf_title']);
				
				if ( !empty( $_REQUEST['pdf_only_title'] ) )
					$settings['pdf_only_title'] = sanitize_text_field($_REQUEST['pdf_only_title']);
					
				if ( !empty( $_REQUEST['pdf_open_target'] ) )
					$settings['pdf_open_target'] = sanitize_text_field($_REQUEST['pdf_open_target']);
				
				if ( !empty( $_REQUEST['article_format'] ) )
					$settings['article_format'] = sanitize_text_field($_REQUEST['article_format']);
				
				if ( !empty( $_REQUEST['cover_image_width'] ) )
					$settings['cover_image_width'] = sanitize_text_field($_REQUEST['cover_image_width']);
				else
					unset( $settings['cover_image_width'] );
				
				if ( !empty( $_REQUEST['cover_image_height'] ) )
					$settings['cover_image_height'] = sanitize_text_field($_REQUEST['cover_image_height']);
				else
					unset( $settings['cover_image_height'] );
				
				if ( !empty( $_REQUEST['featured_image_width'] ) )
					$settings['featured_image_width'] = sanitize_text_field($_REQUEST['featured_image_width']);
				else
					unset( $settings['featured_image_width'] );
				
				if ( !empty( $_REQUEST['featured_image_height'] ) )
					$settings['featured_image_height'] = sanitize_text_field($_REQUEST['featured_image_height']);
				else
					unset( $settings['featured_image_height'] );
				
				if ( !empty( $_REQUEST['featured_thumb_width'] ) )
					$settings['featured_thumb_width'] = sanitize_text_field($_REQUEST['featured_thumb_width']);
				else
					unset( $settings['featured_thumb_width'] );
				
				if ( !empty( $_REQUEST['featured_thumb_height'] ) )
					$settings['featured_thumb_height'] = sanitize_text_field($_REQUEST['featured_thumb_height']);
				else
					unset( $settings['featured_thumb_height'] );

				if ( !empty( $_REQUEST['default_issue_image'] ) ) {
					$settings['default_issue_image'] = sanitize_key($_REQUEST['default_issue_image']);
					$settings['custom_image_used'] = 1;
				}
				
				if ( !empty( $_REQUEST['display_byline_as'] ) )
					$settings['display_byline_as'] = sanitize_text_field($_REQUEST['display_byline_as']);
				
				if ( !empty( $_REQUEST['aip_magazine_author_name'] ) )
					$settings['aip_magazine_author_name'] = sanitize_text_field($_REQUEST['aip_magazine_author_name']);
				else
					unset( $settings['aip_magazine_author_name'] );

				if ( !empty( $_REQUEST['show_thumbnail_byline'] ) )
					$settings['show_thumbnail_byline'] = sanitize_text_field($_REQUEST['show_thumbnail_byline']);
				else
					unset( $settings['show_thumbnail_byline'] );
				
				if ( !empty( $_REQUEST['use_wp_taxonomies'] ) )
					$settings['use_wp_taxonomies'] = sanitize_text_field($_REQUEST['use_wp_taxonomies']);
				else
					unset( $settings['use_wp_taxonomies'] );

                if ( !empty( $_REQUEST['use_issue_tax_links'] ) )
                    $settings['use_issue_tax_links'] = sanitize_text_field($_REQUEST['use_issue_tax_links']);
                else
                    unset( $settings['use_issue_tax_links'] );

                if ( !empty( $_REQUEST['show_rotator_control'] ) )
					$settings['show_rotator_control'] = sanitize_text_field($_REQUEST['show_rotator_control']);
				else
					unset( $settings['show_rotator_control'] );

				if ( !empty( $_REQUEST['show_rotator_direction'] ) )
					$settings['show_rotator_direction'] = sanitize_text_field($_REQUEST['show_rotator_direction']);
				else
					unset( $settings['show_rotator_direction'] );

				if ( !empty( $_REQUEST['animation_type'] ) )
					$settings['animation_type'] = sanitize_text_field($_REQUEST['animation_type']);

				$settings = apply_filters( 'aip_magazine_save_settings', $settings );

				$this->update_settings( $settings );
					
				// It's not pretty, but the easiest way to get the menu to refresh after save...
				?>
					<script type="text/javascript">
					<!--
                    window.location = "<?php echo $_SERVER['PHP_SELF'] .'?post_type=aip_article&page=aip_magazine&settings_saved=true'; ?>"
                    //-->
					</script>
				<?php
				
			}

			
			
			if ( !empty( $_REQUEST['update_aip_magazine_settings'] ) || !empty( $_GET['settings_saved'] ) ) {
				
				// update settings notification ?>
				<div class="updated"><p><strong><?php _e( 'Settings updated.', 'aip_magazine' );?></strong></p></div>
				<?php
				
			}
			
			// Display HTML form for the options below
			?>
			<div class="wrap aip_magazine-settings">

			 <h2 style='margin-bottom: 10px;' ><?php _e( 'Settings', 'aip_magazine' ); ?></h2>


            <div class="postbox-container column-primary">

            	<h2 class="nav-tab-wrapper" id="aip_magazine-tabs">
					<a class="nav-tab" id="general-tab" href="#top#general"><?php _e( 'General', 'aip_magazine' );?></a>
					<?php do_action( 'aip_magazine_nav_tabs' ); ?>
				</h2>

            	<div class="tabwrapper">

            	<form id="aip_magazine" method="post" action="" enctype="multipart/form-data" encoding="multipart/form-data">

           		<div id="general" class="aip_magazinetab">
	            <div>	
	            <div class="meta-box-sortables ui-sortable">
            
                
                    
                    <div id="modules">

                        <h3><span><?php _e( 'Admin Options', 'aip_magazine' ); ?></span></h3>
                        
                        <div class="inside">
                        
                        <table id="aip_magazine_administrator_options" class="form-table">

                            <?php $journals = get_terms( 'aip_magazine_issue_journals' );?>
                            <tr>
                                <th rowspan="1"> <?php _e( 'Page for Articles', 'aip_magazine' ); ?></th>
                                <td><?php echo wp_dropdown_pages( array( 'name' => 'page_for_articles', 'echo' => 0, 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => $settings['page_for_articles'] ) ); ?></td>
                            </tr>
                            <tr>
                                <th rowspan="1"> <?php _e( 'Page for Articles Active Issue', 'aip_magazine' ); ?></th>
                                <td><?php echo wp_dropdown_pages( array( 'name' => 'page_for_articles_active_issue', 'echo' => 0, 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => $settings['page_for_articles_active_issue'] ) ); ?>
                                    &nbsp;&nbsp;&nbsp;<?php _e( 'for Journal', 'aip_magazine' ); ?>
                                    <select id="<?php echo'page_of_journals_articles_active_issue'; ?>" name="<?php echo'page_of_journals_articles_active_issue'; ?>">
                                        <option value="0" <?php selected( 'none', $settings['page_of_journals_articles_active_issue'] ); ?>><?php _e( 'None', 'aip_magazine' ); ?></option>
                                        <?php foreach ( $journals as $journal ) { ?>
                                            <option value="<?php echo esc_attr($journal->term_id); ?>" <?php selected( $journal->term_id, $settings['page_of_journals_articles_active_issue'] ); ?>><?php echo $journal->name; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Page for Issue Archives', 'aip_magazine' ); ?></th>
                                <td><?php echo wp_dropdown_pages( array( 'name' => 'page_for_archives', 'echo' => 0, 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => $settings['page_for_archives'] ) ); ?>
                                    &nbsp;&nbsp;&nbsp;<?php _e( 'for Journal', 'aip_magazine' ); ?>
                                    <select id="<?php echo'page_of_journals_articles_archives'; ?>" name="<?php echo'page_of_journals_articles_archives'; ?>">
                                        <option value="0" <?php selected( 'none', $settings['page_of_journals_articles_archives'] ); ?>><?php _e( 'None', 'aip_magazine' ); ?></option>
                                        <?php foreach ( $journals as $journal ) { ?>
                                            <option value="<?php echo esc_attr($journal->term_id); ?>" <?php selected( $journal->term_id, $settings['page_of_journals_articles_archives'] ); ?>><?php echo $journal->name; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                        
                        	<?php if ( apply_filters( 'enqueue_aip_magazine_styles', true ) ) { ?>
                            
                        	<tr>
                                <th rowspan="1"> <?php _e( 'CSS Style', 'aip_magazine' ); ?></th>
                                <td>
								<select id='css_style' name='css_style'>
                                <?php
								$css_styles = $this->get_css_styles();
								foreach ( $css_styles as $slug => $name ) {
									?>
									<option value='<?php echo esc_attr($slug); ?>' <?php selected( $slug, $settings['css_style'] ); ?> ><?php echo $name; ?></option>
                                    <?php
								}
								?>
								</select>
                                </td>
                            </tr>
                            
                            <?php } ?>
                            
                            <tr>
                                <th rowspan="1"> <?php _e( 'PDF Download Link Title', 'aip_magazine' ); ?></th>
                                <td><input type="text" id="pdf_title" class="regular-text" name="pdf_title" value="<?php echo htmlspecialchars( stripcslashes( $settings['pdf_title'] ) ); ?>" /></td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'PDF Only Title', 'aip_magazine' ); ?></th>
                                <td><input type="text" id="pdf_only_title" class="regular-text" name="pdf_only_title" value="<?php echo htmlspecialchars( stripcslashes( $settings['pdf_only_title'] ) ); ?>" /></td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'PDF Link Target', 'aip_magazine' ); ?></th>
                                <td>
								<select id='pdf_open_target' name='pdf_open_target'>
									<option value='_blank' <?php selected( '_blank', $settings['pdf_open_target'] ); ?> ><?php _e( 'Open in New Window/Tab', 'aip_magazine' ); ?></option>
									<option value='_self' <?php selected( '_self', $settings['pdf_open_target'] ); ?> ><?php _e( 'Open in Same Window/Tab', 'aip_magazine' ); ?></option>
								</select>
                                </td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Cover Image Size', 'aip_magazine' ); ?></th>
                                <td>
                                <?php _e( 'Width', 'aip_magazine' ); ?> <input type="text" id="cover_image_width" class="small-text" name="cover_image_width" value="<?php echo esc_attr(htmlspecialchars( stripcslashes( $settings['cover_image_width'] )) ); ?>" />px &nbsp;&nbsp;&nbsp;&nbsp; <?php _e( 'Height', 'aip_magazine' ); ?> <input type="text" id="cover_image_height" class="small-text" name="cover_image_height" value="<?php echo htmlspecialchars( stripcslashes( $settings['cover_image_height'] ) ); ?>" />px
                                </td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Featured Rotator Image Size', 'aip_magazine' ); ?></th>
                                <td>
                                <?php _e( 'Width', 'aip_magazine' ); ?> <input type="text" id="featured_image_width" class="small-text" name="featured_image_width" value="<?php echo esc_attr(htmlspecialchars( stripcslashes( $settings['featured_image_width']) ) ); ?>" />px &nbsp;&nbsp;&nbsp;&nbsp; <?php _e( 'Height', 'aip_magazine' ); ?> <input type="text" id="featured_image_height" class="small-text" name="featured_image_height" value="<?php echo htmlspecialchars( stripcslashes( $settings['featured_image_height'] ) ); ?>" />px
                                </td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Featured Thumbnail Image Size', 'aip_magazine' ); ?></th>
                                <td>
                                <?php _e( 'Width', 'aip_magazine' ); ?> <input type="text" id="featured_thumb_width" class="small-text" name="featured_thumb_width" value="<?php echo esc_attr(htmlspecialchars( stripcslashes( $settings['featured_thumb_width'] )) ); ?>" />px &nbsp;&nbsp;&nbsp;&nbsp; <?php _e( 'Height', 'aip_magazine' ); ?> <input type="text" id="featured_thumb_height" class="small-text" name="featured_thumb_height" value="<?php echo htmlspecialchars( stripcslashes( $settings['featured_thumb_height'] ) ); ?>" />px
                                </td>
                            </tr>
                            
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Default Issue Image', 'aip_magazine' ); ?></th>
                                <td>
                                	<input id="default_issue_image" type="text" size="36" name="default_issue_image" value="<?php echo $settings['default_issue_image']; ?>" />
								    <input id="upload_image_button" class="button" type="button" value="Upload Image" />
								    <p>Enter a URL or upload an image</p>

                                

                                	<p><img style="max-width: 400px;" src="<?php echo $settings['default_issue_image']; ?>" /></p>
                                
                                <?php if ( 0 < $settings['custom_image_used'] ) { ?>
                                <p><a href="?<?php echo http_build_query( wp_parse_args( array( 'remove_default_issue_image' => 1 ), $_GET ) )?>"><?php __( 'Remove Custom Default Issue Image', 'aip_magazine' ); ?></a></p>
                                <?php } ?>
                                </td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Display Byline As', 'aip_magazine' ); ?></th>
                                <td>
                                <select id="display_byline_as" name="display_byline_as" >
                                	<option value="user_firstlast" <?php selected( 'user_firstlast' == $settings['display_byline_as'] ); ?>>First & Last Name</option>
                                	<option value="user_firstname" <?php selected( 'user_firstname' == $settings['display_byline_as'] ); ?>>First Name</option>
                                	<option value="user_lastname" <?php selected( 'user_lastname' == $settings['display_byline_as'] ); ?>>Last Name</option>
                                	<option value="display_name" <?php selected( 'display_name' == $settings['display_byline_as'] ); ?>>Display Name</option>
                                </select>
                                </td>
                            </tr>

                            <tr>

                                <th rowspan="1"> <?php _e( 'Show Thumbnail Byline', 'aip_magazine' ); ?></th>
                                <td><input type="checkbox" id="show_thumbnail_byline" name="show_thumbnail_byline" <?php if (isset($settings['show_thumbnail_byline']))checked( $settings['show_thumbnail_byline'] || 'on' == $settings['show_thumbnail_byline'] ); ?>" /></td>
                            </tr>
                        
                        	<tr>
                                <th rowspan="1"> <?php _e( 'Name', 'aip_magazine' ); ?></th>
                                <td><input type="checkbox" id="aip_magazine_author_name" name="aip_magazine_author_name" <?php checked( $settings['aip_magazine_author_name'] || 'on' == $settings['aip_magazine_author_name'] ); ?>" /> <?php _e( 'Use AipMagazine Author Name instead of WordPress Author', 'aip_magazine' ); ?></td>
                            </tr>
                        
                        	<!--<tr>
                                <th rowspan="1"> <?php _e( 'Categories and Tags', 'aip_magazine' ); ?></th>
                                <td><input type="checkbox" id="use_wp_taxonomies" name="use_wp_taxonomies" <?php checked( $settings['use_wp_taxonomies'] || 'on' == $settings['use_wp_taxonomies'] ); ?>" /> <?php _e( 'Use Default WordPress Category and Tag Taxonomies', 'aip_magazine' ); ?></td>
                            </tr>-->

                            <tr>
                                <th rowspan="1"> <?php _e( 'Links', 'aip_magazine' ); ?></th>
                                <td><input type="checkbox" id="use_issue_tax_links" name="use_issue_tax_links" <?php checked( $settings['use_issue_tax_links'] || 'on' == $settings['use_issue_tax_links'] ); ?> /> <?php _e( 'Use Taxonomical links instead of shortcode based links for Issues', 'aip_magazine' ); ?></td>
                            </tr>
                            
                        </table>
                        
	                        <?php wp_nonce_field( 'aip_magazine_general_options', 'aip_magazine_general_options_nonce' ); ?>
	                                                  
	                       

                        </div> <!-- inside -->
                        
                    </div> <!-- postbox -->

                    <div id="modules">
                    
                       
                        <h3><span><?php _e( 'AipMagazine Featured Rotator Options', 'aip_magazine' ); ?></span></h3>
                        
                        <div class="inside">
						
						 <table id="aip_magazine_administrator_options" class="form-table">

						    <tr>
                                <th rowspan="1"> <?php _e( 'Pagination Navigation', 'aip_magazine' ); ?></th>
                                <td><input type="checkbox" id="show_rotator_control" name="show_rotator_control" <?php checked( $settings['show_rotator_control'] || 'on' == $settings['show_rotator_control'] ); ?>" /><?php _e( 'Display pagination above the slider', 'aip_magazine' ); ?>
								</td>
                            </tr>

                            <tr>
                                <th rowspan="1"> <?php _e( 'Direction Navigation', 'aip_magazine' ); ?></th>
                                <td><input type="checkbox" id="show_rotator_direction" name="show_rotator_direction" <?php checked( $settings['show_rotator_direction'] || 'on' == $settings['show_rotator_direction'] ); ?>" /><?php _e( 'Display previous/next navigation arrows', 'aip_magazine' ); ?>
								</td>
                            </tr>

                            <tr>
                                <th rowspan="1"> <?php _e( 'Animation Type', 'aip_magazine' ); ?></th>
                                <td>
                                <select id="animation_type" name="animation_type" >
                                	<option value="slide" <?php selected( 'slide' == $settings['animation_type'] ); ?>>Slide</option>
                                	<option value="fade" <?php selected( 'fade' == $settings['animation_type'] ); ?>>Fade</option>
                                	
                                </select>
                                </td>
                            </tr>
                        

                        	
                           </table>
                        
	                       

                        </div> <!-- inside -->

                     </div> <!-- postbox -->
                    
                    <div id="modules">
                    
                       
                        <h3><span><?php _e( 'AipMagazine Article Shortcode Format', 'aip_magazine' ); ?></span></h3>
                        
                        <div class="inside">

	                        <p>This controls the article output of the [aip_magazine_articles] shortcode on the Current Issue page.</p>
	                        
	                        <textarea id="article_format" class="code" cols="75" rows="8" name="article_format"><?php echo esc_attr(htmlspecialchars( stripcslashes( $settings['article_format']) ) ); ?></textarea>
	                        
	                                                  
	                        <p class="submit">
	                            <input class="button-primary" type="submit" name="update_aip_magazine_settings" value="<?php _e( 'Save Settings', 'aip_magazine' ) ?>" />
	                        </p>

                        </div> <!-- inside -->
                        
                    </div> <!-- postbox -->

            </div>

            </div>

            </div> <!-- hometab -->
			
			<?php do_action( 'aip_magazine_settings_areas' ); ?>

            </div> <!-- tabwrapper -->

            </div> 

	             <div class="postbox-container column-secondary">


	             	<div class="metabox-holder">
	               		<div class="postbox">
	               		 
	                        <h3><span><?php _e( 'Rate AipMagazine!', 'aip_magazine' ); ?></span></h3>
	                        
	                        <div class="inside">
	                        	<p>If you find the AipMagazine plugin helpful, please leave us a review on WordPress.org. Your honest feedback helps us improve AipMagazine for everyone.</p>

	                        	<p><a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/aip_magazine">Rate AipMagazine on WordPress.org</a></p>

	                        	

	                        </div>

	                	</div>
	                </div>
	              
	               </div>
			</div>

			
			<?php
			
		}
		
		/**
		 * Outputs the AipMagazine settings page
		 *
		 * @since 1.0.0
		 * @uses do_action() On 'help_page' for addons
		 */
		function help_page() {
			
			// Display HTML
			?>
			<div class=wrap>
            <div style="width:70%;" class="postbox-container">

        
                <h2 style='margin-bottom: 10px;' ><?php _e( 'AipMagazine Help', 'aip_magazine' ); ?></h2>

                  <div id="aip_magazine-getting-started">

    
                    <h3><span><?php _e( 'Getting Started', 'aip_magazine' ); ?></span></h3>
                    

                    	<p><?php _e( 'The following steps will demonstrate how to get started creating your online magazine.', 'aip_magazine' ); ?></p>


                     	<h4><?php _e( '0. Install AipMagazine', 'aip_magazine' ); ?></h4>
					  
                        <ol>
                            <li><?php _e( 'Go to Plugins->Add New and search for AipMagazine', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Click "Install Now" and then "Active Plugin"', 'aip_magazine' ); ?></li>
                        </ol>

                        <h4><?php _e( '1. Create pages for Current Issue and Past Issues', 'aip_magazine' ); ?></h4>
                        <ol>
                            <li><?php _e( 'Go to Pages->Add New', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Create a page for your current issue. We recommend using "Current Issue" as the page title.', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Create a page for your issue archives. We recommend using "Past Issues" as the page title.', 'aip_magazine' ); ?></li>
                        </ol>

                        <h4><?php _e( '2. Configure AipMagazine Settings', 'aip_magazine' ); ?></h4>
                        <ol>
                            <li><?php _e( 'Go to Magazine->Settings', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Choose your page for articles (your current issue page)', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Choose your page for issue archives (your past issues page)', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'You can configure the rest of the options to your liking, or leave them in their default state.', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Click "Save Settings"', 'aip_magazine' ); ?></li>
                        </ol>

                        <h4><?php _e( '3. Create a Journal', 'aip_magazine' ); ?></h4>
                        <ol>
                            <li><?php _e( 'Go to Magazine->Journals', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Enter the name, the slug, the description and the name of the Journal and click "Add New Journal"', 'aip_magazine' ); ?></li>
                        </ol>

                        <h4><?php _e( '4. Create an Issue', 'aip_magazine' ); ?></h4>
                        <ol>
                            <li><?php _e( 'Go to Magazine->Issues', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Enter the name of the issue (i.e. Summer 2014) and click "Add New Issues"', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Click on the newly created issue title', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Upload a cover image. You can adjust the dimensions of the cover image on the AipMagazine Settings page.', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Enter any other information for the issue, if applicable', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Click "Update"', 'aip_magazine' ); ?></li>
                        </ol>

                        <h4><?php _e( '5. Add Articles to the Issue', 'aip_magazine' ); ?></h4>
                        <ol>
                            <li><?php _e( 'Go to Magazine->Add New', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Enter the title and content for your article, just like a normal WordPress post', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Add a featured image, if applicable', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Choose the issue the article is related to in the Issues sidebar area', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Adjust the AipMagazine Article Options at the bottom of the article, if applicable', 'aip_magazine' ); ?></li>
                        </ol>

                        <h4><?php _e( '6. Add AipMagazine Widgets to Sidebar', 'aip_magazine' ); ?></h4>
                        <ol>
                            <li><?php _e( 'Go to Appearance->Widgets', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Drag the Issue widget you want to use into your sidebar', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Click "Save"', 'aip_magazine' ); ?></li>
                        </ol>

                        <h4><?php _e( '7. Set Issue to Published', 'aip_magazine' ); ?></h4>
                        <ol>
                            <li><?php _e( 'Go to Articles->Issues', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Click on the title of the issue you want to make live', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Change the Issue Status dropdown to "Live"', 'aip_magazine' ); ?></li>
                            <li><?php _e( 'Click "Update"', 'aip_magazine' ); ?></li>
                        </ol>

                  </div>

                   <h3><span><?php _e( 'AipMagazine Shortcodes', 'aip_magazine' ); ?></span></h3>

                   <!--<p>For more help with customizing AipMagazine shortcodes, please read the <a href="https://zeen101.com/get-help/documentation/shortcodes/" target="_blank">documentation</a>.</p>-->

                   <p><strong><?php _e( 'AipMagazine', 'aip_magazine' ); ?> <?php _e( 'Issue Title:', 'aip_magazine' ); ?> </strong><code style="font-size: 1.2em; background: #ffffe0;">[aip_magazine_issue_title]</code></p>
                                    
                    <p><?php _e( 'This shortcode will display the current issue title.', 'aip_magazine' ); ?></p>

                    <hr>

                
					<p><strong><?php _e( 'AipMagazine', 'aip_magazine' ); ?> <?php _e( 'Article Loop:', 'aip_magazine' ); ?> </strong><code style="font-size: 1.2em; background: #ffffe0;">[aip_magazine_articles]</code></p>
                                    
                    <p><?php _e( 'This shortcode will display the list of articles in an issue.', 'aip_magazine' ); ?></p>

                    <h4><?php _e( 'Default Arguments:', 'aip_magazine' ); ?></h4>

                    <ul>
                        <li><em>posts_per_page</em> - -1 <?php _e( '(no pagination)', 'aip_magazine' ); ?></li>
                        <li><em>issue</em> - <?php _e( 'Active Issue', 'aip_magazine' ); ?></li>
                    </ul>

                    <h4><?php _e( 'Accepted Arguments:', 'aip_magazine' ); ?></h4>

                    <ul>
                        <li><em>posts_per_page</em> - <?php _e( 'Any number -1 and greater (-1 = no pagination)', 'aip_magazine' ); ?></li>
                        <li><em>issue</em> - <?php _e( 'the Issue slug', 'aip_magazine' ); ?></li>
                    </ul>

                    <h4><?php _e( 'Examples:', 'aip_magazine' ); ?></h4>

                    <p><em>[aip_magazine_articles post_per_page="3"]</em></p>


                    <hr>

                              
					<p><strong><?php _e( 'AipMagazine', 'aip_magazine' ); ?> <?php _e( 'Featured Article Rotator:', 'aip_magazine' ); ?> </strong><code style="font-size: 1.2em; background: #ffffe0;">[aip_magazine_featured_rotator]</code></p>
                                    
                    <p><?php _e( 'This shortcode will display a slideshow of articles that have been checked to display in the featured rotator.', 'aip_magazine' ); ?></p>

                    <h4><?php _e( 'Default Arguments:', 'aip_magazine' ); ?></h4>

                    <ul>
                        <li><em>journal_id</em> - <?php _e( 'taken from settings', 'aip_magazine' ); ?></li>
                        <li><em>orderby</em> - menu_order</li>
                        <li><em>order</em> - DESC</li>
                        <li><em>issue</em> - <?php _e( 'Active Issue', 'aip_magazine' ); ?></li>
                    </ul>

                    <h4><?php _e( 'Accepted Arguments:', 'aip_magazine' ); ?></h4>

                    <ul>
                        <li><em>journal_id</em> - <?php _e( 'the Journal id', 'aip_magazine' ); ?></li>
                        <li><em>orderby</em> - menu_order, title</li>
                        <li><em>order</em> - DESC, ASC</li>
                        <li><em>issue</em> - <?php _e( 'the Issue slug', 'aip_magazine' ); ?></li>
                    </ul>


                    <h4><?php _e( 'Examples:', 'aip_magazine' ); ?></h4>

                    <p><em>[aip_magazine_featured_rotator journal_id="1"]</em></p>

                    <p><em>[aip_magazine_featured_rotator issue="issue-5" orderby="title" order="ASC"]</em></p>


                    <hr>


                    <p><strong><?php _e( 'AipMagazine', 'aip_magazine' ); ?> <?php _e( 'Featured Thumbnails:', 'aip_magazine' ); ?></strong> <code style="font-size: 1.2em; background: #ffffe0;">[aip_magazine_featured_thumbnails]</code></p>

                    <p><?php _e( 'This shortcode will display the grid of featured article thumbnails in an issue', 'aip_magazine' ); ?>.</p>

                    <h4><?php _e( 'Default Arguments:', 'aip_magazine' ); ?></h4>

                    <ul>
                        <li><em>journal_id</em> - <?php _e( 'taken from settings', 'aip_magazine' ); ?></li>
                        <li><em>orderby</em> - menu_order</li>
                        <li><em>order</em> - DESC</li>
                        <li><em>issue</em> - <?php _e( 'Active Issue', 'aip_magazine' ); ?></li>
                    </ul>

                    <h4><?php _e( 'Accepted Arguments:', 'aip_magazine' ); ?></h4>

                    <ul>
                        <li><em>journal_id</em> - <?php _e( 'the Journal id', 'aip_magazine' ); ?></li>
                        <li><em>orderby</em> - menu_order, title</li>
                        <li><em>order</em> - DESC, ASC</li>
                        <li><em>issue</em> - <?php _e( 'the Issue slug', 'aip_magazine' ); ?></li>
                    </ul>

                    <h4><?php _e( 'Examples:', 'aip_magazine' ); ?></h4>

                    <p><em>[aip_magazine_featured_thumbnails journal_id="2"]</em></p>

                    <p><em>[aip_magazine_featured_thumbnails issue="issue-5" orderby="menu_order" order="DESC"]</em></p>

                    <hr>
                                    
                             
                    <p><strong><?php _e( 'AipMagazine', 'aip_magazine' ); ?> <?php _e( 'Archive Page:', 'aip_magazine' ); ?>:</strong> <code style="font-size: 1.2em; background: #ffffe0;">[aip_magazine_archives]</code></p>
                                    
                    <p><?php _e( 'This shortcode will display the list of current and past issues.', 'aip_magazine' ); ?></p>
                                    
                    <h4><?php _e( 'Default Arguments:', 'aip_magazine' ); ?></h4>


                    <ul>
                        <li><em>journal_id</em> - <?php _e( 'taken from settings', 'aip_magazine' ); ?></li>
                        <li><em>orderby</em> - term_id</li>
                        <li><em>order</em> - DESC</li>
                        <li><em>limit</em> - 0</li>
                    </ul>

                    <h4><?php _e( 'Accepted Arguments:', 'aip_magazine' ); ?></h4>

                    <ul>
                        <li><em>journal_id</em> - <?php _e( 'the Journal id', 'aip_magazine' ); ?></li>
                        <li><em>orderby</em> - term_id, issue_order, name</li>
                        <li><em>order</em> - DESC, ASC</li>
                        <li><em>limit</em> - <?php _e( 'Any number 0 and greater', 'aip_magazine' ); ?></li>
                    </ul>

                    <h4><?php _e( 'Examples:', 'aip_magazine' ); ?></h4>

                    <p><em>[aip_magazine_archives journal_id="1"]</em></p>
                    <p><em>[aip_magazine_archives journal_id="1" orderby="name" order="ASC" limit=5]</em></p>

                <?php do_action( 'aip_magazine_help_page' ); ?>

            </div>
			</div>
			<?php
			
		}
		/**
		 * Outputs the AipMagazine CSS page
		 *
		 * @since 1.3.0
		 */
		function css_page() {
			
			// Display HTML
			?>
			<div class=wrap>
            <div style="width:70%;" class="postbox-container">
            <div class="metabox-holder">	
            <div class="meta-box-sortables ui-sortable">
        
                <h2 style='margin-bottom: 10px;' ><?php _e( 'AipMagazine Advanced Styles', 'aip_magazine' ); ?></h2>
                
                <div id="aip_magazine-articles" class="postbox">
                
                    <div class="handlediv" title="Click to toggle"><br /></div>
    
                    <h3 class="hndle"><span><?php _e( 'Advanced Style Options', 'aip_magazine' ); ?></span></h3>
                    
                    <div class="inside">
                                    
                        <table class="form-table">
                    
                            <tr>
                            
                                <td>
									<?php _e( 'Reset to Default Styles', 'aip_magazine' ); ?>
                                </td>
                                
                            </tr>
                            
                        </table>
                    
                    </div>
                    
                </div>
                                
            </div>
            </div>
            </div>
			</div>
			<?php
			
		}
		
		/**
		 * Upgrade function, tests for upgrade version changes and performs necessary actions
		 *
		 * @since 1.0.0
		 */
		function upgrade() {
			
			$settings = $this->get_settings();
			
			if ( !empty( $settings['version'] ) )
				$old_version = $settings['version'];
			else
				$old_version = 0;

			if ( version_compare( $old_version, '1.2.0', '<' ) )
				$this->upgrade_to_1_2_0( $old_version );

			$settings['version'] = AIPMAGAZINE_VERSION;
			$this->update_settings( $settings );
			
		}
		
		/**
		 * Initialized permissions
		 *
		 * @since 1.2.0
		 */
		function upgrade_to_1_2_0( $old_version ) {
			
			$role = get_role('administrator');
			if ($role !== NULL)
				// Articles
				$role->add_cap('edit_article');
				$role->add_cap('read_article');
				$role->add_cap('delete_article');
				$role->add_cap('edit_articles');
				$role->add_cap('edit_others_articles');
				$role->add_cap('publish_articles');
				$role->add_cap('read_private_articles');
				$role->add_cap('delete_articles');
				$role->add_cap('delete_private_articles');
				$role->add_cap('delete_published_articles');
				$role->add_cap('delete_others_articles');
				$role->add_cap('edit_private_articles');
				$role->add_cap('edit_published_articles');
				// Issues
				$role->add_cap('manage_aip_magazine_settings');
				$role->add_cap('manage_issues');
				$role->add_cap('manage_article_categories');
				$role->add_cap('manage_article_tags');
                $role->add_cap('manage_article_journals');
				$role->add_cap('edit_issues');
				$role->add_cap('edit_others_issues');
				$role->add_cap('edit_published_issues');
				$role->add_cap('publish_issues');
	
			$role = get_role('editor');
			if ($role !== NULL) {}
				// Articles
				$role->add_cap('edit_articles');
				$role->add_cap('edit_others_articles');
				$role->add_cap('edit_published_articles');
				$role->add_cap('publish_articles');
				$role->add_cap('delete_published_articles');
				$role->add_cap('delete_others_articles');
				$role->add_cap('delete_articles');
				$role->add_cap('delete_private_articles');
				$role->add_cap('edit_private_articles');
				$role->add_cap('read_private_articles');
				// Issues
				$role->add_cap('manage_issues');
				$role->add_cap('manage_article_categories');
				$role->add_cap('manage_article_tags');
                $role->add_cap('manage_article_journals');
				$role->add_cap('edit_issues');
				$role->add_cap('edit_others_issues');
				$role->add_cap('edit_published_issues');
				$role->add_cap('publish_issues');
	
			$role = get_role('author');
			if ($role !== NULL) {}
				// Articles
				$role->add_cap('edit_articles');
				$role->add_cap('edit_published_articles');
				$role->add_cap('publish_articles');
				$role->add_cap('delete_articles');
				$role->add_cap('delete_published_articles');
				// Issues
				$role->add_cap('edit_issues');
				$role->add_cap('edit_published_issues');
				$role->add_cap('publish_issues');
	
			$role = get_role('contributor');
			if ($role !== NULL) {}
				// Articles
				$role->add_cap('edit_articles');
				$role->add_cap('delete_articles');
				// Issues
				$role->add_cap('edit_issues');
				
			if ( 0 != $old_version ) {
			
				update_option( 'aip_magazine_nag', '<strong>Attention AipMagazine Subscribers!</strong> We have launched a new version of AipMagazine and split out the Advanced Search and Migration Tool into their own plugins. If you were using either of these functions in your previous version of AipMagazine, you will need to download them from your account at <a href="http://aip_magazine.com/">AipMagazine</a> and install them on your site.<br />Sorry for any inconvenience this may have caused you and thank you for your continued support!' );
				
			}
				
		}
		

		
		/**
		 * Verify the API status reported back to AipMagazine
		 *
		 * @since 1.0.0
		 *
		 * @param object $response WordPress remote query body
		 */
		function api_status( $response ) {
		
			if ( 1 < $response->account_status ) {
				
				update_option( 'aip_magazine_nag', $response->response );
				
			} else {
			
				delete_option( 'aip_magazine_nag' );
				delete_option( 'aip_magazine_nag_version_dismissed' );
				
			}
			
		}
		
		/**
		 * Returns the style available with AipMagazine
		 *
		 * @since 1.0.0
		 * @uses apply_filters on 'aip_magazine_css_styles' hook, for extending AipMagazine
		 */
		function get_css_styles() {
		
			$styles = array(
				'default'	=> __( 'Default', 'aip_magazine' ),
				'none'		=> __( 'None', 'aip_magazine' ),
			);
			
			return apply_filters( 'aip_magazine_css_styles', $styles );
			
		}
		
		/**
		 * If an AipMagazine notification is set, display it.
		 * Called by teh admin_notices hook
		 *
		 * @since 1.0.0
		 */
		function aip_magazine_notification() {
			
			if ( !empty( $_REQUEST['remove_aip_magazine_nag'] ) ) {
				
				delete_option( 'aip_magazine_nag' );
				update_option( 'aip_magazine_nag_version_dismissed', AIPMAGAZINE_VERSION );
				
			}
		
			if ( ( $notification = get_option( 'aip_magazine_nag' ) ) && version_compare( get_option( 'aip_magazine_nag_version_dismissed' ), AIPMAGAZINE_VERSION, '<' ) )
				echo '<div class="update-nag"><p>' . $notification . '</p><p><a href="' . add_query_arg( 'remove_aip_magazine_nag', true ) . '">' . __( 'Dismiss', 'aip_magazine' ) . '</a></p></div>';
		 
		}

		/**
		 * Process ajax calls for notice links
		 *
		 * @since 2.0.3
		 */
		function ajax_process_notice_link() {

			$nonce = $_POST['nonce'];

			if ( ! wp_verify_nonce( $nonce, 'aip_magazine-notice-nonce' ) )
				die ( 'Busted!'); 

			global $current_user;

			update_user_meta( $current_user->ID, 'aip_magazine_rss_item_notice_link', 1 );

			echo get_user_meta( $current_user->ID, 'aip_magazine_rss_item_notice_link', true );

			exit;

		}
		
	}
	
}
