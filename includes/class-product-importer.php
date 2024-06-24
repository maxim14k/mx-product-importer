<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Product_Importer {

    public static function import_products() {
        $file_url = get_option( 'mpi_file_url', '' );
        $import_limit = get_option( 'mpi_import_limit', '' );

        if ( empty( $file_url ) ) {
            error_log( 'File URL is not set.' );
            return;
        }

        $file_content = file_get_contents( $file_url );

        if ( $file_content === false ) {
            error_log( 'Failed to retrieve file content.' );
            return;
        }

        $xml = simplexml_load_string( $file_content );

        if ( $xml === false ) {
            error_log( 'Failed to parse XML content.' );
            return;
        }

        $imported_count = 0;

        foreach ( $xml->product as $product ) {
            if ( ! empty( $import_limit ) && $imported_count >= $import_limit ) {
                break;
            }

            $data = [
                (string) $product['name'],
                (string) $product['white_price'],
                (string) $product['description']
            ];

            if ( ! empty( $data ) ) {
                self::import_product( $data );
                $imported_count++;
            }
        }

        if ( $imported_count > 0 ) {
            $current_time = date('Y-m-d H:i:s');
            error_log( "[$current_time] Successfully imported $imported_count products." );
        }
    }

    private static function import_product( $data ) {
        // Example data: $data[0] - Name, $data[1] - Price, $data[2] - Description
        $product_data = array(
            'post_title'   => $data[0],
            'post_content' => $data[2],
            'post_status'  => 'publish',
            'post_type'    => 'product',
        );

        $product_id = wp_insert_post( $product_data );

        if ( $product_id ) {
            update_post_meta( $product_id, '_price', $data[1] );
        }
    }
}

?>
