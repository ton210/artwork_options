<?php
/**
 * Admin Templates Page
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$templates_class = new SWPD_Templates( $GLOBALS['swpd_logger'], new SWPD_Cache() );
$categories = $templates_class->get_categories();
$selected_category = isset( $_GET['category'] ) ? sanitize_key( $_GET['category'] ) : '';
?>

<div class="wrap swpd-templates">
	<h1>
		<?php esc_html_e( 'Design Templates', 'swpd' ); ?>
		<a href="#" class="page-title-action" id="add-new-template"><?php esc_html_e( 'Add New', 'swpd' ); ?></a>
		<a href="#" class="page-title-action" id="import-template"><?php esc_html_e( 'Import', 'swpd' ); ?></a>
	</h1>
	
	<div class="swpd-template-filters">
		<ul class="subsubsub">
			<li><a href="?page=swpd-templates" <?php echo empty( $selected_category ) ? 'class="current"' : ''; ?>><?php esc_html_e( 'All', 'swpd' ); ?></a> |</li>
			<?php foreach ( $categories as $cat_key => $cat_label ) : ?>
				<li><a href="?page=swpd-templates&category=<?php echo esc_attr( $cat_key ); ?>" <?php echo $selected_category === $cat_key ? 'class="current"' : ''; ?>><?php echo esc_html( $cat_label ); ?></a> |</li>
			<?php endforeach; ?>
		</ul>
	</div>
	
	<div class="swpd-templates-grid">
		<?php
		$templates = $templates_class->get_templates( $selected_category );
		
		if ( ! empty( $templates ) ) :
			foreach ( $templates as $template ) :
				$is_builtin = strpos( $template['id'], 'custom-' ) !== 0;
		?>
			<div class="swpd-template-card">
				<div class="template-preview">
					<?php if ( ! empty( $template['thumbnail'] ) ) : ?>
						<img src="<?php echo esc_url( $template['thumbnail'] ); ?>" alt="<?php echo esc_attr( $template['name'] ); ?>" />
					<?php else : ?>
						<div class="no-preview"><?php esc_html_e( 'No Preview', 'swpd' ); ?></div>
					<?php endif; ?>
				</div>
				<div class="template-info">
					<h3><?php echo esc_html( $template['name'] ); ?></h3>
					<p class="template-meta">
						<?php echo esc_html( $categories[$template['category']] ?? $template['category'] ); ?>
						<?php if ( $is_builtin ) : ?>
							<span class="template-badge"><?php esc_html_e( 'Built-in', 'swpd' ); ?></span>
						<?php endif; ?>
					</p>
					<div class="template-actions">
						<button class="button preview-template" data-template='<?php echo esc_attr( wp_json_encode( $template ) ); ?>'>
							<?php esc_html_e( 'Preview', 'swpd' ); ?>
						</button>
						<?php if ( ! $is_builtin ) : ?>
							<button class="button edit-template" data-template-id="<?php echo esc_attr( $template['id'] ); ?>">
								<?php esc_html_e( 'Edit', 'swpd' ); ?>
							</button>
							<button class="button delete-template" data-template-id="<?php echo esc_attr( $template['id'] ); ?>">
								<?php esc_html_e( 'Delete', 'swpd' ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php
			endforeach;
		else :
		?>
			<p><?php esc_html_e( 'No templates found.', 'swpd' ); ?></p>
		<?php endif; ?>
	</div>
</div>

<style>
.swpd-template-filters {
	margin: 20px 0;
}

.swpd-templates-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.swpd-template-card {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 4px;
	overflow: hidden;
	transition: box-shadow 0.2s;
}

.swpd-template-card:hover {
	box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.template-preview {
	width: 100%;
	height: 200px;
	background: #f5f5f5;
	display: flex;
	align-items: center;
	justify-content: center;
}

.template-preview img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.no-preview {
	color: #999;
	font-size: 14px;
}

.template-info {
	padding: 15px;
}

.template-info h3 {
	margin: 0 0 10px;
	font-size: 16px;
}

.template-meta {
	color: #666;
	font-size: 13px;
	margin: 0 0 15px;
}

.template-badge {
	background: #0073aa;
	color: #fff;
	font-size: 11px;
	padding: 2px 8px;
	border-radius: 3px;
	margin-left: 10px;
}

.template-actions {
	display: flex;
	gap: 5px;
}

.template-actions .button {
	font-size: 13px;
	padding: 4px 12px;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Preview template
	$('.preview-template').on('click', function() {
		var template = $(this).data('template');
		// TODO: Implement template preview modal
		console.log('Preview template:', template);
		alert('Template preview coming soon!');
	});
	
	// Edit template
	$('.edit-template').on('click', function() {
		var templateId = $(this).data('template-id');
		// TODO: Implement template editor
		alert('Edit template: ' + templateId);
	});
	
	// Delete template
	$('.delete-template').on('click', function() {
		if (!confirm('<?php esc_html_e( 'Are you sure you want to delete this template?', 'swpd' ); ?>')) {
			return;
		}
		var templateId = $(this).data('template-id');
		// TODO: Implement delete via AJAX
		alert('Delete template: ' + templateId);
	});
	
	// Add new template
	$('#add-new-template').on('click', function(e) {
		e.preventDefault();
		// TODO: Implement template creation modal
		alert('Add new template feature coming soon!');
	});
	
	// Import template
	$('#import-template').on('click', function(e) {
		e.preventDefault();
		// TODO: Implement template import
		alert('Import template feature coming soon!');
	});
});
</script>