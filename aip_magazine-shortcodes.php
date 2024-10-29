<?php
/**
 * Registers AipMagazine class for setting up AipMagazine shortcodes
 *
 * @package AipMagazine
 * @since 1.0.0
 */
	
if ( !function_exists( 'do_aip_magazine_articles' ) ) {

    /**
     * Outputs Article HTML from shortcode call
     *
     * @since 1.0.0
     *
     * @param array $atts Arguments passed through shortcode
     * @param null $article_format
     * @return string HTML output of AipMagazine Articles
     */
	function do_aip_magazine_articles( $atts, $article_format = NULL ) {
		
		global $post;
		
		$aip_magazine_settings = get_aip_magazine_settings();
		$results = '';
		$articles = array();
		$post__in = array();

        if (empty($atts['journal_id'])){
            $parent = $aip_magazine_settings['page_of_journals_articles_active_issue'];
        }else{
            $parent = $atts['journal_id'];
        }


        $defaults = array(
			'posts_per_page'    	=> -1,
			'offset'            	=> 0,
			'orderby'           	=> 'issue_order',
			'order'             	=> 'ASC',
			'article_format'		=> empty( $article_format ) ? $aip_magazine_settings['article_format'] : $article_format,
			'show_featured'			=> 1,
			'issue'					=> get_active_aip_magazine_issue($parent),
			'article_category'		=> 'all',
			'use_category_order'	=> 'false',
		);
	
		// Merge defaults with passed atts
		// Extract (make each array element its own PHP var
		extract( shortcode_atts( $defaults, $atts ) );

		
		$args = array(
			'posts_per_page'	=> $posts_per_page,
			'offset'			=> $offset,
            'post_type'			=> 'aip_article',
            'orderby'			=> $orderby,
			'order'				=> $order
		);
		
		if ( !$show_featured ) {
			
			$args['meta_query'] = array(
									'relation' => 'AND',
									array(
										'key' => '_featured_rotator',
										'compare' => 'NOT EXISTS'
									),
									array(
										'key' => '_featured_thumb',
										'compare' => 'NOT EXISTS'
									)
								);
			
		}
	
		$aip_magazine_issue = array(
			'taxonomy' 	=> 'aip_magazine_issue',
			'field' 	=> 'slug',
			'terms' 	=> $issue
		);
		
		$args['tax_query'] = array(
			$aip_magazine_issue
		);
		
		if ( !empty( $aip_magazine_settings['use_wp_taxonomies'] ) ) 
			$cat_type = 'category';
		else
			$cat_type = 'aip_magazine_issue_categories';

        if ('aip_magazine_issue_categories' === $cat_type )
            $use_category_order = true;


		if ( 'true' == $use_category_order && 'aip_magazine_issue_categories' === $cat_type ) {


			$count = 0;

			$terms = array();
			
			if ( 'all' === $article_category ) {
			
				$all_terms = get_terms( 'aip_magazine_issue_categories');
				
				foreach( $all_terms as $term ) {
				
					$issue_cat_meta = get_option( 'aip_magazine_issue_categories_' . $term->term_id . '_meta' );
						
					if ( !empty( $issue_cat_meta['category_order'] ) )
						$terms[ $issue_cat_meta['category_order'] ] = $term->slug;

						
				}
				
			} else {
			
				foreach( split( ',', $article_category ) as $term_slug ) {
					
					$term = get_term_by( 'slug', $term_slug, 'aip_magazine_issue_categories' );
				
					$issue_cat_meta = get_option( 'aip_magazine_issue_categories_' . $term->term_id . '_meta' );
						
					if ( !empty( $issue_cat_meta['category_order'] ) )
						$terms[ $issue_cat_meta['category_order'] ] = $term->slug;
					else
						$terms[ '-' . ++$count ] = $term->slug;
						
				}
			
			}
			
			if ($terms!=null) {
				ksort($terms);
			}

			if ( !empty( $terms ) ) {
				foreach( $terms as $term ) {

					$category = array(
						'taxonomy' 	=> $cat_type,
						'field' 	=> 'slug',
						'terms' 	=> $term,
					);

					$args['tax_query'] = array(
						'relation'	=> 'AND',
						$aip_magazine_issue,
						$category
					);

					$articles = array_merge( $articles, get_posts( $args ) );

				}
			}

			//And we want all articles not in a category
			$category = array(
				'taxonomy' 	=> $cat_type,
				'field'		=> 'slug',
				'terms'		=> $terms, 
				'operator'	=> 'NOT IN',
			);

			$args['tax_query'] = array(
                               'relation'      => 'AND',
                                $aip_magazine_issue,
                                $category
                        );

            $articles = array_merge( $articles, get_posts( $args ) );
			//Now we need to get rid of duplicates (assuming an article is in more than one category
			if ( !empty( $articles ) ) {
				
				foreach( $articles as $article ) {
				
					$post__in[] = $article->ID;
					
				}
				
				$args['post__in']	= array_unique( $post__in );
				$args['orderby']	= 'post__in';
				unset( $args['tax_query'] );
					
				$articles = get_posts( $args );
			
			}
			
		} else {
			
			if ( !empty( $article_category ) && 'all' !== $article_category ) {
					
				$category = array(
					'taxonomy' 	=> $cat_type,
					'field' 	=> 'slug',
					'terms' 	=> split( ',', $article_category ),
				);	
				
				$args['tax_query'] = array(
					'relation'	=> 'AND',
					$aip_magazine_issue,
					$category
				);
				
			}
				
			$articles = get_posts( $args );
			
		}
		
		$results .= '<div id="fascicolo" class="issue-box">';
	
		if ( $articles ) : 
		
			$old_post = $post;

            $term = get_term_by( 'slug', $issue, 'aip_magazine_issue' );
            $meta_options = get_option( 'aip_magazine_issue_' . $term->term_id . '_meta' );

			$results .='<header><h2 class="issue-box-issue-title">'. $term->name .'</h2></header>';

			if ( !empty( $meta_options['pdf_version'] ) ) {
                $results .='<div class="issue-box-col-sx">'
                    .wp_get_attachment_image( $meta_options['cover_image'], 'issue-box-img' ).
                    '<a id="wp_attachment_url" class="issue-box-download-link" target="_blank" href="'
                    . esc_url(wp_get_attachment_url( $meta_options['pdf_version'] ) ).
                    '">' . __( 'Download PDF', 'aip_magazine' ) .
                    '</a></div>';
            }else {
                if(!empty($meta_options['cover_image']))
                $results .='<div class="issue-box-col-sx">'.wp_get_attachment_image( $meta_options['cover_image'], 'issue-box-img aip_magazine-cover-image' ).'</div>';
            }

            $results .='<div class="issue-box-col-dx">';


			$prima_rubrica = true;
            $rubrica_in_corso = '';
			foreach( $articles as $article ) {
				
				$post = $article;
                $all_terms =  wp_get_object_terms($post->ID, 'aip_magazine_issue_categories');
                if(!empty($all_terms)){
                    if ($rubrica_in_corso != $all_terms[0]->name ) {
                        $rubrica_in_corso = $all_terms[0]->name;
                        if (!empty($rubrica_in_corso)) {
							if (!$prima_rubrica){
								$results .='</section>';
							}else{
								$prima_rubrica = false;
							}
							$results .='<section class="issue-box-section">';
							if ($rubrica_in_corso != 'No Categories') {
								$results .='<h3 class="issue-box-section-title">'.$rubrica_in_corso.'</h3>';
							}
                        }
                    }
                }
                $results .='<a href="'.esc_url( get_permalink() ).'" rel="bookmark">';
                $results .='<article id="post-'.$post->ID.' ?>" class="issue-box-article">';
                $results .='<h4 class="issue-box-article-title">'. $post->post_title.'</h4>';
                $results .='<p class="issue-box-article-author">'. get_post_meta( $post->ID, '_autori', true ).'</p>';
                $results .='</article></a>';
			}

            $results .='</div>';

			if ( get_option( 'aip_magazine_api_error_received' ) )
				$results .= '<div class="api_error"><p><a href="http://aip_magazine.com/" target="_blank">' . __( 'Issue Management by ', 'aip_magazine' ) . 'AipMagazine</a></div>';
		
			$post = $old_post;
	
		else :
			//carico comunque l'issue per verificare se è un PDF Archive e mostrare eventualmente copertina e PDF
            $issue = get_active_aip_magazine_issue('');

            $term = get_term_by( 'slug', $issue, 'aip_magazine_issue' );

			if ( !( $term == null ) ) {
				$meta_options = get_option( 'aip_magazine_issue_' . $term->term_id . '_meta' );
			}

			if ( !empty( $meta_options['issue_status'] ) )  {
				if ($meta_options['issue_status'] === 'PDF Archive'){
					$results .='<header><h2 class="issue-box-issue-title">'. $term->name .'</h2></header>';
					if ( !empty( $meta_options['pdf_version'] ) ){
						$results .= '<div class="issue-box-col-sx"><p class="aip_magazine_widget_issue_cover_image"><a href="'.apply_filters( 'aip_magazine_pdf_attachment_url', wp_get_attachment_url( $meta_options['pdf_version'] ), $meta_options['pdf_version'] ).'" target="_blank" >' . wp_get_attachment_image($meta_options['cover_image'] ) . '</a></p></div><div class="issue-box-col-sx"><h3>Per scaricare il file PDF del fascicolo puoi cliccare sulla copertina qui di fianco</h3></div>';
					}else{
						$results .= apply_filters( 'aip_magazine_no_articles_found_shortcode_message', '<div class="issue-box-col-full"><h3 class="aip_magazine-entry-title no-articles-found">' . __( 'No PDF related to the Issue', 'aip_magazine' ) . '</h3></div>' );
					}
				}else{
					$results .= apply_filters( 'aip_magazine_no_articles_found_shortcode_message', '<div class="issue-box-col-full"><h3 class="aip_magazine-entry-title no-articles-found">' . __( 'No article found', 'aip_magazine' ) . '</h3></div>' );
				}
			}
		endif;
		
		$results .= '</div>';
		
		wp_reset_postdata();
		
		return $results;
		
	}
	add_shortcode( 'aip_magazine_articles', 'do_aip_magazine_articles' );

}

if ( !function_exists( 'do_aip_magazine_title' ) ) {
	
	/**
	 * Outputs Issue Title HTML from shortcode call
	 *
	 * @since 1.1.8
	 *
	 * @param array $atts Arguments passed through shortcode
	 * @return string HTML output of Issue Title
	 */
	function do_aip_magazine_title( $atts ) {
		
		$aip_magazine_settings = get_aip_magazine_settings();
		
		$defaults = array(
			'issue' => get_active_aip_magazine_issue(''),
			'field'	=> 'slug'
		);
	
		// Merge defaults with passed atts
		// Extract (make each array element its own PHP var
		extract( shortcode_atts( $defaults, $atts ) );
		
		$term = get_term_by( $field, $issue, 'aip_magazine_issue' );
		
		return '<div class="aip_magazine_title">' . $term->name . '</div>';
		
	}
	add_shortcode( 'aip_magazine_issue_title', 'do_aip_magazine_title' );

}
	
if ( !function_exists( 'do_aip_magazine_archives' ) ) {
	
	/**
	 * Outputs Issue Archives HTML from shortcode call
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Arguments passed through shortcode
	 * @return string HTML output of Issue Archives
	 */
	function do_aip_magazine_archives( $atts ) {
		
		$aip_magazine_settings = get_aip_magazine_settings();
		
		$defaults = array(
                            'journal_id'    => $aip_magazine_settings['page_of_journals_articles_archives'],
                            'orderby' 		=> 'issue_order',
							'order'			=> 'DESC',
							'limit'			=> 0,
							'pdf_title'		=> $aip_magazine_settings['pdf_title'],
							'default_image'	=> $aip_magazine_settings['default_issue_image'],
							'args'			=> array( 'hide_empty' => 0 ),
						);
		extract( shortcode_atts( $defaults, $atts ) );
		
		if ( is_string( $args ) ) {
			$args = str_replace( '&amp;', '&', $args );
			$args = str_replace( '&#038;', '&', $args );
		}
		
		$args = apply_filters( 'do_aip_magazine_archives_get_terms_args', $args );
		$aip_magazine_issues = get_terms( 'aip_magazine_issue', $args );
		$archives = array();
		$archives_no_issue_order = array();

        if (empty($atts['journal_id'])){
            $parent = $defaults['journal_id'];
        }else{
            $parent = $atts['journal_id'];
        }

		foreach ( $aip_magazine_issues as $issue ) {
            if (!empty($parent) ){
                if($parent == $issue->parent){
                    $issue_meta = get_option( 'aip_magazine_issue_' . $issue->term_id . '_meta' );

                    // If issue is not a Draft, add it to the archive array;
                    if ( !empty( $issue_meta['issue_status'] ) && ( 'Draft' !== $issue_meta['issue_status'] || current_user_can( apply_filters( 'see_aip_magazine_draft_issues', 'manage_issues' ) ) ) ) {

                        switch( $orderby ) {

                            case "issue_order":
                                if ( !empty( $issue_meta['issue_order'] ) )
                                    $archives[ $issue_meta['issue_order'] ] = array( $issue, $issue_meta );
                                else
                                    $archives_no_issue_order[] = array( $issue, $issue_meta );
                                break;

							case "name":
								$archives[ $issue->name ] = array( $issue, $issue_meta );
								break;

                            case "term_id":
                                $archives[ $issue->term_id ] = array( $issue, $issue_meta );
                                break;

                        }

                    }
                }
            }
		}
		
		if ( 'issue_order' == $orderby && !empty( $archives_no_issue_order ) )
			$archives = array_merge( $archives_no_issue_order, $archives );
		
		if ( "DESC" == $order )
			krsort( $archives );
		else
			ksort( $archives );
			
		$archive_count = count( $archives ) - 1; //we want zero based
		
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		if ( !empty( $limit ) ) {
			$offset = ( $paged - 1 ) * $limit;
			$archives = array_slice( $archives, $offset, $limit );
		}
			
		$results = '<div aria-multiselectable="true" role="tablist" id="accordion" class="aip_magazine_archives_shortcode panel-group">';

        $anno_incorso = 0;
        $primo_giro = true;
        $contAccordion = 1;

		foreach ( $archives as $archive => $issue_array ) {

			$issue_meta = get_option( 'aip_magazine_issue_' . $issue_array[0]->term_id . '_meta' );
				
			$class = '';
			if ( 'Draft' === $issue_meta['issue_status'] )
				$class = 'aip_magazine_issue_draft';
			if ('Draft' !== $issue_meta['issue_status']) {

				if (0 == $aip_magazine_settings['page_for_articles']) {
					$article_page = get_bloginfo('wpurl') . '/' . apply_filters('aip_magazine_page_for_articles', 'article/');
				    }
				else {
					$article_page = get_page_link($aip_magazine_settings['page_for_articles']);
				    }

				$page_for_articles = $aip_magazine_settings['page_for_articles'];
				if ($page_for_articles == 0){
					$issue_url = get_term_link($issue_array[0]->slug, $issue_array[0]->taxonomy);
					}
				else {
					$issue_url = get_page_link($aip_magazine_settings['page_for_articles']) . '?issue=' . $issue_array[0]->slug;
					}

				if ( !empty( $aip_magazine_settings['use_issue_tax_links'] ) || is_wp_error( $issue_url ) ) {
                    $issue_url = add_query_arg( 'issue', $issue_array[0]->slug, $article_page );
                }

                if ( !empty( $issue_array[1]['pdf_version'] ) || !empty( $issue_meta['external_pdf_link'] ) ) {

                    $pdf_url = empty( $issue_meta['external_pdf_link'] ) ? apply_filters( 'aip_magazine_pdf_attachment_url', wp_get_attachment_url( $issue_array[1]['pdf_version'] ), $issue_array[1]['pdf_version'] ) : $issue_meta['external_pdf_link'];

                    $pdf_line = '<a href="' . esc_url($pdf_url) . '" class = "archive-download-link" target="' . $aip_magazine_settings['pdf_open_target'] . '">';

                    if ( 'PDF Archive' == $issue_array[1]['issue_status'] ) {

                        $issue_url = $pdf_url;
                        $pdf_line .= empty( $pdf_only_title ) ? $aip_magazine_settings['pdf_only_title'] : $pdf_only_title;

                    } else {

                        $pdf_line .= empty( $pdf_title ) ? $aip_magazine_settings['pdf_title'] : $pdf_title;

                    }

                    $pdf_line .= ' <i class="fa fa-download"></i></a>';

                } else {

                    $pdf_line = apply_filters( 'aip_magazine_pdf_version', '&nbsp;', $pdf_title, $issue_array[0] );

                }

                if ( !empty( $issue_meta['external_link'] ) )
                    $issue_url = apply_filters( 'archive_issue_url_external_link', $issue_meta['external_link'], $issue_url );


                if($anno_incorso != get_Year($issue_array[1]['issue_date'])){
                    $anno_incorso = get_Year($issue_array[1]['issue_date']);
                    if (!$primo_giro){
                        $results .= '</div></div></div>';
						$results .= '<div class="archive-panel"><div id="heading'. $contAccordion .'" role="tab" class="archive-panel-heading">
                                 <h4 class="archive-panel-title">
                                 <a aria-controls="collapse' . $contAccordion . '" aria-expanded="false" href="#collapse' . $contAccordion . '" data-parent="#accordion" data-toggle="collapse" class="collapsed">' . esc_html($anno_incorso) . '</a>
                                 </h4>
                                 </div>
                                 <div aria-labelledby="heading'. $contAccordion .'" role="tabpanel" class="panel-collapse collapse" id="collapse'. $contAccordion .'" aria-expanded="false" style="height: 0px;">
                                 <div class="archive-panel-body">';
                    }else{
                        $primo_giro = false;
						$results .= '<div class="archive-panel"><div id="heading'. $contAccordion .'" role="tab" class="archive-panel-heading">
                                 <h4 class="archive-panel-title">
                                 <a aria-controls="collapse' . $contAccordion . '" aria-expanded="true" href="#collapse' . $contAccordion . '" data-parent="#accordion" data-toggle="collapse" class="">' . esc_html($anno_incorso) . '</a>
                                 </h4>
                                 </div>
                                 <div aria-labelledby="heading'. $contAccordion .'" role="tabpanel" class="panel-collapse collapse in" id="collapse'. $contAccordion .'" aria-expanded="false">
                                 <div class="archive-panel-body">';
                    }
                    $contAccordion++;
                }


				if ( !empty( $issue_meta['external_link'] ) ) {
					$results .= '<p><a href="' . esc_url($issue_url ). '" target="_blank">' . esc_html($issue_array[0]->name) . '</a> </p>'  ;
				}else{
					$results .= '<p><a href="' . esc_url($issue_url ). '">' . $issue_array[0]->name . '</a> '.$pdf_line.'</p>'  ;
				}
			}
		}

        if (!$primo_giro)
           $results .= '</div></div></div>';

		if ( !empty( $limit ) ) {
		
			$url = remove_query_arg( array( 'page', 'paged' ) );
		
			$results .= '<div class="next_previous_archive_pagination">';
		
			if ( 0 === $offset && $limit < $archive_count ) {
				//Previous link only
				$results .= '<div class="alignleft"><a href="' . add_query_arg( 'paged', $paged + 1, $url ) . '">' . __( 'Previous Archives', 'aip_magazine' ) . '</a></div>';
				
			} else if ( $offset >= $archive_count ) {
				//Next link only
				$results .= '<div class="alignright"><a href="' . add_query_arg( 'paged', $paged - 1, $url ) . '">' . __( 'Next Archives', 'aip_magazine' ) . '</a></div>';
			} else {
				//Next and Previous Links
				$results .= '<div class="alignleft"><a href="' . add_query_arg( 'paged', $paged + 1, $url ) . '">' . __( 'Previous Archives', 'aip_magazine' ) . '</a></div>';
				$results .= '<div class="alignright"><a href="' . add_query_arg( 'paged', $paged - 1, $url ) . '">' . __( 'Next Archives', 'aip_magazine' ) . '</a></div>';
			}
			
			
			$results .= '</div>';
		}
		
		if ( get_option( 'aip_magazine_api_error_received' ) )
			$results .= '<div class="api_error"><p><a href="http://aip_magazine.com/" target="_blank">' . __( 'Issue Management by ', 'aip_magazine' ) . 'AipMagazine</a></div>';
			
		$results .= '</div>';
		
		return $results;
		
	}
	add_shortcode( 'aip_magazine_archives', 'do_aip_magazine_archives' );
	
}


if ( !function_exists( 'do_aip_magazine_featured_rotator' ) ) {
	
	/**
	 * Outputs Issue Featured Rotator Images HTML from shortcode call
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Arguments passed through shortcode
	 * @return string HTML output of Issue Featured Rotator Images
	 */
	function do_aip_magazine_featured_rotator( $atts ) {
		$results = '';

		$results .= '<div class="pro_alert"><h2>'.__( 'You have to install the PRO version to use this shortcode', 'aip_magazine' ).'</h2></div>';

		return $results;
		
	}
	add_shortcode( 'aip_magazine_featured_rotator', 'do_aip_magazine_featured_rotator' );

}

if ( !function_exists( 'do_aip_magazine_featured_thumbs' ) ) {

	/**
	 * Outputs Issue Featured Thumbnail Images HTML from shortcode call
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Arguments passed through shortcode
	 * @return string HTML output of Issue Featured Rotator Thumbnails
	 */
	function do_aip_magazine_featured_thumbs( $atts ) {
		
		global $post;
		$results = '';
		
		$aip_magazine_settings = get_aip_magazine_settings();
		
		$defaults = array(
			'content_type'		=> 'teaser',
			'posts_per_page'    => -1,
			'offset'            => 0,
			'orderby'           => 'menu_order',
			'order'             => 'DESC',
			'max_images'		=> 0,
			'issue'				=>  get_active_aip_magazine_issue('all'),
			'article_category'	=> '',
			'show_cats'			=> false,
		);
		
		// Merge defaults with passed atts
		// Extract (make each array element its own PHP var
		extract( shortcode_atts( $defaults, $atts ) );
		
		$args = array(
			'posts_per_page'	=> $posts_per_page,
			'offset'			=> $offset,
            'post_type'			=> 'aip_article',
            'orderby'			=> $orderby,
			'order'				=> $order,
			'meta_key'			=> '_featured_thumb',
			'issue' 			=> $issue,
		);

		if ( !empty( $aip_magazine_settings['use_wp_taxonomies'] ) ) 
			$cat_type = 'category';
		else
			$cat_type = 'aip_magazine_issue_categories';


		//costruisco gli array che utilizzo per comporre la tax_query
		$aip_magazine_issue = array(
			'taxonomy' 	=> 'aip_magazine_issue',
			'field' 	=> 'slug',
			'terms' 	=> explode( ',', $issue ),
		);

		$category = array(
			'taxonomy' 	=> 'aip_magazine_issue_categories',
			'field' 	=> 'slug',
			'terms' 	=> explode( ',', $article_category ),
		);


		if ( !empty( $issue ) && 'all' !== $issue ){ //nello shortcode ho ricevuto un issue in input e non è all

			if (!empty( $article_category ) && 'all' !== $article_category) { //nello shortcode ho ricevuto categories in input e non è all

				$args['tax_query'] = array(
					'relation'	=> 'AND',
					$aip_magazine_issue,
					$category
				);

			}else{ //nello shortcode ho ricevuto categories in input e non è all

				$args['tax_query'] = array(
					'relation'	=> 'AND',
					$aip_magazine_issue
				);

			}

		}else{ //nello shortcode non ho ricevuto un issue o ho ricevuto all

			if (!empty( $article_category ) && 'all' !== $article_category) { //nello shortcode ho ricevuto categories in input e non è all

				$args['tax_query'] = array(
					'relation'	=> 'AND',
					$category
				);

			}

		}
						
		$featured_articles = get_posts( $args );
		
		if ( $featured_articles ) : 
			
			$results .= '<div id="aip_magazine-featured-article-thumbs-wrap">';
		
			$count = 1;
			/* start the loop */
			foreach( $featured_articles as $article ) {
				
				if ( has_post_thumbnail( $article->ID ) ) {

					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $article->ID ), 'aip_magazine-featured-thumb-image' );
					$image = apply_filters( 'aip_magazine_featured_thumbs_article_image', $image, $article );
					
					$results .= apply_filters( 'aip_magazine_featured_thumbs_before_thumbnail_div', '', $article );
					$results .= '<div class="aip_magazine-featured-article-thumb">';
					$results .= apply_filters( 'aip_magazine_featured_thumbs_start_thumbnail_div', '', $article );
					
					$results .= apply_filters( 'aip_magazine_featured_thumbs_before_thumbnail_image', '', $article );
					$results .= apply_filters( 'aip_magazine_featured_thumbs_thumbnail_image', '<a class="aip_magazine-featured-thumbs-img" href="' . esc_url(get_permalink( $article->ID )) . '"><img src="' . $image[0] . '" width="' . $image[1] . '" height="' . $image[2] . '" alt="' . get_post_meta( $article->ID, '_teaser_text', true ) . '" /></a>', $article );
					$results .= apply_filters( 'aip_magazine_featured_thumbs_after_thumbnail_image', '', $article );
					
					
					if ( 'true' === $show_cats ) {
						$results .= apply_filters( 'aip_magazine_featured_thumbs_before_thumbnail_category', '', $article );
						$results .= apply_filters( 'aip_magazine_featured_thumbs_thumbnail_category', '<p class="aip_magazine-article-category">' . get_the_term_list( $article->ID, $cat_type ) . '</p>', $article );
						$results .= apply_filters( 'aip_magazine_featured_thumbs_after_thumbnail_category', '', $article );
					}
					

					$results .= apply_filters( 'aip_magazine_featured_thumbs_before_thumbnail_title', '', $article );
					$results .= apply_filters( 'aip_magazine_featured_thumbs_after_thumbnail_title', '<h3 class="aip_magazine-featured-thumb-title"><a href="' . esc_url(get_permalink( $article->ID )) . '">' . esc_html(get_the_title( $article->ID )) . '</a></h3>', $article );
					$results .= apply_filters( 'aip_magazine_featured_thumbs_after_thumbnail_title', '', $article );

					$results .= apply_filters( 'aip_magazine_featured_thumbs_before_thumbnail_content', '', $article );
					switch ( $content_type ) {
								
							case 'excerpt':
								$results .= apply_filters( 'aip_magazine_featured_thumbs_thumbnail_content', '<p class="aip_magazine-featured-thumb-content">' . get_aip_magazine_article_excerpt( $article->ID ) . '</p>', $article );	
								break;
								
							case 'teaser':	
							default:					
								$results .= apply_filters( 'aip_magazine_featured_thumbs_thumbnail_content', '<p class="featured-thumb-content">' . get_post_meta( $article->ID, '_teaser_text', true ) . '</p>', $article );
								break;
								
					}
					$results .= apply_filters( 'aip_magazine_featured_thumbs_after_thumbnail_content', '', $article );
					
					$results .= apply_filters( 'aip_magazine_featured_thumbs_before_thumbnail_byline', '', $article );
					if ( isset($aip_magazine_settings['show_thumbnail_byline']) ) {
						
						$authors = get_aip_magazine_authors( $article );

						if ( !empty( $authors ) ) {
							$results .= '<p class="featured-thumb-byline">' .  sprintf( __( 'By %s', 'aip_magazine' ), apply_filters( 'aip_magazine_author_name', $authors, $article->ID ) ) . '</p>';
						}
						
					}
					$results .= apply_filters( 'aip_magazine_featured_thumbs_before_thumbnail_byline', '', $article );
					
					$results .= apply_filters( 'aip_magazine_featured_thumbs_end_thumbnail_div', '', $article );
					$results .= '</div>';
					$results .= apply_filters( 'aip_magazine_featured_article_after_thumbnail_div', '', $article );

					if ( 0 != $max_images && $max_images <= $count )
						break;
						
					$count++;
					
				}
				
			}
			
			$results .= '</div>';
			
		endif;
		
		return $results;
		
	}
	add_shortcode( 'aip_magazine_featured_thumbnails', 'do_aip_magazine_featured_thumbs' );

}


if ( !function_exists( 'get_Year' ) ) {

    function get_Year($str_date){
       $idx = strrpos($str_date,'/');
       $year = substr($str_date,$idx+1,strlen($str_date));
       return $year;
    }

}
