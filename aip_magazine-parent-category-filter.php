<?php
/**
 * Created by PhpStorm.
 * User: greca
 * Date: 03/03/16
 * Time: 15.10
 */

class Aip_Magazine_Parent_Category_Filter {
    const TEXT_DOMAIN = 'parent-category-filter';

    public $allow_taxonomies = array();

    public $taxonomy = '';

    public $parent_taxonomy = 'aip_magazine_issue_journals';


    public function __construct() {


        $this->taxonomy = ( isset( $_REQUEST['taxonomy'] ) ) ? $_REQUEST['taxonomy'] : '';
        if (strcasecmp($this->taxonomy,'aip_magazine_issue') === 0 || strcasecmp($this->taxonomy,'aip_magazine_issue_categories') === 0)
           $this->allow_taxonomies = array(sanitize_key($_REQUEST['taxonomy'] ));
    }

    public function is_target_taxonomy( $taxonomy ) {

        return in_array( $taxonomy, apply_filters( 'parent-category-filter-allow-taxonomies', $this->allow_taxonomies ) );

    }

    public function register_hooks() {

        add_action( 'load-edit-tags.php', array( &$this, 'edit_tags_php' ) );

    }

    public function plugins_loaded() {

        load_plugin_textdomain( self::TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ).'/languages' );

    }

    public function edit_tags_php() {
        $this->taxonomy = ( isset( $_REQUEST['taxonomy'] ) ) ? sanitize_key($_REQUEST['taxonomy']) : '';
        if ( '' === $this->taxonomy ) {
            return;
        }
        if ( ! $this->is_target_taxonomy( $this->taxonomy ) ) {
            return;
        }
        add_filter( 'get_terms_args', array( &$this, 'get_terms_args' ), 10, 2 );
        add_action( "after-{$this->taxonomy}-table", array( &$this, 'after_taxonomy_table' ) );

    }

    public function get_terms_args( $args, $taxonomies ) {

        if ('' === $this->parent_search() && empty($_REQUEST['s'])) {
            $args['parent'] = 0;
            $args['hide_empty'] = 0;
            return $args;
        }

        /**
         * ignore when "wp_dropdown_categories()"
         *   in "/[wp-admin]/edit-tags.php"
         */

        if ( array_key_exists( 'selected', $args ) && $this->call_from( 'wp_dropdown_categories' ) && '' !== $this->parent_search()) {
            return $args;
        }


        /**
         * set 'parent' when "wp_count_terms()"
         *   in "WP_Terms_List_Table::prepare_items()"
         */
        if ( 'count' === $args[ 'fields' ] && $this->call_from( 'prepare_items' ) && '' !== $this->parent_search()) {
           $args['include'] = $this->get_term_children_journals($this->parent_search(),$this->taxonomy);

        }

        /**
         * set "WP_Terms_List_Table::callback_args['orderby']" value ' '(temporary)
         *   because pass to condition "if ( is_taxonomy_hierarchical( $taxonomy ) && ! isset( $args['orderby'] ) )"
         *     in "WP_Terms_List_Table::display_rows_or_placeholder()"
         *     via "WP_Terms_List_Table::display()"
         */
          if ( $this->call_from( 'prepare_items' ) && '' !== $this->parent_search()) {
               global $wp_list_table;
               if (  $wp_list_table ) {

                   $wp_list_table->callback_args['orderby'] = ( isset( $_GET['orderby'] ) ) ? $_GET['orderby'] : '';

               }
            }
        /**
         * set 'child_of' when "get_terms"
         *   in "WP_Terms_List_Table::display_rows_or_placeholder()"
         *   via "WP_Terms_List_Table::display()"
         */
        if ( 'all' === $args['fields'] && $this->call_from( 'display_rows_or_placeholder' ) && '' !== $this->parent_search()) {
            $args['include'] = $this->get_term_children_journals($this->parent_search(),$this->taxonomy);
        }



        return $args;
    }

    public function after_taxonomy_table( $taxonomy) {

        global $post_type;

        ?>
        <div id="parent_search_wrap" style="display: none;">
            <form id="parent_search_form" class="search-form" method="get">
                <input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy ); ?>" />
                <input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>" />
                <label for="parent_search"><?php _e( 'Filter by Journal', 'aip_magazine' ) ?></label>
                <select name="parent_search" id="parent_search" class="postform">
                    <option value="-1" selected> </option>
                    <?php
                    $terms = get_terms( array(
                        'taxonomy' => 'aip_magazine_issue_journals',
                        'hide_empty' => false,
                    ) );
                    foreach ( $terms as $term ) {
                        echo ( "<option class='level-0' value='" . $term->term_id . "' />" . $term->name . "</option>" );
                    }
                    ?>
                </select>

            </form>
        </div>

        <?php
        wp_enqueue_script(
            'parent-category-filter',
            plugins_url( 'js/aip_magazine-parent-category-filter.js' , __FILE__ ),
            array('jquery'),
            false,
            true
        );
    }

    public function call_from( $function ) {

        $db = debug_backtrace();
        if ( ! is_array( $db ) ) {
            return false;
        }
        foreach ( $db as $call_from  ) {
            if ( ! is_array( $call_from ) ) {
                continue;
            }
            if ( ! array_key_exists( 'function', $call_from ) ) {
                continue;
            }
            if ( $function === $call_from['function'] ) {
                return true;
            }
        }
        return false;

    }

    public function parent_search() {

        if ( ! isset( $_REQUEST['parent_search'] ) ) {
            return '';
        }
        if ( (int)$_REQUEST['parent_search'] <= 0 ) {
            return '';
        }
        $tax = get_term((int)sanitize_text_field($_REQUEST['parent_search']), $this->parent_taxonomy );
        $children = $this->get_term_children_journals( $tax->term_id, $this->taxonomy );
        if ( ! $children  ) {
            return '';
        } else {
            return (int)$_REQUEST['parent_search'];
        }
    }



    private function get_term_children_journals( $parent, $taxonomy ) {
        global $wpdb;
        $term_taxonomy_children = $wpdb->get_col( 'SELECT term_id FROM ' . $wpdb->term_taxonomy . ' WHERE parent = '. $parent.' AND taxonomy = "'.$taxonomy.'"');

        foreach( $term_taxonomy_children as $children_id )
            $term_ids[] = $children_id;
        if (isset($term_ids))
            return $term_ids;
    }


}
