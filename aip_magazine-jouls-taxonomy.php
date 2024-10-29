<?php
/**
 * Registers AipMagazine c, Journals Taxonomy w/ Meta Boxes
 *
 * @package AipMagazine
 * @since 1.0.0
 */


if ( !function_exists( 'create_aip_magazine_jouls_taxonomy' ) ) {

    /**
     * Registers Article Journals taxonomy for AipMagazine
     *
     * @since 1.0.0
     * @todo misnamed originaly, should reallly be aip_magazine_article_journals
     */
    function create_aip_magazine_jouls_taxonomy() {
        $labels = array(
            'name'              => __( 'Journals', 'aip_magazine' ),
            'singular_name'     => __( 'Journal', 'aip_magazine' ),
            'search_items'      => __( 'Search Journal', 'aip_magazine'),
            'all_items'         => __( 'All Journals', 'aip_magazine' ),
            'parent_item'       => __( 'Parent Journal', 'aip_magazine' ),
            'parent_item_colon' => __( 'Parent Journal:', 'aip_magazine' ),
            'edit_item'         => __( 'Edit Journal', 'aip_magazine' ),
            'update_item'       => __( 'Update Journal', 'aip_magazine' ),
            'add_new_item'      => __( 'Add New Journal', 'aip_magazine' ),
            'new_item_name'     => __( 'New Journal Name', 'aip_magazine' ),
            'menu_name'         => __( 'Journals', 'aip_magazine' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'article-journals' ),
        );

        register_taxonomy( 'aip_magazine_issue_journals', array( ), $args );
        wp_insert_term('Journal','aip_magazine_issue_journals');
    }
    add_action( 'init', 'create_aip_magazine_jouls_taxonomy', 0 );

}
if ( !function_exists( 'aip_magazine_article_journals_add_form_fields' ) ) {

    /**
     * Outputs HTML for new form fields in Article Categories
     *
     * @since 1.0.0
     */
    function aip_magazine_article_journals_add_form_fields() {

        ?>

        <div class="form-field">
            <label for="issn"><?php _e( 'ISSN', 'aip_magazine' ); ?> (only in PRO version)</label>
        </div>


    <?php

    }
    add_action( 'aip_magazine_issue_journals_add_form_fields', 'aip_magazine_article_journals_add_form_fields' );

}
if ( !function_exists( 'aip_magazine_article_journals_edit_form_fields' ) ) {

    /**
     * Outputs HTML for new form fields in Article Categories (on Edit form)
     *
     * @since 1.0.0
     * @todo misnamed originaly, should reallly be aip_magazine_article_categories
     */
    function aip_magazine_article_journals_edit_form_fields( $tag, $taxonomy ) {



        $article_journal_meta = get_option( 'aip_magazine_issue_journals_' . $tag->term_id . '_meta' );

        echo '<script type="text/javascript">';
        echo 'jQuery(".term-parent-wrap").hide();';
        echo '</script>';


        ?>

        <tr class="form-field">
            <th valign="top" scope="row"><label for="issn"> <?php _e( 'ISSN', 'aip_magazine' ); ?> (only in PRO version)</label></th>
            <td></td>
        </tr>

    <?php

    }
    add_action( 'aip_magazine_issue_journals_edit_form_fields', 'aip_magazine_article_journals_edit_form_fields', 10, 2 );

}

if ( !function_exists( 'save_aip_magazine_article_journals_meta' ) ) {

    /**
     * Saves form fields for Issues taxonomy
     *
     * @since 1.0.0
     * @todo misnamed originaly, should reallly be aip_magazine_article_journals
     *
     * @param int $term_id Term ID
     * @param int $taxonomy_id Taxonomy ID
     */
    function save_aip_magazine_article_journals_meta( $term_id, $taxonomy_id ) {


        $issue_journal_meta = get_option( 'aip_magazine_issue_journals_' . $term_id . '_meta' );


        if ( !empty( $_POST['issn'] ) )
            $issue_journal_meta['issn'] = sanitize_text_field($_POST['issn']);

        update_option( 'aip_magazine_issue_journals_' . $term_id . '_meta', $issue_journal_meta );




    }

    add_action( 'created_aip_magazine_issue_journals', 'save_aip_magazine_article_journals_meta', 10, 2 );
    add_action( 'edited_aip_magazine_issue_journals', 'save_aip_magazine_article_journals_meta', 10, 2 );


}