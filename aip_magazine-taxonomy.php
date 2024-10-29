<?php
/**
 * Registers AipMagazine Issue Taxonomy w/ Meta Boxes
 *
 * @package AipMagazine
 * @since 1.0.0
 */

if ( !function_exists( 'create_issue_taxonomy' ) ) {

    /**
     * Registers AipMagazine Issue Taxonomy
     *
     * @since 1.0.0
     */
    function create_issue_taxonomy() {

        $labels = array(

            'name' 				=> __( 'Issues', 'aip_magazine' ),
            'singular_name' 	=> __( 'Issue', 'aip_magazine' ),
            'search_items'		=> __( 'Search Issues', 'aip_magazine' ),
            'all_items' 		=> __( 'All Issues', 'aip_magazine' ),
            'parent_item' 		=> __( 'Parent Issues', 'aip_magazine' ),
            'parent_item_colon' => __( 'Parent Issues:', 'aip_magazine' ),
            'edit_item' 		=> __( 'Edit Issues', 'aip_magazine' ),
            'update_item' 		=> __( 'Update Issues', 'aip_magazine' ),
            'add_new_item' 		=> __( 'Add New Issues', 'aip_magazine' ),
            'new_item_name' 	=> __( 'New Issue', 'aip_magazine' ),
            'menu_name' 		=> __( 'Issues', 'aip_magazine' )

        );

        register_taxonomy(
            'aip_magazine_issue',
            array( 'aip_article' ),
            array(
                'hierarchical' 	=> true,
                'labels' 		=> $labels,
                'show_ui' 		=> true,
                'query_var' 	=> true,
                'rewrite' 		=> array( 'slug' => 'issue' ),
                'capabilities' 	=> array(
                    'manage_terms' 	=> 'manage_issues',
                    'edit_terms' 	=> 'manage_issues',
                    'delete_terms' 	=> 'manage_issues',
                    'assign_terms' 	=> 'edit_issues'
                )

            )
        );

    }
    add_action( 'init', 'create_issue_taxonomy', 0 );

}

if ( !function_exists( 'aip_magazine_issue_columns' ) ) {

    /**
     * Filters column headings for Issues
     *
     * @since 1.0.0
     *
     * @param array $columns
     * @return array $columns
     */
    function aip_magazine_issue_columns( $columns ) {

        $columns['name'] = __( 'Issue Title', 'aip_magazine' );
        $columns['issue_date'] = __( 'Issue Date', 'aip_magazine' );
        $columns['issue_order'] = __( 'Issue Order', 'aip_magazine' );
        $columns['issue_status'] = __( 'Issue Status', 'aip_magazine' );
        unset($columns['slug']);
        unset($columns['description']);
        unset($columns['posts']);

        return $columns;

    }
    add_filter( 'manage_edit-aip_magazine_issue_columns', 'aip_magazine_issue_columns', 10, 1 );

}

if ( !function_exists( 'aip_magazine_issue_sortable_columns' ) ) {

    /**
     * Filters sortable columns
     *
     * @since 1.0.0
     *
     * @param array $columns
     * @return array $columns
     */
    function aip_magazine_issue_sortable_columns( $columns ) {

        $columns['issue_date'] = 'issue_date';
        $columns['issue_order'] = 'issue_order';

        return $columns;

    }
    add_filter( 'manage_edit-aip_magazine_issue_sortable_columns', 'aip_magazine_issue_sortable_columns', 10, 1 );

}

if ( !function_exists( 'aip_magazine_issue_sortable_column_orderby' ) )  {

    /**
     * Filters sortable columns
     *
     * @since 1.0.0
     * @todo there is a better way to do this sort
     *
     * @param array $terms
     * @param array $taxonomies
     * @param array $args
     * @return array Of sorted terms
     */
    function aip_magazine_issue_sortable_column_orderby( $terms, $taxonomies, $args ) {

        global $hook_suffix;

		if ( ('post.php?post=' == $hook_suffix || 'post-new.php' == $hook_suffix || 'edit-tags.php' == $hook_suffix) && in_array( 'aip_magazine_issue', $taxonomies )
            && ( empty( $_GET['orderby'] ) && !empty( $args['orderby'] )
                || ( !empty( $args['orderby'] ) && 'issue_order' == $args['orderby'] ) ) ) {

            $sort = array();

            foreach ( $terms as $issue ) {


                if (isset($issue->term_id)){
                    $issue_meta = get_option( 'aip_magazine_issue_' . $issue->term_id . '_meta' );

                    $parent = $issue->parent;
					if ( !empty( $issue_meta['issue_order'] ) ) {
                        $sort[ $issue_meta['issue_order'].'.'.$parent] = $issue;
					}
                }

             }

            if ( "asc" != $args['order'] )
                krsort( $sort );
            else
                ksort( $sort );

            if('post-new.php' == $hook_suffix || 'post.php' == $hook_suffix) ksort( $sort );

            $terms = $sort;

        }

        return $terms;

    }
    add_filter( 'get_terms', 'aip_magazine_issue_sortable_column_orderby', 10, 3 );

}


if ( !function_exists( 'aip_magazine_issue_sortable_column_orderby_date' ) )  {

    /**
     * Filters sortable columns
     *
     * @since 1.0.0
     * @todo there is a better way to do this sort
     *
     * @param array $terms
     * @param array $taxonomies
     * @param array $args
     * @return array Of sorted terms
     */
    function aip_magazine_issue_sortable_column_orderby_date( $terms, $taxonomies, $args ) {

        global $hook_suffix;

        if ( 'edit-tags.php' == $hook_suffix && in_array( 'aip_magazine_issue', $taxonomies )
            && ( empty( $_GET['orderby'] ) && !empty( $args['orderby'] )
                || ( !empty( $args['orderby'] ) && 'issue_date' == $args['orderby'] ) ) ) {

            $sort = array();

            $i = 0;
			foreach ( $terms as $issue ) {
				$i++;
				$issue_meta = get_option( 'aip_magazine_issue_' . $issue->term_id . '_meta' );
				if ( !empty( $issue_meta['issue_date'] ) ) {
				    $idx = $issue_meta['issue_date'].' '.date('H:i:s');
				    $idx_strtotime = strtotime(str_replace("/", "-",$idx));
				    $sort[($idx_strtotime+$i)] = $issue;
				}
			}

            if ( "asc" != $args['order'] )
                krsort($sort);
            else
               ksort($sort);

            $terms = $sort;



        }

        return $terms;

    }
    add_filter( 'get_terms', 'aip_magazine_issue_sortable_column_orderby_date', 10, 3 );

}

if ( !function_exists( 'manage_aip_magazine_issue_custom_column' ) )  {

    /**
     * Sets data for custom article cateagory columns
     *
     * @since 1.0.0
     * @todo there is a better way to do this sort
     *
     * @param mixed $blank
     * @param string $column_name
     * @param int $term_id
     *
     * @return mixed Value of column for given term ID.
     */
    function manage_aip_magazine_issue_custom_column( $blank, $column_name, $term_id ) {

        $issue_meta = get_option( 'aip_magazine_issue_' . $term_id . '_meta' );

        if ( !empty( $issue_meta[$column_name] ) )
            return $issue_meta[$column_name];
        else
            return '';

    }
    add_filter( "manage_aip_magazine_issue_custom_column", 'manage_aip_magazine_issue_custom_column', 10, 3 );

}

if ( !function_exists( 'aip_magazine_issue_taxonomy_add_form_fields' ) )  {

    /**
     * Outputs HTML for new form fields in Issues
     *
     * @since 1.0.0
     */
    function aip_magazine_issue_taxonomy_add_form_fields() {

        ?>


        <div class="form-field">
            <label for="issue_date"><?php _e( 'Issue Date', 'aip_magazine' ); ?></label>
            <?php echo get_aip_magazine_date_issues ('', 1, 1, 0) ;?>
        </div>


        <div class="form-field">
            <label for="issue_status"><?php _e( 'Issue Status', 'aip_magazine' ); ?></label>
            <?php echo get_aip_magazine_issue_statuses(); ?>
        </div>

        <div class="form-field">
            <label for="issue_order"><?php _e( 'Issue Order', 'aip_magazine' ); ?></label>
            <input type="text" name="issue_order" id="issue_order" />
        </div>

    <?php

    }
    add_action( 'aip_magazine_issue_add_form_fields', 'aip_magazine_issue_taxonomy_add_form_fields' );



}

if ( !function_exists( 'get_aip_magazine_issue_statuses' ) )  {

    /**
     * Outputs HTML for AipMagazine Issue statuses
     *
     * @since 1.0.0
     *
     * @param bool|string $select Currently selected option
     * @return string select HTML of available statuses
     */
    function get_aip_magazine_issue_statuses( $select = false ) {

        $statuses = apply_filters( 'aip_magazine_issue_statuses', array( 'Draft','Live','PDF Archive') );

        $html = '<select name="issue_status" id="issue_status">';
        foreach ( $statuses as $status ) {

            $html .= '<option value="' . $status . '" ' . selected( $select, $status, false ) . '>' . __( $status, 'aip_magazine' ) . '</option>';

        }
        $html .= '</select>';

        return $html;

    }

}

if ( !function_exists( 'aip_magazine_issue_taxonomy_edit_form_fields' ) )  {

    /**
     * Outputs HTML for new form fields in Issue (on Edit form)
     *
     * @since 1.0.0
     */
    function aip_magazine_issue_taxonomy_edit_form_fields( $tag, $taxonomy ) {

        $defaults = array(
            'issue_date'        => '',
            'issue_status'		=> '',
            'issue_order'		=> '',
            'issue_volume'      => '',
            'cover_image'		=> '',
            'pdf_version'		=> '',
            'external_link'		=> '',
            'external_pdf_link'	=> '',
        );
        $issue_meta = get_option( 'aip_magazine_issue_' . $tag->term_id . '_meta' );
        $issue_meta = wp_parse_args( $issue_meta, $defaults );
        $get_issue = get_term_by('parent',$tag->term_id, 'aip_magazine_issue');

        if (($tag->parent != -1)){
            echo '<script type="text/javascript">';
            echo 'jQuery("#parent").prop("disabled", true);';
            echo '</script>';
        }
        ?>

        <tr class="form-field">
            <th valign="top" scope="row"><?php _e( 'Issue Date', 'aip_magazine' ); ?></th>
            <td><?php echo get_aip_magazine_date_issues($issue_meta['issue_date'],1,1,0); ?></td>
        </tr>
        <tr class="form-field">
            <th valign="top" scope="row"><?php _e( 'Issue Status', 'aip_magazine' ); ?></th>
            <td><?php echo get_aip_magazine_issue_statuses( $issue_meta['issue_status'] ); ?></td>
        </tr>

        <tr class="form-field">
            <th valign="top" scope="row"><?php _e( 'Issue Order', 'aip_magazine' ); ?></th>
            <td><input type="text" name="issue_order" id="issue_order" value="<?php echo esc_attr($issue_meta['issue_order']) ?>" /></td>
        </tr>

        <tr class="form-field">
            <th valign="top" scope="row"><?php _e( 'Issue Volume', 'aip_magazine' ); ?></th>
            <td><input type="text" name="issue_volume" id="issue_volume" value="<?php echo esc_attr($issue_meta['issue_volume']) ?>" /></td>
        </tr>
        <?php
        if ( ($_GET['remove_cover_image'] == $issue_meta['cover_image'])  ) {
            wp_delete_attachment( $issue_meta['cover_image'] );
            $issue_meta['cover_image'] = '';
            update_option( 'aip_magazine_issue_' . $tag->term_id . '_meta', $issue_meta );

        }

        if ( !empty( $issue_meta['cover_image'] ) ) {

            $view_image = '<p>' . wp_get_attachment_image( $issue_meta['cover_image'], 'aip_magazine-cover-image' ) . '</p>';
            $remove_image = '<p><a href="?' . http_build_query( wp_parse_args( array( 'remove_cover_image' => $issue_meta['cover_image'] ), $_GET ) ) . '">' . __( 'Remove Cover Image', 'aip_magazine' ) . '</a></p>';

        } else {

            $view_image = '';
            $remove_image = '';

        }
        ?>

        <tr class="form-field">
            <th valign="top" scope="row"><?php _e( 'Cover Image', 'aip_magazine' ); ?></th>
            <td><input type="file" name="cover_image" id="cover_image" value="" /><?php echo $view_image . $remove_image; ?></td>
        </tr>

        <?php
        if ( ($_GET['remove_pdf_version'] == $issue_meta['pdf_version'])  ) {
            wp_delete_attachment( $issue_meta['pdf_version'] );
            $issue_meta['pdf_version'] = '';
            update_option( 'aip_magazine_issue_' . $tag->term_id . '_meta', $issue_meta );

        }

        if ( !empty( $issue_meta['pdf_version'] ) ) {
            $view_pdf = '<p><a target="_blank" href="' . esc_url(wp_get_attachment_url( $issue_meta['pdf_version'] ) ). '">' . __( 'View PDF Version', 'aip_magazine' ) . '</a></p>';
            $remove_pdf = '<p><a href="?' . http_build_query( wp_parse_args( array( 'remove_pdf_version' => $issue_meta['pdf_version'] ), $_GET ) ). '">' . __( 'Remove PDF Version', 'aip_magazine' ) . '</a></p>';

        } else {

            $view_pdf = '';
            $remove_pdf = '';

        }
        ?>

        <tr class="form-field">
            <th valign="top" scope="row"><?php _e( 'External Issue Link', 'aip_magazine' ); ?></th>
            <td><input type="text" name="external_link" id="external_link" value="<?php echo esc_attr($issue_meta['external_link']) ?>" />
                <p class="description"><?php _e( 'Use http://domain-name - Leave empty if you do not want your issue to link to an external source.', 'aip_magazine' ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th valign="top" scope="row"><?php _e( 'PDF Version', 'aip_magazine' ); ?></th>
            <td>
                <input type="file" name="pdf_version" id="pdf_version" value="" />
                <?php
                echo $view_pdf . $remove_pdf;
                echo apply_filters( 'aip_magazine_pdf_version', '', __( 'Issue-to-PDF Generated PDF', 'aip_magazine' ), $tag );
                ?>
            </td>
        </tr>

        <tr class="form-field">
            <th valign="top" scope="row"><?php _e( 'External PDF Link', 'aip_magazine' ); ?></th>
            <td><input type="text" name="external_pdf_link" id="external_pdf_link" value="<?php echo  esc_attr($issue_meta['external_pdf_link']) ?>" />
                <p class="description"><?php _e( 'Leave empty if you do not want your PDF to link to an external source.', 'aip_magazine' ); ?></p>
            </td>
        </tr>
        <input type="hidden" id="hidden_parent_issue" name="hidden_parent_issue" value="<?php echo esc_attr($get_issue->parent); ?>" />
    <?php

    }
    add_action( 'aip_magazine_issue_edit_form_fields', 'aip_magazine_issue_taxonomy_edit_form_fields', 10, 2 );

}

if ( !function_exists( 'save_aip_magazine_issue_meta' ) ) {

    /**
     * Saves form fields for Issues taxonomy
     *
     * @since 1.0.0
     * @todo misnamed originaly, should reallly be aip_magazine_article_categories
     *
     * @param int $term_id Term ID
     * @param int $taxonomy_id Taxonomy ID
     */
    function save_aip_magazine_issue_meta( $term_id, $taxonomy_id) {

        global $hook_suffix;
        $nonModificatoOrder = false;
        //flag per distinguere in caso dell'insert al caso update
        $new_insert = "start";

        if (!empty($_POST['hidden_insert_form']))
            $new_insert = sanitize_text_field($_POST['hidden_insert_form']);

        $issue_meta = get_option( 'aip_magazine_issue_' . $term_id . '_meta' );


        if ($_POST['parent']==-1){
            if ($new_insert =='insert' && $hook_suffix == null){
                wp_delete_term( $term_id, 'aip_magazine_issue' );
                echo __( 'Add Journal', 'aip_magazine' );
                exit;

            }

            if ('edit-tags.php' == $hook_suffix && $_POST['action']=='editedtag'){
                $location = 'term.php?action=edit&taxonomy=aip_magazine_issue&tag_ID='.$term_id.'&post_type=aip_article
                             &wp_http_referer='.admin_url('term.php?taxonomy=aip_magazine_issue&post_type=aip_article&error=1&message=5');

                wp_redirect( trim($location) );
                wp_update_term( $term_id, 'aip_magazine_issue', array( 'parent' => sanitize_text_field($_POST['hidden_parent_issue'] )) );
                exit;
            }
        }

        if ( !empty( $_POST['jj'] ) && !empty( $_POST['mm'] ) && !empty( $_POST['aa'] )){
			/*checking date*/
			if (strlen($_POST['jj']) != 2 || strlen($_POST['aa']) != 4 ){
				if ($new_insert =='insert' && $hook_suffix == null) {
				    wp_delete_term( $term_id, 'aip_magazine_issue' );
				    echo __( 'Date format dd/mm/yyyy','aip_magazine'); exit;
				}else{
					if ('edit-tags.php' == $hook_suffix && $_POST['action']=='editedtag'){
                        $location = 'term.php?action=edit&taxonomy=aip_magazine_issue&tag_ID='.$term_id.'&post_type=aip_article
                             &wp_http_referer='.admin_url('term.php.php?taxonomy=aip_magazine_issue&post_type=aip_article&error=1&message=5');
					    wp_redirect( trim($location) );
					    exit;
					}
				}
			} else {
				$issue_meta['issue_date'] = sanitize_text_field($_POST['jj'] ).'/'. sanitize_text_field($_POST['mm']) .'/'. sanitize_text_field($_POST['aa']);
            }
		}else{
			if ($new_insert =='insert' && $hook_suffix == null) {
			    wp_delete_term( $term_id, 'aip_magazine_issue' );
			    echo __( 'Issue Date inconsistent, record not updated','aip_magazine');
			    exit;
			}

		}

        if ( !empty( $_POST['issue_status'] ) )
            $issue_meta['issue_status'] = sanitize_text_field($_POST['issue_status']);

        if ( !empty( $_POST['issue_volume'] ) )
            $issue_meta['issue_volume'] = sanitize_text_field($_POST['issue_volume']);


        $order = trim(sanitize_text_field($_POST['issue_order']));
        if (  $order != '' && is_numeric($order)){
            if (!empty($issue_meta['issue_order']) && ($issue_meta['issue_order'] == $order))
                $nonModificatoOrder = true;
            else
                $issue_meta['issue_order'] = (int)$order;

        }

        if ( !empty( $_FILES['cover_image']['name'] ) ) {
            require_once(ABSPATH . 'wp-admin/includes/admin.php');
            $id = media_handle_upload( 'cover_image', 0 ); //post id of Client Files page
            if ( is_wp_error($id) ) {
                $errors['upload_error'] = $id;
                $id = false;
            }
            $issue_meta['cover_image'] = $id;
        }

        if ( !empty( $_FILES['pdf_version']['name'] ) ) {
            require_once(ABSPATH . 'wp-admin/includes/admin.php');
            $id = media_handle_upload( 'pdf_version', 0 ); //post id of Client Files page
            if ( is_wp_error($id) ) {
                $errors['upload_error'] = $id;
                $id = false;
            }
            $issue_meta['pdf_version'] = $id;
        }

        $issue_meta['external_link'] = !empty( $_POST['external_link'] ) ? sanitize_text_field($_POST['external_link']) : '';
        $issue_meta['external_pdf_link'] = !empty( $_POST['external_pdf_link'] ) ? sanitize_text_field($_POST['external_pdf_link']) : '';





        if (!empty($issue_meta['issue_order']) && $issue_meta['issue_order'] !== 0){

            //controllare che il numero d'ordine non sia inserito
            $aip_magazine_fascicolo = get_aip_magazine_issues_ids( 'aip_magazine_issue');
            $trovato = false;
            for ( $i = 0; $i < count($aip_magazine_fascicolo); $i++) {
                $issue_meta_record = get_option( 'aip_magazine_issue_' . $aip_magazine_fascicolo[$i] . '_meta' );

                if (!$nonModificatoOrder){
                    if ($issue_meta_record['issue_order'] == $issue_meta['issue_order'] ) {
                        $trovato = true;
                        break;
                    }
                }
            }

            if ($trovato){

                if ('edit-tags.php' == $hook_suffix && $_POST['action']=='editedtag'){
                    $location = 'term.php?action=edit&taxonomy=aip_magazine_issue&tag_ID='.$term_id.'&post_type=aip_article
                                 &wp_http_referer='.admin_url('term.php.php?taxonomy=aip_magazine_issue&post_type=aip_article&error=1&message=5');

                    wp_redirect( $location );
                    exit;

                }else{
                    /* in questo tratto distinguo tra inserimento in cui voglio inserire
                     * un issue order già presente
                    */
                    if ($new_insert =='insert'){
                        wp_delete_term( $term_id, 'aip_magazine_issue' );
                        echo __( 'Issue Order already used','aip_magazine');
                        exit;
                    }else{
                        // caso della modifica-rapida
                        update_option( 'aip_magazine_issue_' . $term_id . '_meta', $issue_meta );
                    }
                }
            }else{
                // caso in cui l'issue order non presente
                update_option( 'aip_magazine_issue_' . $term_id . '_meta', $issue_meta );
            }
        }else{
            if ($new_insert =='insert' && $hook_suffix == null){
                wp_delete_term( $term_id, 'aip_magazine_issue' );
                echo __( 'Issue Order inconsistent, record not updated','aip_magazine');
                exit;

            }

        }

    }

        add_action( 'created_aip_magazine_issue', 'save_aip_magazine_issue_meta', 10, 2 );
        add_action( 'edited_aip_magazine_issue', 'save_aip_magazine_issue_meta', 10, 2 );


}


if ( !function_exists( 'get_aip_magazine_draft_issues' ) )  {

    /**
     * Outputs array of Issue Terms IDs for AipMagazine Issue statuses set to Draft
     *
     * @since 1.0.0
     *
     * @return array Draft Issues
     */
    function get_aip_magazine_draft_issues() {

        global $wpdb;

        $term_ids = array();

        $term_option_names = $wpdb->get_col( 'SELECT option_name FROM ' . $wpdb->options . ' WHERE option_name LIKE "aip_magazine_issue_%_meta" AND option_value LIKE "%Draft%"' );

        foreach( $term_option_names as $name )
            if ( preg_match( '/aip_magazine_issue_(\d+)_meta/', $name, $matches ) )
                $term_ids[] = $matches[1];

        return $term_ids;

    }

}

if ( !function_exists( 'get_aip_magazine_date_issues' ) )  {

    /**
     * Outputs array of Issue Terms IDs for AipMagazine Issue statuses set to Draft
     *
     * @since 1.0.0
     *
     * @param $issue_date
     * @param int $for_post
     * @param int $tab_index
     * @param int $multi
     * @return array date Issues
     */
    function get_aip_magazine_date_issues($issue_date, $for_post = 1, $tab_index = 0, $multi = 0 ) {

        global $wp_locale, $post;

        if ( $for_post && isset($post))
           (in_array($post->post_status, array('draft', 'pending') ) && (!$post->post_date_gmt || '0000-00-00 00:00:00' == $post->post_date_gmt ) );

        $tab_index_attribute = '';
        if ( (int) $tab_index > 0 )
            $tab_index_attribute = " tabindex=\"$tab_index\"";

        $time_adj = current_time('timestamp');


        if(!empty($issue_date)){
            $arr_date = explode('/',$issue_date);
            $cur_jj = $arr_date[0];
            $cur_mm = intval($arr_date[1]);
            $cur_aa = $arr_date[2];
        }else{
            $cur_jj = gmdate( 'd', $time_adj );
            $cur_mm = gmdate( 'm', $time_adj );
            $cur_aa = gmdate( 'Y', $time_adj );
        }

        $day = '<input type="text" ' . ( $multi ? '' : 'id="jj" ' ) . 'name="jj" value="' .esc_attr($cur_jj ). '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off"/>';
        $month = "<select " . ( $multi ? '' : 'id="mm" ' ) . "name=\"mm\"$tab_index_attribute>\n";
        for ( $i = 1; $i < 13; $i = $i +1 ) {
            $monthnum = zeroise($i, 2);
            $month .= "\t\t\t" . '<option value="' . esc_attr($monthnum) . '"';
            if ( $i == $cur_mm )
                $month .= ' selected="selected"';
            $month .= '>' . $monthnum . '-' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
        }
        $month .= '</select>';

        $year = '<input type="text" ' . ( $multi ? '' : 'id="aa" ' ) . 'name="aa" value="' . esc_attr($cur_aa ). '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" />';

        echo '<div id="aip_magazine_timestamp-wrap" class="aip_magazine_timestamp-wrap">';
        /* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
       echo($day. $month. $year);

        echo '<input type="hidden" id="issue_date" name="issue_date" value="" />' . "\n";


        echo '<input type="hidden" id="hidden_jj" name=""hidden_jj" value="'.esc_attr($cur_jj).'" />' . "\n";
        if (isset($_POST['mm'])){
            echo '<input type="hidden" id="hidden_mm" name="hidden_mm" value="'.esc_attr($_POST['mm']).'" />' . "\n";
        }
        if (isset($_POST['aa'])){
            echo '<input type="hidden" id="hidden_aa" name="hidden_aa" value="'.esc_attr($_POST['aa']).'" />' . "\n";
        }
        echo '<input type="hidden" id="hidden_insert_form" name="hidden_insert_form" value="insert" />' . "\n";
        echo '</div>';
        ?>


    <?php

    }

}
if ( !function_exists( 'show_only_parent_journlas' ) )  {

    function show_only_parent_journlas($dropdown_args, $taxonomy) {

        if ($taxonomy == 'aip_magazine_issue') {
            $dropdown_args['taxonomy'] = 'aip_magazine_issue_journals';

        }
        return $dropdown_args;
    }
    add_filter('taxonomy_parent_dropdown_args', 'show_only_parent_journlas', 10, 2);
}

if ( !function_exists( 'name_gettext_with_context' ) )  {

    function name_gettext_with_context($translated, $text) {
        if (isset($_REQUEST['taxonomy'])){
            if (is_admin() && $text == "Name" && $_REQUEST['taxonomy'] == 'aip_magazine_issue') {
                return __('Issue Title','aip_magazine');
            }
        }
        return $translated;
    }
    add_filter('gettext_with_context', 'name_gettext_with_context', 20, 3);

}
if ( !function_exists( 'parent_gettext_with_context' ) )  {

    function parent_gettext_with_context($translated, $text) {

        if (is_admin() && $text == "Parent" && $_REQUEST['taxonomy'] == 'aip_magazine_issue') {
            return __('Journals','aip_magazine');
        }

        return $translated;
    }
    add_filter('gettext_with_context', 'parent_gettext_with_context', 20, 3);

}

if ( !function_exists( 'get_aip_magazine_issues_ids' ) )  {

    /**
     * Outputs array of Issue Terms IDs *
     * @since 1.0.0
     *
     * @param $taxonomy
     * @return array ID Issues
     */
    function get_aip_magazine_issues_ids($taxonomy) {

        global $wpdb;

        $term_ids = array();

        $term_taxonomy_children = $wpdb->get_col( 'SELECT term_id FROM ' . $wpdb->term_taxonomy . ' WHERE parent <> 0  AND taxonomy = "'.$taxonomy.'"');

        foreach( $term_taxonomy_children as $children_id )
            $term_ids[] = $children_id;
        if (isset($term_ids))
            return $term_ids;

    }

}