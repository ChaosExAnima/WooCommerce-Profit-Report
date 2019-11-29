<?php
/**
 * Plugin Name: WooCommerce Product Profit Report
 * Plugin URI: https://woocommerce.com/
 * Description: A plugin to add a report to WooCommerce with per-product profits.
 * Version: 1.0.0
 * Author: Echo
 * Author URI: https://echonyc.name
 * Text Domain: wc-product-profit-report
 *
 * @package WCProductProfitReport
 */

namespace EchoNYC\WCProductProfitReport;

defined( 'ABSPATH' ) || exit;

/**
 * Adds report callback.
 *
 * @param array $reports Array of WC reports.
 * @return array
 */
function filter_reports( array $reports ) : array {
	if ( isset( $reports['orders'], $reports['orders']['reports'] ) ) {
		$reports['orders']['reports']['product_profit'] = [
			'title'       => __( 'Product Profit', 'wc-product-profit-report' ),
			'description' => '',
			'callback'    => __NAMESPACE__ . "\\report",
		];
	}
	return $reports;
}

/**
 * Report display.
 *
 * @return void
 */
function report() : void {
	require_once plugin_dir_path( __FILE__ ) . '/class-profitreport.php';
	( new ProfitReport() )->output_report();
}

add_filter( 'woocommerce_admin_reports', __NAMESPACE__ . '\filter_reports' );
