<?php
/**
 * Registers AipMagazine Article, Tags Taxonomy w/ Meta Boxes
 *
 * @package AipMagazine
 * @since 1.0.0
 */
 
if ( !function_exists( 'create_aip_magazine_tags_taxonomy' ) ) {
		
	/**
	 * Registers Article Category taxonomy for AipMagazine
	 *
	 * @since 1.0.0
	 * @todo misnamed originaly, should reallly be aip_magazine_article_tags
	 */
	function create_aip_magazine_tags_taxonomy() {
		
	  $labels = array(
	  
			'name' 					=> __( 'Article Tags', 'aip_magazine' ),
			'singular_name' 		=> __( 'Article Tag', 'aip_magazine' ),
			'search_items' 			=> __( 'Search Article Tags', 'aip_magazine' ),
			'all_items' 			=> __( 'All Article Tags', 'aip_magazine' ), 
			'parent_item' 			=> __( 'Parent Article Tag', 'aip_magazine' ),
			'parent_item_colon' 	=> __( 'Parent Article Tag:', 'aip_magazine' ),
			'edit_item' 			=> __( 'Edit Article Tag', 'aip_magazine' ), 
			'update_item' 			=> __( 'Update Article Tag', 'aip_magazine' ),
			'add_new_item' 			=> __( 'Add New Article Tag', 'aip_magazine' ),
			'new_item_name' 		=> __( 'New Article Tag', 'aip_magazine' ),
			'menu_name' 			=> __( 'Article Tags', 'aip_magazine' )
			
		); 	
	
		register_taxonomy(
			'aip_magazine_issue_tags', 
			array( ),
			array(
				'hierarchical' 	=> false,
				'labels' 		=> $labels,
				'show_ui' 		=> true,
				'show_tagcloud' => true,
				'query_var' 	=> true,
				'rewrite' 		=> array( 'slug' => 'article-tags' ),
				'capabilities' 	=> array(
						'manage_terms' 	=> 'manage_article_tags',
						'edit_terms' 	=> 'manage_article_tags',
						'delete_terms' 	=> 'manage_article_tags',
						'assign_terms' 	=> 'edit_issues'
						)
						
			)
		);
		
	}
	add_action( 'init', 'create_aip_magazine_tags_taxonomy', 0 );
	
}