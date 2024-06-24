<?php
/*
Plugin Name: Mx Product Importer
Description: Imports products from a file every day.
Version: 1.2
Author: Max Khudenko
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-product-importer.php';

function mpi_activate() {
    if ( get_option( 'mpi_daily_import_enabled', false ) && ! wp_next_scheduled( 'mpi_daily_event' ) ) {
        wp_schedule_event( time(), 'daily', 'mpi_daily_event' );
    }
}
register_activation_hook( __FILE__, 'mpi_activate' );

function mpi_deactivate() {
    wp_clear_scheduled_hook( 'mpi_daily_event' );
}
register_deactivation_hook( __FILE__, 'mpi_deactivate' );

add_action( 'mpi_daily_event', array( 'Product_Importer', 'import_products' ) );

// Function for manual testing
function mpi_manual_import() {
    Product_Importer::import_products();
    wp_redirect( add_query_arg( 'mpi_message', 'import_completed', wp_get_referer() ) );
    exit;
}
add_action( 'admin_post_mpi_manual_import', 'mpi_manual_import' );

function mpi_admin_menu() {
    add_menu_page( 'Mx Product Importer', 'Mx Product Importer', 'manage_options', 'mx-product-importer', 'mpi_admin_page' );
}
add_action( 'admin_menu', 'mpi_admin_menu' );

function mpi_admin_page() {
    ?>
    <div class="wrap">
        <h1>Mx Product Importer</h1>
        <?php if ( isset( $_GET['mpi_message'] ) && $_GET['mpi_message'] === 'import_completed' ): ?>
            <div id="message" class="updated notice is-dismissible">
                <p>Manual import completed.</p>
            </div>
        <?php endif; ?>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'mpi_settings_group' );
            do_settings_sections( 'mpi_settings_group' );
            $url = get_option( 'mpi_file_url', '' );
            $import_limit = get_option( 'mpi_import_limit', '' );
            $daily_import_enabled = get_option( 'mpi_daily_import_enabled', false );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">File URL</th>
                    <td>
                        <input type="text" name="mpi_file_url" value="<?php echo esc_attr( $url ); ?>" size="50" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Import Limit</th>
                    <td>
                        <input type="number" name="mpi_import_limit" value="<?php echo esc_attr( $import_limit ); ?>" size="10" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enable Daily Import</th>
                    <td>
                        <input type="checkbox" name="mpi_daily_import_enabled" value="1" <?php checked( $daily_import_enabled, 1 ); ?> />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="mpi_manual_import">
            <button type="submit" class="button button-primary">Run Manual Import</button>
        </form>
    </div>
    <?php
}

function mpi_register_settings() {
    register_setting( 'mpi_settings_group', 'mpi_file_url' );
    register_setting( 'mpi_settings_group', 'mpi_import_limit' );
    register_setting( 'mpi_settings_group', 'mpi_daily_import_enabled' );
}
add_action( 'admin_init', 'mpi_register_settings' );

function mpi_update_schedule() {
    if ( get_option( 'mpi_daily_import_enabled', false ) ) {
        if ( ! wp_next_scheduled( 'mpi_daily_event' ) ) {
            wp_schedule_event( time(), 'daily', 'mpi_daily_event' );
        }
    } else {
        wp_clear_scheduled_hook( 'mpi_daily_event' );
    }
}
add_action( 'update_option_mpi_daily_import_enabled', 'mpi_update_schedule' );
?>
