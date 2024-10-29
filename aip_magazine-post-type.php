<?php
/**
 * Registers AipMagazine Article Post Type w/ Meta Boxes
 *
 * @package AipMagazine
 * @since 1.0.0
 */

if ( !function_exists( 'create_article_post_type' ) ) {

	/**
	 * Registers Article Post type for AipMagazine
	 *
	 * @since 1.0.0
	 */
	function create_article_post_type()  {
				
		$aip_magazine_settings = get_aip_magazine_settings();
		
		if ( !empty( $aip_magazine_settings['use_wp_taxonomies'] ) )
			$taxonomies = array( 'category', 'post_tag' );
		else
			$taxonomies = array( 'aip_magazine_issue_categories', 'aip_magazine_issue_tags', 'aip_magazine_issue_journals' );

		

            // Set UI labels for Custom Post Type
        	 $labels = array(
            'menu_name'           => __( 'Magazine', 'aip_magazine' ),
            'name'                => __( 'Articles', 'aip_magazine' ),
	        'singular_name'       => __( 'Article',  'aip_magazine' ),
	        'all_items'           => __( 'Articles', 'aip_magazine' ),
	        'add_new'             => __( 'Add New Article', 'aip_magazine' ),
            'add_new_item'        => __( 'Add New Article', 'aip_magazine' ),
            'edit_item'           => __( 'Edit Article', 'aip_magazine' ),
            'new_item' 			  => __( 'New Article', 'aip_magazine' ),
            'view_item'           => __( 'View Article', 'aip_magazine' ),
	        'search_items'        => __( 'Search Articles', 'aip_magazine' ),
	        'not_found'           => __( 'No articles found', 'aip_magazine' ),
	        'not_found_in_trash'  => __( 'No articles found in trash', 'aip_magazine' ),
            'parent_item_colon'   => __( 'Parent Item', 'aip_magazine' ),
	    );
		
		$args = array(
			'label' 				=> __( 'article', 'aip_magazine' ),
			'labels' 				=> $labels,
			'description' 			=> __( 'AipMagazine Articles', 'aip_magazine' ),
			'public'				=> true,
			'publicly_queryable' 	=> true,
			'exclude_fromsearch' 	=> false,
			'show_ui' 				=> true,
			'show_in_menu' 			=> true,
			'capability_type' 		=> array( 'article', 'articles' ),
			'map_meta_cap' 			=> true,
			'hierarchical' 			=> false,
			'supports' 				=> array( 	'title', 'author', 'editor', 'comments',
                                                'revisions', 'thumbnail', 'excerpt', 'trackbacks',
                                                'page-attributes'),
			'register_meta_box_cb' 	=> 'add_aip_magazine_articles_metaboxes',
			'has_archive' 			=> true,
			'rewrite' 				=> array( 'slug' => 'article' ),
			'taxonomies'			=> $taxonomies,
			'menu_icon'				=> AIPMAGAZINE_URL . '/images/aip_magazine-16x16.png',
            'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => false,
            'menu_position'       => 25,
			);

        register_post_type( 'aip_article', $args );
		
		if ( 'true' === get_option( 'aip_magazine_flush_rewrite_rules' ) ) {
			
			// ATTENTION: This is *only* done during plugin activation hook in this example!
			// You should *NEVER EVER* do this on every page load!!
			flush_rewrite_rules();
			delete_option( 'aip_magazine_flush_rewrite_rules' );
			
		}
		
	}
	add_action( 'init', 'create_article_post_type' );

}

if ( !function_exists( 'aip_magazine_article_columns' ) ) {

    /**
     * Registers metaboxes for AipMagazine Articles
     *
     * @since 1.0.0
     */
    function aip_magazine_article_columns($columns) {


        $columns['aip_magazine-fascicolo'] =  __( 'Issue', 'aip_magazine' );
        $columns['aip_magazine-rubrica'] =  __( 'Category', 'aip_magazine' );
        $columns['menu_order'] =  __( 'Sorting', 'aip_magazine' );
        unset($columns['author']);
        unset($columns['comments']);

        return $columns;

    }
    add_filter('manage_edit-aip_article_columns', 'aip_magazine_article_columns',10,1);
}

if ( !function_exists( 'aip_magazine_article_custom_column' ) ) {

    function aip_magazine_article_custom_column( $column) {
        global $post;

        $post_custom = get_post( $post->ID);

        if ('aip_magazine-fascicolo' == $column){
            $term = wp_get_post_terms( $post_custom->ID, 'aip_magazine_issue' );
            if (isset($term[0]->name))
                $fascicolo = $term[0]->name;
            else
                $fascicolo = '';
            echo esc_attr( htmlspecialchars($fascicolo));

        }
        if ('aip_magazine-rubrica' == $column){
            $term = wp_get_post_terms( $post_custom->ID, 'aip_magazine_issue_categories' );
            if (isset($term[0]->name))
                $rubrica = $term[0]->name;
            else
                $rubrica = '';

            echo esc_attr( htmlspecialchars($rubrica));

        }
        if ('menu_order' == $column){
            echo esc_attr(htmlspecialchars($post_custom->menu_order));
        }

    }
    add_action( 'manage_posts_custom_column' , 'aip_magazine_article_custom_column');
}

if ( !function_exists( 'aip_magazine_aip_article_sortable_columns' ) ) {

    /**
     * Filters sortable columns
     *
     * @since 1.0.0
     *
     * @param array $columns
     * @return array $columns
     */
    function aip_magazine_article_sortable_columns( $columns ) {

        $columns['menu_order'] = 'menu_order';

        return $columns;

    }
    add_filter( 'manage_edit-aip_article_sortable_columns', 'aip_magazine_article_sortable_columns', 10, 1 );

}


if ( !function_exists( 'aip_magazine_article_add_post_thumbnails' ) ) {
	
	function aip_magazine_article_add_post_thumbnails() {
		
		$supported_post_types = get_theme_support( 'post-thumbnails' );
		
		if( false === $supported_post_types )  {

            $post_types = array( 'aip_article' );
			add_theme_support( 'post-thumbnails', $post_types ); 
			           
		} else if ( is_array( $supported_post_types ) ) {
			
			$post_types = $supported_post_types[0];
            $post_types[] = 'aip_article';
			add_theme_support( 'post-thumbnails', $post_types );

		} 
	
	}
	add_action( 'after_setup_theme', 'aip_magazine_article_add_post_thumbnails', 99 );
	
}

if ( !function_exists( 'add_aip_magazine_articles_metaboxes' ) ) {
		
	/**
	 * Registers metaboxes for AipMagazine Articles
	 *
	 * @since 1.0.0
	 */
	function add_aip_magazine_articles_metaboxes() {

		add_meta_box( 'aip_magazine_article_meta_box',
                       __( 'AipMagazine Article Options', 'aip_magazine' ),
                       'aip_magazine_article_meta_box',
                       'aip_article',
                       'normal',
                       'high' );

		
		do_action( 'add_aip_magazine_articles_metaboxes' );
		
	}

}

if ( !function_exists( 'aip_magazine_article_meta_box' ) ) {

	/**
	 * Outputs Article HTML for options metabox
	 *
	 * @since 1.0.0
	 *
	 * @param object $post WordPress post object
	 */
	function aip_magazine_article_meta_box( $post ) {
        global $pagenow;
		/**/
        $autori = get_post_meta( $post->ID, '_autori', true );

        // funzioni per visualizzare un pdf
        $pdf_post = get_post_meta($post->ID, '_wp_custom_attachment', true);

        $first_page =  get_post_meta( $post->ID, '_first_page', true );
        $last_page =  get_post_meta( $post->ID, '_last_page', true );

		$teaser_text 				= get_post_meta( $post->ID, '_teaser_text', true );
		$featured_rotator 			= get_post_meta( $post->ID, '_featured_rotator', true );
		$featured_thumb 			= get_post_meta( $post->ID, '_featured_thumb', true );


		?>

		<div id="aip_magazine-article-metabox">

		<p><input id="featured_rotator" type="checkbox" name="featured_rotator" <?php checked( $featured_rotator || "on" == $featured_rotator ); ?> /><label for="featured_rotator"><?php _e( 'Add article to Featured Rotator', 'aip_magazine' ); ?></label></p>
	
				
		<p><input id="featured_thumb" type="checkbox" name="featured_thumb" <?php checked( $featured_thumb || "on" == $featured_thumb ); ?> /><label for="featured_thumb"><?php _e( 'Add article to Featured Thumbnails', 'aip_magazine' ); ?></label></p>
                    			
		<p>
		<label for="teaser_text"><strong><?php _e( 'Teaser Text', 'aip_magazine' ); ?></strong></label><br>
				
		<input class="large-text" type="text" name="teaser_text" value="<?php echo esc_attr( htmlspecialchars($teaser_text)); ?>" />
		</p>

            <p>
                <label for="autori"><strong><?php _e( 'Authors', 'aip_magazine' ); ?> (<?php _e( 'each author on a new line', 'aip_magazine' ); ?>)</strong></label><br>
                ​<textarea id="autori"  class="large-text" cols="40" rows="3" name="autori"><?php echo esc_attr( htmlspecialchars($autori));?></textarea>
            </p>

            <?php
               wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');

            if ( !empty( $pdf_post ) ) {

              $view_pdf = $view_pdf = '<p><a id="wp_custom_attachment_url" target="_blank" href="' .esc_url($pdf_post['url']) . '">' . __( 'View PDF Version', 'aip_magazine' ) . '</a></p>
                                       <input type="hidden" id="_view_pdf" value="1" name="_view_pdf">';

                if(strlen(trim($pdf_post['url'])) > 0) {
                    $remove_pdf = '<p><a href="javascript:;" id="wp_custom_attachment_delete">' . __( 'Remove PDF Version', 'aip_magazine' ) . '</a></p>';

              } // end if

            } else {

              $view_pdf = '';
              $remove_pdf = '';

            }
            ?>
            <p>
            <label for="wp_custom_attachment"><strong><?php _e( 'PDF Version', 'aip_magazine' ); ?></strong></label><br>

            <input type="file" id="wp_custom_attachment" name="wp_custom_attachment" value="" size="25" />
            <?php
                echo $view_pdf . $remove_pdf;
                echo apply_filters( 'aip_magazine_pdf_version', '', __( 'Issue-to-PDF Generated PDF', 'aip_magazine' ), $post );
            ?>
            </p>

            <p class="google_scholar">
                <label for="first_page"><strong><?php _e( 'First Page (used by Google Scholar)', 'aip_magazine' ); ?></strong></label><br>

                <input id="first_page" class="large-text" type="text" name="first_page" value="<?php echo esc_attr( htmlspecialchars($first_page)); ?>" />
            </p>

            <p class="google_scholar">
                <label for="last_page"><strong><?php _e( 'Last Page (used by Google Scholar)', 'aip_magazine' ); ?></strong></label><br>

                <input id="last_page" class="large-text" type="text" name="last_page" value="<?php echo esc_attr( htmlspecialchars($last_page)); ?>" />
            </p>

		</div>
        <div>
            <?php
              $ids_journals_hidden = get_ids_issue_journals_hidden();
              if (isset($ids_journals_hidden)){
                  //creo dei campi hidden di journals e issue
                  for ($i = 0; $i < count($ids_journals_hidden); $i++){
                       $ids_issue_hidden = get_ids_issue_hidden($ids_journals_hidden[$i]);
                       $vett_ids_issue_hidden = '';

					  for ($j = 0; $j < count($ids_issue_hidden); $j++){
						  if ($j < count($ids_issue_hidden)-1) {
						      $vett_ids_issue_hidden_menu_order = get_last_menu_order($ids_issue_hidden[$j]); ?>
                              <input type="hidden" id="issue_hidden_menu_order_<?php echo esc_attr(htmlspecialchars($ids_issue_hidden[$j])); ?>" name="issue_hidden_menu_order_<?php echo esc_attr(htmlspecialchars($ids_issue_hidden[$j])); ?>"value="<?php echo esc_attr(htmlspecialchars($vett_ids_issue_hidden_menu_order));?>">
                              <?php $vett_ids_issue_hidden = $vett_ids_issue_hidden.$ids_issue_hidden[$j].'-';
						  } else {
							  $vett_ids_issue_hidden_menu_order = get_last_menu_order($ids_issue_hidden[$j]);
							  ?>
                              <input type="hidden"
                                     id="issue_hidden_menu_order_<?php echo esc_attr(htmlspecialchars($ids_issue_hidden[$j])); ?>"
                                     name="issue_hidden_menu_order_<?php echo esc_attr(htmlspecialchars($ids_issue_hidden[$j])); ?>"
                                     value="<?php echo esc_attr(htmlspecialchars($vett_ids_issue_hidden_menu_order)); ?>">

							  <?php
							  $vett_ids_issue_hidden = $vett_ids_issue_hidden . $ids_issue_hidden[$j];
						  }
                       }
                      $ids_categories_hidden = get_ids_categories_hidden($ids_journals_hidden[$i]);
                      $vett_ids_categories_hidden = '';
                      for ($k = 0; $k < count($ids_categories_hidden); $k++){
                          if ($k < count($ids_categories_hidden)-1)
                              $vett_ids_categories_hidden = $vett_ids_categories_hidden.$ids_categories_hidden[$k].'-';
                          else
                              $vett_ids_categories_hidden = $vett_ids_categories_hidden.$ids_categories_hidden[$k];
                      }

              ?>
                      <input type="hidden" id="journal_issue_hidden-<?php echo esc_attr( htmlspecialchars($ids_journals_hidden[$i])); ?>" value="<?php echo esc_attr( htmlspecialchars($vett_ids_issue_hidden));?>" name="journal_issue_hidden-<?php echo esc_attr( htmlspecialchars($ids_journals_hidden[$i]));?>">
                      <input type="hidden" id="journal_categories_hidden-<?php echo esc_attr( htmlspecialchars($ids_journals_hidden[$i])); ?>" value="<?php echo esc_attr( htmlspecialchars($vett_ids_categories_hidden));?>" name="journal_issue_hidden-<?php echo esc_attr( htmlspecialchars($ids_journals_hidden[$i]));?>">
                      <input type="hidden" id="message_journals" value="<?php echo __('Select a Journal','aip_magazine');?>" name="message_journals">
                      <input type="hidden" id="message_issue" value="<?php echo __('Select an Issue','aip_magazine');?>" name="message_issue">

                  <?php

                  }
              }
            ?>

        </div>
		<?php

	}

}

if ( !function_exists( 'save_aip_magazine_aip_article_meta' ) ) {

    /**
     * Saves Article meta
     *
     * @since 1.0.0
     *
     * @param int $post_id WordPress post ID
     * @return int
     */
    function save_aip_magazine_aip_article_meta( $post_id ) {

        if (isset($_POST['wp_custom_attachment_nonce'])){
            if(!wp_verify_nonce($_POST['wp_custom_attachment_nonce'], plugin_basename(__FILE__))) {
                return $post_id;
            } // end if
        }
        // verify if this is an auto save routine.
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( isset( $_REQUEST['_inline_edit'] ) || isset( $_REQUEST['doing_wp_cron'] ) )
            return;

        //controllo se è stata associata una categories altrimenti associo per default la No Categories
        if (isset($_POST['tax_input'])){
            $leng = count($_POST['tax_input']['aip_magazine_issue_categories']);
            if ($leng == 1){
                $term_no_categories = get_terms('aip_magazine_issue_categories','parent=0&hide_empty=0');
                foreach ($term_no_categories as $term_no_categorie){
                    $termId = $term_no_categorie->term_id;
                }
                wp_set_object_terms( $post_id, $termId, 'aip_magazine_issue_categories' );

            }
        }

		if ( !empty( $_POST['teaser_text'] ) )
			update_post_meta( $post_id, '_teaser_text', sanitize_text_field($_POST['teaser_text']) );
		else
			delete_post_meta( $post_id, '_teaser_text' );
			
		if ( !empty( $_POST['featured_rotator'] ) )
			update_post_meta( $post_id, '_featured_rotator', sanitize_text_field($_POST['featured_rotator'] ));
		else
			delete_post_meta( $post_id, '_featured_rotator' );
			
		if ( !empty( $_POST['featured_thumb'] ) )
			update_post_meta( $post_id, '_featured_thumb', sanitize_text_field($_POST['featured_thumb']) );
		else
			delete_post_meta( $post_id, '_featured_thumb' );


            if ( !empty( $_POST['autori'] ) )
                update_post_meta( $post_id, '_autori', wp_specialchars_decode(sanitize_textarea_field($_POST['autori'])) );
            else
                delete_post_meta( $post_id, '_autori' );


            // Make sure the file array isn't empty
            if(!empty($_FILES['wp_custom_attachment']['name'])) {

                // Setup the array of supported file types. In this case, it's just PDF.
                $supported_types = array('application/pdf');

                // Get the file type of the upload
                $arr_file_type = wp_check_filetype(basename(sanitize_textarea_field($_FILES['wp_custom_attachment']['name'])));
                $uploaded_type = $arr_file_type['type'];

                // Check if the type is supported. If not, throw an error.
                if(in_array($uploaded_type, $supported_types)) {

                    // Use the WordPress API to upload the file
                    $upload = wp_upload_bits($_FILES['wp_custom_attachment']['name'], null, file_get_contents(sanitize_textarea_field($_FILES['wp_custom_attachment']['tmp_name'])));

                    if(isset($upload['error']) && $upload['error'] != 0) {
                        wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
                    } else {

                        update_post_meta( $post_id, '_wp_custom_attachment', $upload);

                    } // end if/else

                } else {
                    wp_die("The file type that you've uploaded is not a PDF.");
                } // end if/else

            }else{
                // Grab a reference to the file associated with this post
                $doc = get_post_meta($post_id, '_wp_custom_attachment', true);



                // Determine if a file is associated with this post and if the delete flag has been set (by clearing out the input box)
                if(isset($_POST['_view_pdf'])){
                    if ($_POST['_view_pdf'] == '0'){

                        // Attempt to remove the file. If deleting it fails, print a WordPress error.

                        if(unlink($doc['file'])) {

                            // Delete succeeded so reset the WordPress meta data
                            delete_post_meta($post_id, '_wp_custom_attachment');


                        } else {
                            wp_die('There was an error trying to delete your file.');
                        } // end if/el;se

                    } // end if
                }

        } // end if/else

        if ( !empty( $_POST['first_page'] ) )
            update_post_meta( $post_id, '_first_page', wp_specialchars_decode(sanitize_text_field($_POST['first_page'])) );
        else
            delete_post_meta( $post_id, '_first_page' );

        if ( !empty( $_POST['last_page'] ) )
            update_post_meta( $post_id, '_last_page', wp_specialchars_decode(sanitize_text_field($_POST['last_page'])) );
        else
            delete_post_meta( $post_id, '_last_page' );


        do_action( 'save_aip_magazine_aip_article_meta', $post_id );
				
	}
    add_action( 'save_post', 'save_aip_magazine_aip_article_meta' );

}
//funzione per inserire un filtro di ricerca relativo alle riviste nella lista degli articoli
if ( !function_exists( 'aip_magazine_filter_list_journals' ) ) {

    /**
     * Registers fliter for AipMagazine Articles
     *
     * @since 1.0.0
     */
    function aip_magazine_filter_list_journals() {

        $screen = get_current_screen();
        global $wp_query;
        if ( $screen->post_type == 'aip_article' ) {
            wp_dropdown_categories( array(
                'show_option_all' => __('Show All Journals','aip_magazine'),
                'taxonomy' => 'aip_magazine_issue_journals',
                'name' => 'aip_magazine_issue_journals',
                'orderby' => 'name',
                'order' => 'desc',
                'selected' => ( isset( $wp_query->query['aip_magazine_issue_journals'] ) ? $wp_query->query['aip_magazine_issue_journals'] : '' ),
                'hierarchical' => true,
                'depth' => 3,
                'show_count' => false,
                'hide_empty' => true,
                'parent'    => '-1',
            ) );
        }

    }
    add_action( 'restrict_manage_posts', 'aip_magazine_filter_list_journals' );
}


if ( !function_exists( 'aip_magazine_perform_filtering_journals' ) ) {

    /**
     * Registers fliter for AipMagazine Articles
     *
     * @since 1.0.0
     */
    function aip_magazine_perform_filtering_journals($query) {
        global $pagenow;
        $qv = &$query->query_vars;

        if( $pagenow=='edit.php' && isset($qv['aip_magazine_issue_journals']) && is_numeric($qv['aip_magazine_issue_journals']) ){
            if ($qv['aip_magazine_issue_journals'] != 0){
                $term = get_term_by( 'id', $qv['aip_magazine_issue_journals'], 'aip_magazine_issue_journals' );
                $qv['aip_magazine_issue_journals'] = $term->slug;
            }
        }

    }
    add_filter( 'parse_query','aip_magazine_perform_filtering_journals' );
}
//funzione per inserire un filtro di ricerca relativo al fascicolo nella lista degli articoli
if ( !function_exists( 'aip_magazine_filter_list_issue' ) ) {

    /**
     * Registers fliter for AipMagazine Articles
     *
     * @since 1.0.0
     */
    function aip_magazine_filter_list_issue() {

        $screen = get_current_screen();
        global $wp_query;
        if ( $screen->post_type == 'aip_article' ) {
          if (isset($_REQUEST['aip_magazine_issue_journals'])){
                wp_dropdown_categories( array(
                    'show_option_all' => __('Show All Issues','aip_magazine'),
                    'taxonomy' => 'aip_magazine_issue',
                    'name' => 'aip_magazine_issue',
                    'orderby' => 'name',
                    'order' => 'desc',
                    'selected' => ( isset( $wp_query->query['aip_magazine_issue'] ) ? $wp_query->query['aip_magazine_issue'] : '' ),
                    'hierarchical' => true,
                    'depth' => 3,
                    'show_count' => false,
                    'hide_empty' => true,
                    'exclude' => get_ids_issue_journals(sanitize_key($_REQUEST['aip_magazine_issue_journals'])),
                ) );
            }
       }

    }
    add_action( 'restrict_manage_posts', 'aip_magazine_filter_list_issue' );
}

if ( !function_exists( 'aip_magazine_perform_filtering_issue' ) ) {

    /**
     * Registers fliter for AipMagazine Articles
     *
     * @since 1.0.0
     */
   function aip_magazine_perform_filtering_issue($query) {
        global $pagenow;
        $qv = &$query->query_vars;

        if( $pagenow=='edit.php' && isset($qv['aip_magazine_issue']) && is_numeric($qv['aip_magazine_issue']) ){
            if ($qv['aip_magazine_issue'] != 0){
                $term = get_term_by( 'id', $qv['aip_magazine_issue'], 'aip_magazine_issue' );
                $qv['aip_magazine_issue'] = $term->slug;
            }
        }

    }
    add_filter( 'parse_query','aip_magazine_perform_filtering_issue' );
}
//funzione per inserire un filtro di ricerca relativo alle rubriche nella lista degli articoli
if ( !function_exists( 'aip_magazine_filter_list_categories' ) ) {

    /**
     * Registers fliter for AipMagazine Articles
     *
     * @since 1.0.0
     */
    function aip_magazine_filter_list_categories() {

        $screen = get_current_screen();
        global $wp_query;
        if ( $screen->post_type == 'aip_article' ) {
            if (isset( $_REQUEST['aip_magazine_issue_journals'] )){

                wp_dropdown_categories( array(
                    'show_option_all' => __('Show All Categories','aip_magazine'),
                    'taxonomy' => 'aip_magazine_issue_categories',
                    'name' => 'aip_magazine_issue_categories',
                    'orderby' => 'name',
                    'order' => 'desc',
                    'selected' => ( isset( $wp_query->query['aip_magazine_issue_categories'] ) ? $wp_query->query['aip_magazine_issue_categories'] : '' ),
                    'hierarchical' => true,
                    'depth' => 3,
                    'show_count' => false,
                    'hide_empty' => true,
                    'exclude' => get_ids_issue_journals(sanitize_key($_REQUEST['aip_magazine_issue_journals'])),
                ) );

            }
        }

    }
    add_action( 'restrict_manage_posts', 'aip_magazine_filter_list_categories' );
}


if ( !function_exists( 'aip_magazine_perform_filtering_categories' ) ) {

    /**
     * Registers fliter for AipMagazine Articles
     *
     * @since 1.0.0
     */
    function aip_magazine_perform_filtering_categories($query) {
        global $pagenow;
        $qv = &$query->query_vars;

        if( $pagenow=='edit.php' && isset($qv['aip_magazine_issue_categories']) && is_numeric($qv['aip_magazine_issue_categories']) ){
            if ($qv['aip_magazine_issue_categories'] != 0){
                $term = get_term_by( 'id', $qv['aip_magazine_issue_categories'], 'aip_magazine_issue_categories' );
                $qv['aip_magazine_issue_categories'] = $term->slug;
            }
        }

    }
    add_filter( 'parse_query','aip_magazine_perform_filtering_categories' );
}

if ( !function_exists( 'get_ids_issue_journals' ) ) {

    function get_ids_issue_journals($parent) {

        global $wpdb;

        $term_ids = array();

        $term_taxonomy_children = $wpdb->get_col( 'SELECT term_id FROM ' . $wpdb->term_taxonomy . ' WHERE parent <> '.$parent);

        foreach( $term_taxonomy_children as $children_id )
            $term_ids[] = $children_id;
        if (isset($term_ids))
            return $term_ids;
    }

}

if ( !function_exists( 'get_ids_issue_journals_hidden' ) ) {

    function get_ids_issue_journals_hidden() {

        global $wpdb;

        $term_ids_hidden = array();

        $term_taxonomy_children = $wpdb->get_col( 'SELECT term_id FROM ' . $wpdb->term_taxonomy . ' WHERE taxonomy ="aip_magazine_issue_journals"' );
        foreach( $term_taxonomy_children as $children_id )
            $term_ids_hidden[] = $children_id;
        if (isset($term_ids_hidden))
            return $term_ids_hidden;
    }

}


if ( !function_exists( 'get_ids_issue_hidden' ) ) {

    function get_ids_issue_hidden($parent) {

        global $wpdb;

        $term_ids_issue_hidden = array();

        $term_taxonomy_children = $wpdb->get_col( 'SELECT term_id FROM ' . $wpdb->term_taxonomy . ' WHERE taxonomy ="aip_magazine_issue" AND parent = '.$parent );
        foreach( $term_taxonomy_children as $children_id )
            $term_ids_issue_hidden[] = $children_id;
        if (isset($term_ids_issue_hidden))
            return $term_ids_issue_hidden;
    }

}


if ( !function_exists( 'get_ids_categories_hidden' ) ) {

    function get_ids_categories_hidden($parent) {

        global $wpdb;

        $term_ids_categories_hidden = array();

        $term_taxonomy_children = $wpdb->get_col( 'SELECT term_id FROM ' . $wpdb->term_taxonomy . ' WHERE taxonomy ="aip_magazine_issue_categories" AND parent = '.$parent );
        foreach( $term_taxonomy_children as $children_id )
            $term_ids_categories_hidden[] = $children_id;
        if (isset($term_ids_categories_hidden))
            return $term_ids_categories_hidden;
    }

}

if ( !function_exists( 'get_last_menu_order' ) ) {

	/**
	 * Return last article in an issue
	 * @since 2.1.0
	 *
	 */
	function get_last_menu_order($term_taxonomy_id) {

	    global $wpdb;

        $query = 'SELECT MAX(' . $wpdb->posts . '.menu_order) FROM ' . $wpdb->posts . ' INNER JOIN '. $wpdb->term_relationships .' ON ' . $wpdb->posts . '.ID = '. $wpdb->term_relationships .'.object_id WHERE ' . $wpdb->posts . '.post_type = "aip_article" AND ' . $wpdb->posts . '.post_status = "publish" AND '. $wpdb->term_relationships .'.term_taxonomy_id = '.$term_taxonomy_id;
	    $last_menu_order = $wpdb->get_col( $query );

	    return $last_menu_order[0];
	}

}