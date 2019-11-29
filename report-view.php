<?php
/**
 * Template view for report.
 *
 * @package WCProductProfitReport
 */

namespace EchoNYC\WCProductProfitReport;

?>
<table class="widefat">
	<thead>
		<tr>
			<th><strong><?php esc_html_e( 'Payment Method', 'wc-product-profit-report' ); ?></strong></th>
			<th><strong><?php esc_html_e( 'Number Of Orders', 'wc-product-profit-report' ); ?></strong></th>
			<th><strong><?php esc_html_e( 'Order Gross', 'wc-product-profit-report' ); ?></strong></th>
			<th><strong><?php esc_html_e( 'Payment Fees', 'wc-product-profit-report' ); ?></strong></th>
			<th><strong><?php esc_html_e( 'Order Profit', 'wc-product-profit-report' ); ?></strong></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $sales_by_payment_method as $sale ) : ?>
			<tr>
				<td><?php echo esc_html( $payment_gateways[ $sale->payment_method ]->title ?? __( 'Unknown', 'wc-product-profit-report' ) ); ?></td>
				<td><?php echo absint( $sale->total_orders ); ?></td>
				<td><?php echo wc_price( $sale->order_total ); // phpcs:ignore ?></td>
				<td><?php echo wc_price( $sale->fees ); // phpcs:ignore ?></td>
				<td><?php echo wc_price( $sale->order_total - $sale->fees ); // phpcs:ignore ?></td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<td><strong><?php esc_html_e( 'Total', 'wc-product-profit-report' ); ?></strong></td>
			<td><?php echo absint( $total_sales->total_orders ); ?></td>
			<td><?php echo wc_price( $total_sales->order_total ); // phpcs:ignore ?></td>
			<td><?php echo wc_price( $total_sales->fees ); // phpcs:ignore ?></td>
			<td><?php echo wc_price( $total_sales->order_total - $total_sales->fees ); // phpcs:ignore ?></td>
		</tr>
	</tbody>
</table>
