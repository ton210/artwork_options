<?php
/**
 * Admin Designs Page Template
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$designs_table = $wpdb->prefix . 'swpd_designs';

// Pagination
$per_page = 20;
$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$offset = ( $current_page - 1 ) * $per_page;

// Filters
$where_clauses = array( '1=1' );
$filter_user = isset( $_GET['filter_user'] ) ? intval( $_GET['filter_user'] ) : 0;
$filter_product = isset( $_GET['filter_product'] ) ? intval( $_GET['filter_product'] ) : 0;
$filter_status = isset( $_GET['filter_status'] ) ? sanitize_text_field( $_GET['filter_status'] ) : '';

if ( $filter_user > 0 ) {
	$where_clauses[] = $wpdb->prepare( 'user_id = %d', $filter_user );
}
if ( $filter_product > 0 ) {
	$where_clauses[] = $wpdb->prepare( 'product_id = %d', $filter_product );
}
if ( $filter_status ) {
	$where_clauses[] = $wpdb->prepare( 'status = %s', $filter_status );
}

$where_sql = implode( ' AND ', $where_clauses );

// Get total count
$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$designs_table} WHERE {$where_sql}" );

// Get designs
$designs = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM {$designs_table} 
	WHERE {$where_sql}
	ORDER BY created_at DESC 
	LIMIT %d OFFSET %d",
	$per_page,
	$offset
));
?>

<div class="wrap swpd-designs">
	<h1>
		<?php esc_html_e( 'Customer Designs', 'swpd' ); ?>
		<a href="#" class="page-title-action" id="export-designs"><?php esc_html_e( 'Export', 'swpd' ); ?></a>
	</h1>
	
	<div class="tablenav top">
		<form method="get" action="">
			<input type="hidden" name="page" value="swpd-designs" />
			
			<div class="alignleft actions">
				<select name="filter_status">
					<option value=""><?php esc_html_e( 'All Statuses', 'swpd' ); ?></option>
					<option value="draft" <?php selected( $filter_status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'swpd' ); ?></option>
					<option value="published" <?php selected( $filter_status, 'published' ); ?>><?php esc_html_e( 'Published', 'swpd' ); ?></option>
				</select>
				
				<select name="filter_product">
					<option value="0"><?php esc_html_e( 'All Products', 'swpd' ); ?></option>
					<?php
					$products = $wpdb->get_results( "SELECT DISTINCT product_id FROM {$designs_table}" );
					foreach ( $products as $prod ) :
						$product = wc_get_product( $prod->product_id );
						if ( $product ) :
					?>
						<option value="<?php echo esc_attr( $prod->product_id ); ?>" <?php selected( $filter_product, $prod->product_id ); ?>>
							<?php echo esc_html( $product->get_name() ); ?>
						</option>
					<?php 
						endif;
					endforeach; 
					?>
				</select>
				
				<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'swpd' ); ?>" />
			</div>
		</form>
		
		<div class="tablenav-pages">
			<?php
			$total_pages = ceil( $total_items / $per_page );
			$pagination_args = array(
				'base' => add_query_arg( 'paged', '%#%' ),
				'format' => '',
				'current' => $current_page,
				'total' => $total_pages
			);
			echo paginate_links( $pagination_args );
			?>
		</div>
	</div>
	
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th style="width: 50px;"><?php esc_html_e( 'ID', 'swpd' ); ?></th>
				<th style="width: 100px;"><?php esc_html_e( 'Preview', 'swpd' ); ?></th>
				<th><?php esc_html_e( 'Design Name', 'swpd' ); ?></th>
				<th><?php esc_html_e( 'Product', 'swpd' ); ?></th>
				<th><?php esc_html_e( 'User', 'swpd' ); ?></th>
				<th><?php esc_html_e( 'Status', 'swpd' ); ?></th>
				<th><?php esc_html_e( 'Created', 'swpd' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'swpd' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $designs ) ) : ?>
				<?php foreach ( $designs as $design ) : ?>
					<tr>
						<td><?php echo esc_html( $design->id ); ?></td>
						<td>
							<?php if ( $design->preview_url ) : ?>
								<img src="<?php echo esc_url( $design->preview_url ); ?>" alt="" style="width: 80px; height: 80px; object-fit: contain; border: 1px solid #ddd;" />
							<?php else : ?>
								<span style="color: #999;"><?php esc_html_e( 'No preview', 'swpd' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<strong><?php echo esc_html( $design->design_name ); ?></strong>
						</td>
						<td>
							<?php
							$product = wc_get_product( $design->product_id );
							if ( $product ) {
								echo '<a href="' . esc_url( get_edit_post_link( $design->product_id ) ) . '">';
								echo esc_html( $product->get_name() );
								echo '</a>';
							} else {
								echo esc_html( 'Product #' . $design->product_id );
							}
							?>
						</td>
						<td>
							<?php
							if ( $design->user_id ) {
								$user = get_user_by( 'id', $design->user_id );
								if ( $user ) {
									echo '<a href="' . esc_url( get_edit_user_link( $design->user_id ) ) . '">';
									echo esc_html( $user->display_name );
									echo '</a>';
								} else {
									echo esc_html( 'User #' . $design->user_id );
								}
							} else {
								echo '<em>' . esc_html__( 'Guest', 'swpd' ) . '</em>';
							}
							?>
						</td>
						<td>
							<span class="status-badge status-<?php echo esc_attr( $design->status ); ?>">
								<?php echo esc_html( ucfirst( $design->status ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $design->created_at ) ) ); ?></td>
						<td>
							<button class="button button-small view-design" data-design-id="<?php echo esc_attr( $design->id ); ?>">
								<?php esc_html_e( 'View', 'swpd' ); ?>
							</button>
							<button class="button button-small delete-design" data-design-id="<?php echo esc_attr( $design->id ); ?>">
								<?php esc_html_e( 'Delete', 'swpd' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="8"><?php esc_html_e( 'No designs found.', 'swpd' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<style>
.status-badge {
	display: inline-block;
	padding: 3px 8px;
	border-radius: 3px;
	font-size: 12px;
	font-weight: 600;
}
.status-draft {
	background: #f0f0f0;
	color: #666;
}
.status-published {
	background: #7ad03a;
	color: #fff;
}
</style>

<script>
jQuery(document).ready(function($) {
	$('.view-design').on('click', function() {
		var designId = $(this).data('design-id');
		// TODO: Implement design viewer modal
		alert('View design #' + designId);
	});
	
	$('.delete-design').on('click', function() {
		if (!confirm('<?php esc_html_e( 'Are you sure you want to delete this design?', 'swpd' ); ?>')) {
			return;
		}
		var designId = $(this).data('design-id');
		// TODO: Implement delete via AJAX
		alert('Delete design #' + designId);
	});
	
	$('#export-designs').on('click', function(e) {
		e.preventDefault();
		// TODO: Implement export functionality
		alert('Export designs feature coming soon!');
	});
});
</script>