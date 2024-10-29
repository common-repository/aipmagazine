<?php
/**
 * Registers AipMagazine Article, Category Taxonomy w/ Meta Boxes
 *
 * @package AipMagazine
 * @since 1.0.0
 */

if ( !function_exists( 'create_aip_magazine_cats_taxonomy' ) ) {
		
	/**
	 * Registers Article Category taxonomy for AipMagazine
	 *
	 * @since 1.0.0
	 * @todo misnamed originaly, should reallly be aip_magazine_article_categories
	 */
	function create_aip_magazine_cats_taxonomy() {
		
	  $labels = array(


          'name' 			  => __( 'Categories', 'aip_magazine' ),
          'singular_name' 	  => __( 'Category', 'aip_magazine' ),
          'search_items' 	  => __( 'Search Categories', 'aip_magazine' ),
          'all_items' 		  => __( 'All Categories', 'aip_magazine' ),
          'parent_item' 	  => __( 'Parent Category', 'aip_magazine' ),
          'parent_item_colon' => __( 'Parent Category:', 'aip_magazine' ),
          'edit_item' 		  => __( 'Edit Category', 'aip_magazine' ),
          'update_item' 	  => __( 'Update Category', 'aip_magazine' ),
          'add_new_item' 	  => __( 'Add New Category', 'aip_magazine' ),
          'new_item_name' 	  => __( 'New Category', 'aip_magazine' ),
          'menu_name' 		  => __( 'Categories', 'aip_magazine' )
			
		); 	
	
		register_taxonomy(
			'aip_magazine_issue_categories', 
			array( ), 
			array(
				'hierarchical' 	=> true,
				'labels' 		=> $labels,
				'show_ui' 		=> true,
				'show_tagcloud' => true,
				'query_var' 	=> true,
				'rewrite' 		=> array( 'slug' => 'article-categories' ),
				'capabilities' 	=> array(
						'manage_terms' 	=> 'manage_article_categories',
						'edit_terms' 	=> 'manage_article_categories',
						'delete_terms' 	=> 'manage_article_categories',
						'assign_terms' 	=> 'edit_issues'
						)
			)
		);
		
	}
	add_action( 'init', 'create_aip_magazine_cats_taxonomy', 0 );

}

if ( !function_exists( 'aip_magazine_no_categories_custom' ) ) {

    // Register Custom Taxonomy
    function aip_magazine_no_categories_custom() {

        //CODE TO REGISTER TAXONOMY

       $term_no_categories = get_terms('aip_magazine_issue_categories','parent=0&hide_empty=0');
       if ($term_no_categories == null){
           wp_insert_term(
               __('No Categories','aip_magazine'),
               'aip_magazine_issue_categories',
               array(
                   'slug'        => 'no-categories',
                   'parent'      => 0
               )
           );
           $term_no_categories = get_terms('aip_magazine_issue_categories','parent=0&hide_empty=0');
           $termId = $term_no_categories[0]->term_id;
           $issue_cat_no_categories = get_option('aip_magazine_issue_categories_' . $termId . '_meta');
           $issue_cat_no_categories['category_order'] = -1;
           update_option( 'aip_magazine_issue_categories_' . $termId . '_meta', $issue_cat_no_categories );
       }

    }

    // Hook into the 'init' action
    add_action( 'init', 'aip_magazine_no_categories_custom', 0 );
}
if ( !function_exists( 'aip_magazine_article_categories_columns' ) ) {
		
	/**
	 * Filters column headings for Article categories
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns
	 * @return array $columns
	 */
	function aip_magazine_article_categories_columns( $columns ) {
		
		// We add a Category Order field
		$columns['category_order'] = __( 'Category Order', 'aip_magazine' );
        unset($columns['description']);
		return $columns;
		
	}
	add_filter( 'manage_edit-aip_magazine_issue_categories_columns', 'aip_magazine_article_categories_columns', 10, 1 );

}

if ( !function_exists( 'aip_magazine_article_categories_sortable_columns' ) ) {
		
	/**
	 * Filters sortable columns
	 *
	 * @since 1.2.0
	 *
	 * @param array $columns
	 * @return array $columns
	 */
	function aip_magazine_article_categories_sortable_columns( $columns ) {
		
		$columns['category_order'] = 'category_order';
	
		return $columns;
		
	}
	add_filter( 'manage_edit-aip_magazine_issue_categories_sortable_columns', 'aip_magazine_article_categories_sortable_columns', 10, 1 );

}

if ( !function_exists( 'aip_magazine_issue_categories_sortable_column_orderby' ) )  {
	
	/**
	 * Filters sortable columns
	 *
	 * @since 1.2.0
	 * @todo misnamed originaly, should reallly be aip_magazine_article_categories
	 *
	 * @param array $terms
	 * @param array $taxonomies
	 * @param array $args
	 * @return array $terms
	 */
	function aip_magazine_issue_categories_sortable_column_orderby( $terms, $taxonomies, $args ) {
	
		global $hook_suffix;

		if ( ('post.php?post=' == $hook_suffix || 'post-new.php' == $hook_suffix ||'edit-tags.php' == $hook_suffix) && in_array( 'aip_magazine_issue_categories', $taxonomies )
				&& ( empty( $_GET['orderby'] ) && !empty( $args['orderby'] ) 
						|| ( !empty( $args['orderby'] ) && 'category_order' == $args['orderby'] ) ) ) {
				
			$sort = array();

		
			foreach ( $terms as $issue ) {


                if (isset($issue->term_id)){
                    $issue_meta = get_option( 'aip_magazine_issue_categories_' . $issue->term_id . '_meta' );

					$parent = $issue->parent;
					if ( !empty( $issue_meta['category_order'] ) ) {
						$sort[ sanitize_text_field($issue_meta['category_order']).'.'.$parent] = $issue;
					}
                }
				
			}
		
			if ( "asc" != $args['order'] )
				krsort( $sort );
			else
				ksort( $sort );


            if('post.php' == $hook_suffix || 'post-new.php' == $hook_suffix) ksort( $sort );

            $terms = $sort;
			
		}
		
		return $terms;
		
	}
	add_filter( 'get_terms', 'aip_magazine_issue_categories_sortable_column_orderby', 10, 3 );

}

if ( !function_exists( 'manage_aip_magazine_article_categories_custom_column' ) ) {
		
	/**
	 * Sets data for custom article cateagory columns
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $blank
	 * @param string $column_name
	 * @param int $term_id
	 *
	 * @return mixed Value of column for given term ID.
	 */
	function manage_aip_magazine_article_categories_custom_column( $blank, $column_name, $term_id ) {
		
		$issue_cat_meta = get_option( 'aip_magazine_issue_categories_' . $term_id . '_meta' );
	
		return $issue_cat_meta[$column_name];
	
	}
	add_filter( "manage_aip_magazine_issue_categories_custom_column", 'manage_aip_magazine_article_categories_custom_column', 10, 3 );
	
}

if ( !function_exists( 'aip_magazine_article_categories_add_form_fields' ) ) {
		
	/**
	 * Outputs HTML for new form fields in Article Categories
	 *
	 * @since 1.0.0
	 */
	function aip_magazine_article_categories_add_form_fields() {
        $id_no_categories = 0;
		$term_no_categories = get_terms('aip_magazine_issue_categories','category_order=-1');
		foreach($term_no_categories as $cat) {
			$id_no_categories = $cat->term_id;
        }

		if ($id_no_categories !=0){
        ?>
            <input type="hidden" id="hidden_id_no_categories" name="hidden_id_no_categories" value="<?php echo esc_attr($id_no_categories); ?>" />
        <?php
		}
		?>

		<div class="form-field">
			<label for="category_order"><?php _e( 'Category Order', 'aip_magazine' ); ?></label>
			<input type="text" name="category_order" id="category_order" />
		</div>
        <input type="hidden" id="hidden_insert_form" name="hidden_insert_form" value="insert" />


		<?php
		
	}
	add_action( 'aip_magazine_issue_categories_add_form_fields', 'aip_magazine_article_categories_add_form_fields' );

}

if ( !function_exists( 'aip_magazine_article_categories_edit_form_fields' ) ) {
		
	/**
	 * Outputs HTML for new form fields in Article Categories (on Edit form)
	 *
	 * @since 1.0.0
	 * @todo misnamed originaly, should reallly be aip_magazine_article_categories
	 */
	function aip_magazine_article_categories_edit_form_fields( $tag, $taxonomy ) {
	   
		$article_cat_meta = get_option( 'aip_magazine_issue_categories_' . $tag->term_id . '_meta' );
	    $get_categories = get_term_by('parent',$tag->term_id, 'aip_magazine_issue_categories');

        if (($tag->parent != -1)){
            echo '<script type="text/javascript">';
            echo 'jQuery("#parent").prop("disabled", true);';
            echo '</script>';
        }
		?>
		
		<tr class="form-field">
		<th valign="top" scope="row"><label for="category_order"><?php _e( 'Category Order', 'aip_magazine' ); ?></label></th>
		<td><input type="text" name="category_order" id="category_order" value="<?php echo esc_attr($article_cat_meta['category_order']); ?>" /></td>
		</tr>
        <input type="hidden" id="hidden_parent_issue_categories" name="hidden_parent_issue_categories" value="<?php echo esc_attr($get_categories->parent); ?>" />

    <?php
		
	}
	add_action( 'aip_magazine_issue_categories_edit_form_fields', 'aip_magazine_article_categories_edit_form_fields', 10, 2 );

}

if ( !function_exists( 'save_aip_magazine_article_categories_meta' ) ) {
		
	/**
	 * Saves form fields for Article Categories taxonomy
	 *
	 * @since 1.0.0
	 * @todo misnamed originaly, should reallly be aip_magazine_article_categories
	 *
	 * @param int $term_id Term ID
	 * @param int $taxonomy_id Taxonomy ID
	 */
	function save_aip_magazine_article_categories_meta( $term_id, $taxonomy_id ) {
        global $hook_suffix;
        $nonModificatoOrder = false;

        $new_insert = "start";

        if (!empty($_POST['hidden_insert_form']))
            $new_insert = sanitize_text_field($_POST['hidden_insert_form']);

		$issue_cat_meta = get_option( 'aip_magazine_issue_categories_' . $term_id . '_meta' );

        if (isset($_POST['parent'])){
            if ($_POST['parent'] == -1){
                if ($new_insert =='insert' && $hook_suffix == null){
                    wp_delete_term( $term_id, 'aip_magazine_issue_categories' );
                    echo __( 'Add Journal', 'aip_magazine' );
                    exit;

                }
                if ('edit-tags.php' == $hook_suffix && $_POST['action']=='editedtag'){
                     $location = 'term.php?action=edit&taxonomy=aip_magazine_issue_categories&tag_ID='.$term_id.'&post_type=aip_article&wp_http_referer='.admin_url('term.php?taxonomy=aip_magazine_issue_categories&post_type=aip_article&error=1&message=5');
                    wp_redirect( trim($location) );
                    wp_update_term( $term_id, 'aip_magazine_issue_categories', array( 'parent' => sanitize_text_field($_POST['hidden_parent_issue_categories']) ) );
                    exit;
                }
            }
        }
        if (isset($_POST['category_order']))
            $category_order = sanitize_text_field($_POST['category_order']);
        if (  $category_order != '' && is_numeric($category_order)){
                if (!empty($issue_cat_meta['category_order']) && ($issue_cat_meta['category_order'] ==  $category_order))
                    $nonModificatoOrder = true;
                else
                  $issue_cat_meta['category_order'] = (int)$category_order;
        }


        if (!empty($issue_cat_meta['category_order']) && $issue_cat_meta['category_order'] !== 0 && $issue_cat_meta['category_order'] > 0){
            //controllare che il numero d'ordine non sia inserito
            $aip_magazine_rubrica = get_aip_magazine_categories_ids('aip_magazine_issue_categories');
            $trovato = false;
            for ( $i = 0; $i < count($aip_magazine_rubrica); $i++) {
                $issue_cat_record = get_option( 'aip_magazine_issue_categories_' . $aip_magazine_rubrica[$i] . '_meta' );

                if (!$nonModificatoOrder){
                    if ($issue_cat_record['category_order'] == $issue_cat_meta['category_order'] ) {
                        $trovato = true;
                        break;
                    }
                }
            }
            if ($trovato){
                if ('edit-tags.php' == $hook_suffix && $_POST['action']=='editedtag'){
                    $location = 'term.php?action=edit&taxonomy=aip_magazine_issue_categories&tag_ID='.$term_id.'&post_type=aip_article&wp_http_referer='.admin_url('term.php?taxonomy=aip_magazine_issue_categories&post_type=aip_article&error=1&message=5');


                    wp_redirect( trim($location) );
                    exit;
                }else{

                    if ($new_insert =='insert'){
                        wp_delete_term( $term_id, 'aip_magazine_issue_categories' );
                        echo __('Category order already used','aip_magazine');
                        exit;
                    }else{
                        // caso della modifica-rapida
                        update_option( 'aip_magazine_issue_categories_' . $term_id . '_meta', $issue_cat_meta );
                    }
                }

            }else{
                update_option( 'aip_magazine_issue_categories_' . $term_id . '_meta', $issue_cat_meta );
             }
        }else{

            if ($new_insert =='insert' && $hook_suffix == null){
                wp_delete_term( $term_id, 'aip_magazine_issue_categories' );
                echo __('Category Order inconsistent, record not updated','aip_magazine');
                exit;

            }
        }

	}
	add_action( 'created_aip_magazine_issue_categories', 'save_aip_magazine_article_categories_meta', 10, 2 );
	add_action( 'edited_aip_magazine_issue_categories', 'save_aip_magazine_article_categories_meta', 10, 2 );

}
if ( !function_exists( 'show_only_parent_journlas_categories' ) )  {

    function show_only_parent_journlas_categories($dropdown_args, $taxonomy) {

        if ($taxonomy == 'aip_magazine_issue_categories') {
            $dropdown_args['taxonomy'] = 'aip_magazine_issue_journals';

        }
        return $dropdown_args;
    }
    add_filter('taxonomy_parent_dropdown_args', 'show_only_parent_journlas_categories', 10, 2);
}


if ( !function_exists( 'parent_gettext_with_context_categories' ) )  {

    function parent_gettext_with_context_categories($translated, $text) {

        if (is_admin() && $text == "Parent" && isset($_REQUEST['taxonomy']) == 'aip_magazine_issue_categories') {
            return __('Journals','aip_magazine');
        }

        return $translated;
    }
    add_filter('gettext_with_context', 'parent_gettext_with_context_categories', 20, 3);

}


if ( !function_exists( 'get_aip_magazine_categories_ids' ) )  {

    /**
     * Outputs array of Issue Terms IDs *
     * @since 1.0.0
     *
     * @param $taxonomy
     * @return array ID Issues
     */
    function get_aip_magazine_categories_ids($taxonomy) {

        global $wpdb;

        $term_ids = array();

        $term_taxonomy_children = $wpdb->get_col( 'SELECT term_id FROM ' . $wpdb->term_taxonomy . ' WHERE parent <> 0  AND taxonomy = "'.$taxonomy.'"');

        foreach( $term_taxonomy_children as $children_id )
            $term_ids[] = $children_id;
        if (isset($term_ids))
            return $term_ids;

    }

}
/**
 * Create HTML dropdown list of AipMagazine Article Categories.
 *
 * @since 1.2.6 
 * @uses Walker
 */
class Walker_AipMagazineCategoryDropdown extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	var $tree_type = 'category';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int    $depth    Depth of category. Used for padding.
	 * @param array  $args     Uses 'selected' and 'show_count' keys, if they exist. @see wp_dropdown_categories()
	 */
	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$cat_name = apply_filters('list_aip_magazine_cats', $category->name, $category);
		$output .= "\t<option class=\"level-$depth\" value=\"".esc_attr($category->slug)."\"";
		//if ( $category->slug === $args['selected'] )
		//	$output .= ' selected="selected"';
		$output .= '>';
		$output .= $pad.$cat_name;
		if ( $args['show_count'] )
			$output .= '&nbsp;&nbsp;('. $category->count .')';
		$output .= "</option>\n";
	}
}
