<?php
/**
 * Main report class.
 *
 * @package WCProductProfitReport
 */

namespace EchoNYC\WCProductProfitReport;

use WC_Admin_Report;

/**
 * Profit report class.
 */
class ProfitReport extends WC_Admin_Report {

	/**
	 * Product ids.
	 *
	 * @var array
	 */
	public $product_ids = array();

	/**
	 * Constructor.
	 *
	 * @returns void
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'woocommerce_checkout_create_order', [ $this, 'clear_report_cache' ] );
	}

	/**
	 * Output the report.
	 *
	 * @return void
	 */
	public function output_report() : void {
		$this->chart_colours = array(
			'sales_amount' => '#3498db',
			'item_count'   => '#d4d9dc',
		);

		echo '<div id="poststuff" class="woocommerce-reports-wide">
			<div class="postbox">
				<div class="inside">';
		$this->get_main_chart();
		echo '</div></div></div>';
	}

	/**
	 * Get the main chart.
	 *
	 * @return void
	 */
	public function get_main_chart() : void {
		if ( empty( $this->product_ids ) ) {
			$this->product_ids = wc_get_products(
				[
					'limit'   => 1,
					'orderby' => 'date',
					'order'   => 'DESC',
					'return'  => 'ids',
					'status'  => 'publish',
				]
			);
		}

		$query_data = [
			'ID'                      => [
				'type'     => 'post_data',
				'function' => 'COUNT',
				'name'     => 'total_orders',
				'distinct' => true,
			],
			'_payment_method'         => [
				'type'      => 'meta',
				'function'  => '',
				'name'      => 'payment_method',
				'join_type' => 'LEFT',
			],
			'_created_via'         => [
				'type'      => 'meta',
				'function'  => '',
				'name'      => 'created_by',
				'join_type' => 'LEFT',
			],
			'_order_total'            => [
				'type'     => 'meta',
				'function' => 'SUM',
				'name'     => 'order_total',
			],
			'_stripe_fee'             => [
				'type'      => 'meta',
				'function'  => 'SUM',
				'name'      => 'stripe_fees',
				'join_type' => 'LEFT',
			],
			'_paypal_transaction_fee' => [
				'type'      => 'meta',
				'function'  => 'SUM',
				'name'      => 'paypal_fees',
				'join_type' => 'LEFT',
			],
		];

		$sales_by_payment_method = $this->get_order_report_data(
			[
				'data'                => $query_data,
				'query_type'          => 'get_results',
				'group_by'            => 'payment_method',
				'order_types'         => wc_get_order_types( 'sales-reports' ),
				'order_status'        => [ 'completed' ],
				'parent_order_status' => false,
				'where_meta'          => [
					'relation' => 'OR',
					[
						'type'       => 'order_item_meta',
						'meta_key'   => [ '_product_id', '_variation_id' ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value' => $this->product_ids, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						'operator'   => 'IN',
					],
				],
			]
		);
		$total_sales             = (object) [
			'total_orders' => 0,
			'order_total'  => 0,
			'fees'         => 0,
		];
		foreach ( $sales_by_payment_method as $sale ) {
			if ( ! empty( $sale->stripe_fees ) ) {
				$sale->fees = $sale->stripe_fees;
			} elseif ( ! empty( $sale->paypal_fees ) ) {
				$sale->fees = $sale->paypal_fees;
			} else {
				$sale->fees = 0;
			}
			$total_sales->total_orders += $sale->total_orders;
			$total_sales->order_total  += $sale->order_total;
			$total_sales->fees         += $sale->fees;
		}

		$payment_gateways = WC()->payment_gateways()->payment_gateways();

		require_once plugin_dir_path( __FILE__ ) . '/report-view.php';
	}

	/**
	 * Clears the report cache.
	 *
	 * @return void
	 */
	public function clear_report_cache() : void {
		delete_transient( strtolower( get_class( $this ) ) );
	}
}
