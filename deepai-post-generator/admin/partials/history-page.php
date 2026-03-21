<?php
/**
 * Generation history page template.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'deepai_pg_history';

// Pagination.
$per_page     = 20;
$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$offset       = ( $current_page - 1 ) * $per_page;

$total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
$total_pages = ceil( $total_items / $per_page );

$items = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
		$per_page,
		$offset
	)
);
?>
<div class="wrap deepai-wrap">
	<div class="deepai-header">
		<h1>
			<span class="dashicons dashicons-edit-large"></span>
			<?php esc_html_e( 'Generation History', 'deepai-post-generator' ); ?>
		</h1>
		<span class="deepai-version"><?php echo esc_html( 'v' . DEEPAI_PG_VERSION ); ?></span>
	</div>

	<div class="deepai-card">
		<div class="deepai-card-header">
			<span class="dashicons dashicons-backup"></span>
			<h2><?php esc_html_e( 'Recent Generations', 'deepai-post-generator' ); ?></h2>
			<div class="deepai-card-actions">
				<span class="deepai-badge"><?php echo esc_html( $total_items . ' ' . __( 'total', 'deepai-post-generator' ) ); ?></span>
			</div>
		</div>
		<div class="deepai-card-body">
			<?php if ( ! empty( $items ) ) : ?>
				<table class="widefat deepai-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'deepai-post-generator' ); ?></th>
							<th><?php esc_html_e( 'Provider', 'deepai-post-generator' ); ?></th>
							<th><?php esc_html_e( 'Model', 'deepai-post-generator' ); ?></th>
							<th><?php esc_html_e( 'Tokens', 'deepai-post-generator' ); ?></th>
							<th><?php esc_html_e( 'Status', 'deepai-post-generator' ); ?></th>
							<th><?php esc_html_e( 'Post', 'deepai-post-generator' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $items as $item ) : ?>
							<tr>
								<td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item->created_at ) ) ); ?></td>
								<td>
									<span class="deepai-provider-badge deepai-provider-<?php echo esc_attr( $item->provider ); ?>">
										<?php echo esc_html( ucfirst( $item->provider ) ); ?>
									</span>
								</td>
								<td><code><?php echo esc_html( $item->model_used ); ?></code></td>
								<td><?php echo esc_html( number_format( $item->tokens_used ) ); ?></td>
								<td>
									<span class="deepai-status-badge deepai-status-<?php echo esc_attr( $item->status ); ?>">
										<?php echo esc_html( ucfirst( $item->status ) ); ?>
									</span>
								</td>
								<td>
									<?php if ( $item->post_id ) : ?>
										<a href="<?php echo esc_url( get_edit_post_link( $item->post_id ) ); ?>">
											#<?php echo esc_html( $item->post_id ); ?>
										</a>
									<?php else : ?>
										<span class="deepai-muted">—</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="deepai-pagination">
						<?php
						echo wp_kses_post( paginate_links( array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => '&laquo; ' . __( 'Previous', 'deepai-post-generator' ),
							'next_text' => __( 'Next', 'deepai-post-generator' ) . ' &raquo;',
							'total'     => $total_pages,
							'current'   => $current_page,
						) ) );
						?>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="deepai-empty-state">
					<span class="dashicons dashicons-backup"></span>
					<h3><?php esc_html_e( 'No Generation History', 'deepai-post-generator' ); ?></h3>
					<p><?php esc_html_e( 'Your content generation history will appear here.', 'deepai-post-generator' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
