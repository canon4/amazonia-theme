<?php
/**
 * Template Name: Community Admin Panel
 *
 * Panel de gestión para administradores de comunidad.
 * Asignar esta plantilla a la página /community-admin/ en WordPress Admin.
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Control de acceso ───────────────────────────────────────────────────────
if ( ! is_user_logged_in() ) {
	wp_redirect( wc_get_page_permalink( 'myaccount' ) );
	exit;
}

if ( ! amazonia_is_community_admin() ) {
	wp_redirect( home_url( '/' ) );
	exit;
}

$community_id = amazonia_get_managed_community_id();
if ( ! $community_id ) {
	// Logueado pero sin comunidad asignada aún
	get_header();
	echo '<div style="max-width:600px;margin:4rem auto;padding:2rem;text-align:center;font-family:Inter,sans-serif;">';
	echo '<span class="material-symbols-outlined" style="font-size:48px;color:#4ade80;">pending</span>';
	echo '<h2 style="margin:.5rem 0;">Comunidad pendiente de asignación</h2>';
	echo '<p style="color:#64748b;">Tu cuenta está activa pero aún no tienes una comunidad asignada. El administrador de Amazonia Market te asignará pronto.</p>';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '" style="color:#4ade80;font-weight:600;">← Volver a la tienda</a>';
	echo '</div>';
	get_footer();
	exit;
}

$community  = amazonia_get_community_data( $community_id );
$vendors    = amazonia_get_community_vendors( $community_id );
$num_stores = count( $vendors );

// Contar pedidos totales de los vendors de la comunidad
$total_orders = 0;
foreach ( $vendors as $vendor ) {
	$vendor_orders = wc_get_orders( [
		'meta_key'   => '_vendor_id',
		'meta_value' => $vendor->ID,
		'return'     => 'ids',
		'limit'      => -1,
	] );
	$total_orders += count( $vendor_orders );
}

get_header();
?>

<style>
header, .site-header, footer, .site-footer,
.woocommerce-breadcrumb, h1.page-title, h1.entry-title { display: none !important; }
main#primary, .site-main, .page-content, .type-page {
	padding: 0 !important; margin: 0 !important; max-width: 100% !important; width: 100% !important;
}
.woocommerce { margin: 0 !important; padding: 0 !important; width: 100% !important; }
body { margin: 0; padding: 0; }
</style>

<div class="ca-wrap">

	<!-- Header -->
	<header class="ca-header">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ca-header-logo">
			<span class="material-symbols-outlined">eco</span>
			Amazonia Market
		</a>
		<div class="ca-header-user">
			<span><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
			<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">
				<?php esc_html_e( 'Cerrar sesión', 'amazonia-theme' ); ?>
			</a>
		</div>
	</header>

	<div class="ca-container">

		<!-- Hero: info de la comunidad -->
		<div class="ca-hero">
			<?php if ( ! empty( $community['logo'] ) ) : ?>
				<img src="<?php echo esc_url( $community['logo'] ); ?>" alt="<?php echo esc_attr( $community['nombre'] ); ?>" class="ca-hero-logo" loading="lazy" width="80" height="80" />
			<?php else : ?>
				<div class="ca-hero-logo-placeholder">
					<span class="material-symbols-outlined">groups</span>
				</div>
			<?php endif; ?>

			<div class="ca-hero-info">
				<h1><?php echo esc_html( $community['nombre'] ); ?></h1>
				<?php if ( $community['descripcion'] ) : ?>
					<p style="margin:.25rem 0 .5rem;color:#64748b;font-size:.9rem;">
						<?php echo esc_html( $community['descripcion'] ); ?>
					</p>
				<?php endif; ?>
				<div class="ca-hero-meta">
					<?php if ( $community['pais'] || $community['departamento'] ) : ?>
						<span>
							<span class="material-symbols-outlined">location_on</span>
							<?php echo esc_html( implode( ', ', array_filter( [ $community['municipio'], $community['departamento'], $community['pais'] ] ) ) ); ?>
						</span>
					<?php endif; ?>
					<?php if ( $community['categoria'] ) : ?>
						<span>
							<span class="material-symbols-outlined">label</span>
							<?php echo esc_html( $community['categoria'] ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Métricas -->
		<div class="ca-metrics">
			<div class="ca-metric-card">
				<div class="ca-metric-icon"><span class="material-symbols-outlined">storefront</span></div>
				<div class="ca-metric-value" id="ca-metric-stores"><?php echo esc_html( $num_stores ); ?></div>
				<div class="ca-metric-label"><?php esc_html_e( 'Tiendas activas', 'amazonia-theme' ); ?></div>
			</div>
			<div class="ca-metric-card">
				<div class="ca-metric-icon"><span class="material-symbols-outlined">shopping_bag</span></div>
				<div class="ca-metric-value"><?php echo esc_html( $total_orders ); ?></div>
				<div class="ca-metric-label"><?php esc_html_e( 'Pedidos totales', 'amazonia-theme' ); ?></div>
			</div>
		</div>

		<!-- Grid principal -->
		<div class="ca-grid">

			<!-- Tiendas de la comunidad -->
			<div class="ca-card">
				<div class="ca-card-header">
					<span class="material-symbols-outlined">storefront</span>
					<h2><?php esc_html_e( 'Tiendas de la comunidad', 'amazonia-theme' ); ?></h2>
				</div>
				<div class="ca-card-body">
					<ul class="ca-store-list" id="ca-store-list">
						<?php if ( empty( $vendors ) ) : ?>
							<li class="ca-empty">
								<span class="material-symbols-outlined">store</span>
								<?php esc_html_e( 'Aún no hay tiendas. Crea la primera abajo.', 'amazonia-theme' ); ?>
							</li>
						<?php else : ?>
							<?php foreach ( $vendors as $vendor ) :
								$store_name = wcfm_get_vendor_store_name( $vendor->ID );
								$logo       = function_exists( 'wcfm_get_vendor_store_logo_by_vendor' ) ? wcfm_get_vendor_store_logo_by_vendor( $vendor->ID ) : '';
								$store_url  = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $vendor->ID ) : get_author_posts_url( $vendor->ID );
							?>
								<li class="ca-store-item">
									<?php if ( $logo ) : ?>
										<img src="<?php echo esc_url( $logo ); ?>" class="ca-store-avatar" alt="<?php echo esc_attr( $store_name ); ?>" loading="lazy" width="80" height="80" />
									<?php else : ?>
										<div class="ca-store-avatar-placeholder">
											<span class="material-symbols-outlined">storefront</span>
										</div>
									<?php endif; ?>
									<div class="ca-store-info">
										<div class="ca-store-name"><?php echo esc_html( $store_name ?: $vendor->display_name ); ?></div>
										<div class="ca-store-email"><?php echo esc_html( $vendor->user_email ); ?></div>
									</div>
									<?php if ( $store_url ) : ?>
										<a href="<?php echo esc_url( $store_url ); ?>" target="_blank" class="ca-store-link">
											<?php esc_html_e( 'Ver tienda', 'amazonia-theme' ); ?> ↗
										</a>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</div>
			</div>

			<!-- Columna derecha: acciones -->
			<div style="display:flex;flex-direction:column;gap:1.5rem;">

				<!-- Crear nueva tienda -->
				<div class="ca-card">
					<div class="ca-card-header">
						<span class="material-symbols-outlined">add_business</span>
						<h2><?php esc_html_e( 'Crear nueva tienda', 'amazonia-theme' ); ?></h2>
					</div>
					<div class="ca-card-body">
						<form id="ca-create-form" class="ca-form" novalidate>
							<div class="ca-field">
								<label for="ca-store-name"><?php esc_html_e( 'Nombre de la tienda', 'amazonia-theme' ); ?> *</label>
								<input type="text" name="store_name" id="ca-store-name" required
									placeholder="<?php esc_attr_e( 'Ej: Artesanías Shipibo', 'amazonia-theme' ); ?>" />
							</div>
							<div class="ca-field">
								<label for="ca-email"><?php esc_html_e( 'Email del vendedor', 'amazonia-theme' ); ?> *</label>
								<input type="email" name="email" id="ca-email" required
									placeholder="vendedor@ejemplo.com" />
							</div>
							<div class="ca-field-row">
								<div class="ca-field">
									<label for="ca-first-name"><?php esc_html_e( 'Nombre', 'amazonia-theme' ); ?></label>
									<input type="text" name="first_name" id="ca-first-name"
										placeholder="<?php esc_attr_e( 'Juan', 'amazonia-theme' ); ?>" />
								</div>
								<div class="ca-field">
									<label for="ca-last-name"><?php esc_html_e( 'Apellido', 'amazonia-theme' ); ?></label>
									<input type="text" name="last_name" id="ca-last-name"
										placeholder="<?php esc_attr_e( 'Pérez', 'amazonia-theme' ); ?>" />
								</div>
							</div>
							<p style="font-size:.8rem;color:#94a3b8;margin:-.25rem 0 0;">
								<?php esc_html_e( 'Se generará una contraseña automática y se enviará al email del vendedor.', 'amazonia-theme' ); ?>
							</p>
							<button type="submit" class="ca-btn">
								<?php esc_html_e( 'Crear tienda', 'amazonia-theme' ); ?>
							</button>
							<div id="ca-create-feedback" class="ca-feedback"></div>
						</form>
					</div>
				</div>

				<!-- Vincular tienda existente -->
				<div class="ca-card">
					<div class="ca-card-header">
						<span class="material-symbols-outlined">link</span>
						<h2><?php esc_html_e( 'Vincular tienda existente', 'amazonia-theme' ); ?></h2>
					</div>
					<div class="ca-card-body">
						<div class="ca-field">
							<label for="ca-search-input"><?php esc_html_e( 'Buscar por nombre o email', 'amazonia-theme' ); ?></label>
							<input type="text" id="ca-search-input"
								placeholder="<?php esc_attr_e( 'Escribe al menos 2 caracteres...', 'amazonia-theme' ); ?>" />
						</div>
						<div id="ca-search-results" class="ca-search-results"></div>
						<div id="ca-link-feedback" class="ca-feedback"></div>
					</div>
				</div>

			</div>
		</div>

	</div><!-- .ca-container -->
</div><!-- .ca-wrap -->

<?php get_footer(); ?>
