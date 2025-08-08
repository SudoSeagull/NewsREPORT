<?php
/**
 * Plugin Name: NewsREPORT Links
 * Description: Registers a 'drg_link' custom post type with a 'section' taxonomy, plus external URL and weight fields. Includes a CSV importer.
 * Version: 1.0.1
 * Author: SudoSeagull
 * Author URI: https://github.com/SudoSeagull
 * License: MIT
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Drudge_Links {
    const CPT = 'drg_link';
    const TAX = 'section';
    const META_URL = '_drudge_external_url';
    const META_WEIGHT = '_drudge_weight';

    public function __construct() {
        add_action( 'init', [ $this, 'register_cpt_tax' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_meta' ], 10, 2 );
        add_action( 'admin_menu', [ $this, 'register_import_page' ] );
    }

    public function register_cpt_tax() {
        register_post_type( self::CPT, [
            'label'           => __( 'Links', 'drudge-links' ),
            'public'          => true,
            'has_archive'     => false,
            'show_in_rest'    => true,
            'menu_icon'       => 'dashicons-admin-links',
            'supports'        => [ 'title', 'excerpt' ],
            'rewrite'         => [ 'slug' => 'link' ],
            'labels'          => [
                'name'          => __( 'Links', 'drudge-links' ),
                'singular_name' => __( 'Link', 'drudge-links' ),
                'add_new'       => __( 'Add New', 'drudge-links' ),
                'add_new_item'  => __( 'Add New Link', 'drudge-links' ),
                'edit_item'     => __( 'Edit Link', 'drudge-links' ),
            ],
        ] );

        register_taxonomy( self::TAX, self::CPT, [
            'label'        => __( 'Sections', 'drudge-links' ),
            'public'       => true,
            'hierarchical' => true,
            'show_in_rest' => true,
        ] );
    }

    public function add_meta_boxes() {
        add_meta_box(
            'drudge_link_meta',
            __( 'Link Details', 'drudge-links' ),
            [ $this, 'render_meta_box' ],
            self::CPT,
            'side'
        );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'drudge_meta_save', 'drudge_meta_nonce' );
        $url    = get_post_meta( $post->ID, self::META_URL, true );
        $weight = get_post_meta( $post->ID, self::META_WEIGHT, true );
        ?>
        <p>
            <label for="drudge_external_url"><strong><?php esc_html_e( 'External URL', 'drudge-links' ); ?></strong></label>
            <input type="url" id="drudge_external_url" name="drudge_external_url" class="widefat" value="<?php echo esc_attr( $url ); ?>" placeholder="https://example.com/article"/>
        </p>
        <p>
            <label for="drudge_weight"><strong><?php esc_html_e( 'Weight (higher = higher placement)', 'drudge-links' ); ?></strong></label>
            <input type="number" id="drudge_weight" name="drudge_weight" class="widefat" value="<?php echo esc_attr( $weight ); ?>" step="1" />
        </p>
        <?php
    }

    public function save_meta( $post_id, $post ) {
        if ( $post->post_type !== self::CPT ) { return; }
        if ( ! isset( $_POST['drudge_meta_nonce'] ) || ! wp_verify_nonce( $_POST['drudge_meta_nonce'], 'drudge_meta_save' ) ) { return; }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
        if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

        if ( isset( $_POST['drudge_external_url'] ) ) {
            update_post_meta( $post_id, self::META_URL, esc_url_raw( $_POST['drudge_external_url'] ) );
        }
        if ( isset( $_POST['drudge_weight'] ) ) {
            update_post_meta( $post_id, self::META_WEIGHT, intval( $_POST['drudge_weight'] ) );
        }
    }

    public function register_import_page() {
        add_submenu_page(
            'edit.php?post_type=' . self::CPT,
            __( 'Import Links (CSV)', 'drudge-links' ),
            __( 'Import CSV', 'drudge-links' ),
            'manage_options',
            'drudge-import-csv',
            [ $this, 'render_import_page' ]
        );
    }

    public function render_import_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'drudge-links' ) );
        }

        if ( isset( $_POST['drudge_csv_nonce'] ) && wp_verify_nonce( $_POST['drudge_csv_nonce'], 'drudge_csv_upload' ) ) {
            if ( ! empty( $_FILES['csv_file']['tmp_name'] ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                $overrides = [ 'test_form' => false, 'mimes' => [ 'csv' => 'text/csv' ] ];
                $file = wp_handle_upload( $_FILES['csv_file'], $overrides );

                if ( ! empty( $file['error'] ) ) {
                    echo '<div class="error"><p>' . esc_html( $file['error'] ) . '</p></div>';
                } else {
                    $this->import_csv( $file['file'] );
                    echo '<div class="updated"><p>' . esc_html__( 'CSV imported successfully.', 'drudge-links' ) . '</p></div>';
                }
            }
        }

        // Output form without closing PHP tags repeatedly (keep it simple)
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Import Links from CSV', 'drudge-links' ) . '</h1>';
        echo '<form method="post" enctype="multipart/form-data">';
        wp_nonce_field( 'drudge_csv_upload', 'drudge_csv_nonce' );
        echo '<input type="file" name="csv_file" accept=".csv" required />';
        echo '<p class="description">' . esc_html__( 'CSV columns: Title, URL, Section, Weight, Excerpt (header row will be skipped)', 'drudge-links' ) . '</p>';
        submit_button( __( 'Import CSV', 'drudge-links' ) );
        echo '</form></div>';
    }

    private function import_csv( $path ) {
        if ( ! file_exists( $path ) ) { return; }
        if ( ( $handle = fopen( $path, 'r' ) ) === false ) { return; }

        $row = 0;
        while ( ( $data = fgetcsv( $handle ) ) !== false ) {
            $row++;
            if ( $row === 1 ) { continue; } // skip header

            list( $title, $url, $section, $weight, $excerpt ) = array_pad( $data, 5, '' );
            $post_id = wp_insert_post( [
                'post_type'   => self::CPT,
                'post_status' => 'publish',
                'post_title'  => sanitize_text_field( $title ),
                'post_excerpt'=> wp_kses_post( $excerpt ),
            ] );

            if ( is_wp_error( $post_id ) || ! $post_id ) { continue; }

            if ( $section ) {
                wp_set_object_terms( $post_id, [ sanitize_text_field( $section ) ], self::TAX );
            }
            update_post_meta( $post_id, self::META_URL, esc_url_raw( $url ) );
            update_post_meta( $post_id, self::META_WEIGHT, intval( $weight ) );
        }
        fclose( $handle );
    }
}

new Drudge_Links();
