<?php
/**
 * Panel de administración: Códigos de invitación
 * Renderizado por amazonia_render_invite_codes_page() en inc/invite-codes.php
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;

$codes = get_option( 'amazonia_invite_codes', [] );

// Ordenar: activos primero, luego usados, luego revocados
uasort( $codes, function( $a, $b ) {
	$order = [ 'active' => 0, 'used' => 1, 'revoked' => 2 ];
	return ( $order[ $a['status'] ] ?? 3 ) <=> ( $order[ $b['status'] ] ?? 3 );
} );

$status_labels = [
	'active'  => '<span style="color:#16a34a;font-weight:600;">● Activo</span>',
	'used'    => '<span style="color:#64748b;">● Usado</span>',
	'revoked' => '<span style="color:#dc2626;">● Revocado</span>',
];
?>
<div class="wrap">
	<h1 style="display:flex;align-items:center;gap:10px;">
		<span class="dashicons dashicons-tickets-alt" style="font-size:28px;color:#4ade80;"></span>
		<?php esc_html_e( 'Códigos de Invitación — Amazonia Market', 'amazonia-theme' ); ?>
	</h1>
	<p style="color:#64748b;margin-top:4px;">
		<?php esc_html_e( 'Genera y distribuye códigos para que los administradores de comunidad puedan registrarse en la plataforma.', 'amazonia-theme' ); ?>
	</p>

	<?php if ( isset( $_GET['generated'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Código generado correctamente.', 'amazonia-theme' ); ?></p></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['revoked'] ) ) : ?>
		<div class="notice notice-warning is-dismissible"><p><?php esc_html_e( 'Código revocado.', 'amazonia-theme' ); ?></p></div>
	<?php endif; ?>

	<!-- Formulario para generar nuevo código -->
	<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;max-width:520px;margin:24px 0;">
		<h2 style="margin-top:0;font-size:16px;"><?php esc_html_e( 'Generar nuevo código', 'amazonia-theme' ); ?></h2>
		<form method="post">
			<?php wp_nonce_field( 'amazonia_invite_codes_nonce' ); ?>
			<table class="form-table" style="margin:0;">
				<tr>
					<th style="padding:8px 0;width:140px;">
						<label for="amazonia_code_label"><?php esc_html_e( 'Etiqueta (opcional)', 'amazonia-theme' ); ?></label>
					</th>
					<td style="padding:8px 0;">
						<input
							type="text"
							id="amazonia_code_label"
							name="amazonia_code_label"
							class="regular-text"
							placeholder="<?php esc_attr_e( 'Ej: Comunidad Shipibo-Conibo', 'amazonia-theme' ); ?>"
						/>
						<p class="description"><?php esc_html_e( 'Referencia interna para identificar a quién se le entregó.', 'amazonia-theme' ); ?></p>
					</td>
				</tr>
			</table>
			<p style="margin-top:16px;">
				<button type="submit" name="amazonia_generate_code" class="button button-primary" style="background:#4ade80;border-color:#22c55e;color:#fff;">
					<span class="dashicons dashicons-plus-alt2" style="margin-top:3px;"></span>
					<?php esc_html_e( 'Generar código', 'amazonia-theme' ); ?>
				</button>
			</p>
		</form>
	</div>

	<!-- Tabla de códigos -->
	<h2><?php esc_html_e( 'Códigos existentes', 'amazonia-theme' ); ?>
		<span style="font-size:14px;font-weight:normal;color:#64748b;margin-left:8px;">
			(<?php echo count( $codes ); ?> total,
			<?php echo count( array_filter( $codes, fn($c) => $c['status'] === 'active' ) ); ?> activos)
		</span>
	</h2>

	<?php if ( empty( $codes ) ) : ?>
		<p style="color:#94a3b8;font-style:italic;"><?php esc_html_e( 'No hay códigos generados aún.', 'amazonia-theme' ); ?></p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped" style="max-width:900px;">
			<thead>
				<tr>
					<th style="width:140px;"><?php esc_html_e( 'Código', 'amazonia-theme' ); ?></th>
					<th style="width:100px;"><?php esc_html_e( 'Estado', 'amazonia-theme' ); ?></th>
					<th><?php esc_html_e( 'Etiqueta', 'amazonia-theme' ); ?></th>
					<th style="width:160px;"><?php esc_html_e( 'Creado', 'amazonia-theme' ); ?></th>
					<th style="width:160px;"><?php esc_html_e( 'Usado por', 'amazonia-theme' ); ?></th>
					<th style="width:80px;"><?php esc_html_e( 'Acciones', 'amazonia-theme' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $codes as $code => $data ) : ?>
					<tr>
						<td>
							<code style="font-size:13px;letter-spacing:1px;background:#f1f5f9;padding:4px 8px;border-radius:6px;">
								<?php echo esc_html( $code ); ?>
							</code>
						</td>
						<td><?php echo $status_labels[ $data['status'] ] ?? esc_html( $data['status'] ); ?></td>
						<td style="color:#334155;"><?php echo esc_html( $data['label'] ?: '—' ); ?></td>
						<td style="color:#64748b;font-size:12px;"><?php echo esc_html( $data['created_at'] ?: '—' ); ?></td>
						<td style="color:#64748b;font-size:12px;">
							<?php
							if ( $data['used_by'] ) {
								$user = get_userdata( $data['used_by'] );
								echo esc_html( $user ? $user->display_name : "ID {$data['used_by']}" );
								echo '<br><span style="font-size:11px;">' . esc_html( $data['used_at'] ) . '</span>';
							} else {
								echo '—';
							}
							?>
						</td>
						<td>
							<?php if ( 'active' === $data['status'] ) : ?>
								<a href="<?php echo esc_url( wp_nonce_url(
									add_query_arg( [ 'page' => 'amazonia-invite-codes', 'amazonia_revoke' => $code ], admin_url( 'admin.php' ) ),
									'amazonia_revoke_' . $code
								) ); ?>"
								   style="color:#dc2626;font-size:12px;"
								   onclick="return confirm('<?php esc_attr_e( '¿Revocar este código?', 'amazonia-theme' ); ?>');">
									<?php esc_html_e( 'Revocar', 'amazonia-theme' ); ?>
								</a>
							<?php else : ?>
								<span style="color:#cbd5e1;font-size:12px;">—</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
