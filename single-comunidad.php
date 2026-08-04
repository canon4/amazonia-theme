<?php
/**
 * Template para página individual de Comunidad (CPT: comunidad)
 * URL: /comunidad/{slug}/
 *
 * Implementa la "Pantalla A · Perfil de Comunidad" del diseño Amazonia Perfiles.
 * Toda sección sin datos se oculta por completo (no deja encabezados sueltos).
 *
 * @package Amazonia_Theme
 */

get_header();

$community_id = get_the_ID();
$data         = amazonia_get_community_data( $community_id );
$vendors      = amazonia_get_community_vendors( $community_id );
$embed_url    = $data ? amazonia_get_video_embed_url( $data['video_url'] ) : '';

// Enlace de regreso: la página "Comunidades" si existe, si no la portada.
$comunidades_page = get_page_by_path( 'comunidades' );
$back_url         = $comunidades_page ? get_permalink( $comunidades_page ) : home_url( '/' );

// Estadísticas por tienda (nº de productos y rating agregado de reseñas de producto).
$store_stats   = [];
$total_products = 0;
foreach ( $vendors as $vendor ) {
	$stats                       = amazonia_get_store_stats( $vendor->ID );
	$store_stats[ $vendor->ID ]  = $stats;
	$total_products             += $stats['product_count'];
}

/**
 * Parte la historia en dos bloques de texto para darle ritmo editorial.
 * Si viene como un solo párrafo largo, se corta en el punto más cercano a la mitad.
 */
$story_blocks = [];
if ( ! empty( $data['historia'] ) ) {
	$paragraphs = preg_split( '/\R{2,}|\R/', trim( $data['historia'] ), -1, PREG_SPLIT_NO_EMPTY );
	$paragraphs = array_values( array_filter( array_map( 'trim', $paragraphs ) ) );

	if ( count( $paragraphs ) === 1 && mb_strlen( $paragraphs[0] ) > 400 ) {
		$text  = $paragraphs[0];
		$mid   = (int) ( mb_strlen( $text ) / 2 );
		$split = mb_strpos( $text, '. ', $mid );
		if ( false !== $split ) {
			$paragraphs = [ mb_substr( $text, 0, $split + 1 ), mb_substr( $text, $split + 2 ) ];
		}
	}

	$half         = (int) ceil( count( $paragraphs ) / 2 );
	$story_blocks = [ array_slice( $paragraphs, 0, $half ), array_slice( $paragraphs, $half ) ];
}

// Tiles de confianza: solo se muestran los que tienen valor real.
$tiles = [];
if ( ! empty( $data['fundacion'] ) ) {
	$tiles[] = [ 'n' => $data['fundacion'], 'l' => __( 'Fundación', 'amazonia-theme' ) ];
	$years   = (int) current_time( 'Y' ) - (int) $data['fundacion'];
	if ( $years > 0 ) {
		$tiles[] = [ 'n' => $years, 'l' => __( 'Años de tradición', 'amazonia-theme' ) ];
	}
}
if ( ! empty( $data['num_familias'] ) ) {
	$tiles[] = [ 'n' => $data['num_familias'], 'l' => __( 'Familias', 'amazonia-theme' ) ];
}
if ( $vendors ) {
	$tiles[] = [ 'n' => count( $vendors ), 'l' => _n( 'Tienda', 'Tiendas', count( $vendors ), 'amazonia-theme' ) ];
}
if ( $total_products > 0 ) {
	$tiles[] = [ 'n' => $total_products, 'l' => _n( 'Producto', 'Productos', $total_products, 'amazonia-theme' ) ];
}

// Galería: resuelve las URLs una sola vez.
$gallery = [];
foreach ( (array) $data['galeria_ids'] as $att_id ) {
	$large = wp_get_attachment_image_url( $att_id, 'large' );
	if ( ! $large ) {
		continue;
	}
	$gallery[] = [
		'large'  => $large,
		'medium' => wp_get_attachment_image_url( $att_id, 'medium' ) ?: $large,
		'alt'    => get_post_meta( $att_id, '_wp_attachment_image_alt', true ) ?: $data['nombre'],
	];
}

$location = implode( ' · ', array_filter( [ $data['municipio'], $data['departamento'], $data['pais'] ] ) );
?>

<div class="amz-profile">

	<!-- ── Hero ──────────────────────────────────────────────────── -->
	<header class="amz-hero">
		<div class="amz-hero__bg"></div>
		<?php
		$banner = amazonia_resolve_community_image( $data['banner'], 'amazonia-hero' );
		if ( $banner['url'] ) :
			?>
			<div class="amz-hero__img" style="background-image:url('<?php echo esc_url( $banner['url'] ); ?>')" role="presentation"></div>
		<?php endif; ?>
		<div class="amz-hero__glow-a"></div>
		<div class="amz-hero__glow-b"></div>

		<div class="amz-shell amz-hero__inner">
			<a href="<?php echo esc_url( $back_url ); ?>" class="amz-back">
				<span class="material-symbols-outlined" style="font-size:18px" aria-hidden="true">arrow_back</span>
				<?php esc_html_e( 'Volver a comunidades', 'amazonia-theme' ); ?>
			</a>

			<div class="amz-hero__row">
				<?php if ( ! empty( $data['logo'] ) ) : ?>
					<?php
					echo amazonia_community_image_html(
						$data['logo'],
						'thumbnail',
						sprintf( __( 'Logo de %s', 'amazonia-theme' ), $data['nombre'] ),
						[ 'class' => 'amz-logo', 'width' => 140, 'height' => 140, 'loading' => 'eager' ]
					);
					?>
				<?php else : ?>
					<div class="amz-logo" aria-hidden="true"><?php echo esc_html( mb_substr( $data['nombre'], 0, 1 ) ); ?></div>
				<?php endif; ?>

				<div class="amz-hero__body">
					<?php if ( ! empty( $data['categoria'] ) || ! empty( $data['certificaciones'] ) ) : ?>
						<div class="amz-chips">
							<?php if ( ! empty( $data['categoria'] ) ) : ?>
								<span class="amz-chip amz-chip--cat"><?php echo esc_html( $data['categoria'] ); ?></span>
							<?php endif; ?>
							<?php
							foreach ( explode( '·', (string) $data['certificaciones'] ) as $cert ) :
								$cert = trim( $cert );
								if ( '' === $cert ) {
									continue;
								}
								?>
								<span class="amz-chip amz-chip--cert">
									<span class="material-symbols-outlined" aria-hidden="true">verified</span>
									<?php echo esc_html( $cert ); ?>
								</span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<h1 class="amz-title"><?php echo esc_html( $data['nombre'] ); ?></h1>

					<?php if ( ! empty( $data['descripcion'] ) ) : ?>
						<p class="amz-lede"><?php echo esc_html( $data['descripcion'] ); ?></p>
					<?php endif; ?>

					<?php if ( $location ) : ?>
						<div class="amz-meta">
							<span class="material-symbols-outlined" aria-hidden="true">location_on</span>
							<?php echo esc_html( $location ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</header>

	<!-- ── Barra de métricas de confianza ────────────────────────── -->
	<?php if ( $tiles ) : ?>
		<div class="amz-shell amz-stats-wrap">
			<div class="amz-stats">
				<?php foreach ( $tiles as $tile ) : ?>
					<div class="amz-stat">
						<div class="amz-stat__n"><?php echo esc_html( $tile['n'] ); ?></div>
						<div class="amz-stat__l"><?php echo esc_html( $tile['l'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- ── Historia ──────────────────────────────────────────────── -->
	<?php if ( ! empty( $story_blocks[0] ) ) : ?>
		<section class="amz-shell amz-section">
			<div class="amz-h2row">
				<h2 class="amz-h2"><?php esc_html_e( 'Nuestra historia', 'amazonia-theme' ); ?></h2>
			</div>

			<div class="amz-story">
				<div>
					<?php foreach ( $story_blocks[0] as $p ) : ?>
						<p><?php echo esc_html( $p ); ?></p>
					<?php endforeach; ?>
				</div>
				<?php if ( ! empty( $data['storytelling_img_1'] ) ) : ?>
					<div class="amz-story__img">
						<?php
						echo amazonia_community_image_html(
							$data['storytelling_img_1'],
							'large',
							sprintf( __( 'Artesanía de %s', 'amazonia-theme' ), $data['nombre'] )
						);
						?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $story_blocks[1] ) ) : ?>
				<div class="amz-story amz-story--flip">
					<?php if ( ! empty( $data['storytelling_img_2'] ) ) : ?>
						<div class="amz-story__img">
							<?php
							echo amazonia_community_image_html(
								$data['storytelling_img_2'],
								'large',
								sprintf( __( 'Proceso artesanal de %s', 'amazonia-theme' ), $data['nombre'] )
							);
							?>
						</div>
					<?php endif; ?>
					<div>
						<?php foreach ( $story_blocks[1] as $p ) : ?>
							<p><?php echo esc_html( $p ); ?></p>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $data['storytelling_img_3'] ) ) : ?>
				<div class="amz-story__wide">
					<?php
					echo amazonia_community_image_html(
						$data['storytelling_img_3'],
						'large',
						sprintf( __( 'Territorio de %s', 'amazonia-theme' ), $data['nombre'] )
					);
					?>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<!-- ── Valores ───────────────────────────────────────────────── -->
	<?php if ( ! empty( $data['valores'] ) ) : ?>
		<section class="amz-band">
			<div class="amz-shell amz-section">
				<div class="amz-h2row">
					<h2 class="amz-h2"><?php esc_html_e( 'Lo que defendemos', 'amazonia-theme' ); ?></h2>
				</div>
				<div class="amz-values">
					<?php foreach ( $data['valores'] as $valor ) : ?>
						<?php if ( empty( $valor['texto'] ) ) { continue; } ?>
						<div class="amz-value">
							<div class="amz-value__ico">
								<span class="material-symbols-outlined" aria-hidden="true"><?php echo esc_html( $valor['icono'] ?? 'eco' ); ?></span>
							</div>
							<h3><?php echo esc_html( $valor['texto'] ); ?></h3>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ── Video (solo si la comunidad tiene uno) ────────────────── -->
	<?php if ( $embed_url ) : ?>
		<section class="amz-shell amz-section" style="max-width:960px">
			<div class="amz-h2row">
				<h2 class="amz-h2"><?php esc_html_e( 'Conoce el proceso', 'amazonia-theme' ); ?></h2>
			</div>
			<div class="amz-video">
				<iframe src="<?php echo esc_url( $embed_url ); ?>"
				        title="<?php echo esc_attr( sprintf( __( 'Video de %s', 'amazonia-theme' ), $data['nombre'] ) ); ?>"
				        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
				        allowfullscreen loading="lazy"></iframe>
			</div>
		</section>
	<?php endif; ?>

	<!-- ── Galería ───────────────────────────────────────────────── -->
	<?php if ( $gallery ) : ?>
		<section class="amz-shell amz-section">
			<div class="amz-h2row">
				<h2 class="amz-h2"><?php esc_html_e( 'Galería', 'amazonia-theme' ); ?></h2>
			</div>
			<div class="amz-gallery">
				<?php foreach ( $gallery as $img ) : ?>
					<button type="button" class="amz-gallery__item" data-full="<?php echo esc_attr( $img['large'] ); ?>"
					        aria-label="<?php echo esc_attr( sprintf( __( 'Ampliar imagen: %s', 'amazonia-theme' ), $img['alt'] ) ); ?>">
						<img src="<?php echo esc_url( $img['medium'] ); ?>" alt="<?php echo esc_attr( $img['alt'] ); ?>" loading="lazy" />
					</button>
				<?php endforeach; ?>
			</div>
		</section>

		<dialog id="amz-lightbox" aria-label="<?php esc_attr_e( 'Imagen ampliada', 'amazonia-theme' ); ?>">
			<div style="position:relative;display:inline-block">
				<img id="amz-lightbox-img" src="" alt="" />
				<button type="button" class="amz-lightbox__close" data-close
				        aria-label="<?php esc_attr_e( 'Cerrar', 'amazonia-theme' ); ?>">
					<span class="material-symbols-outlined" style="font-size:20px" aria-hidden="true">close</span>
				</button>
			</div>
		</dialog>
	<?php endif; ?>

	<!-- ── Tiendas de la comunidad ───────────────────────────────── -->
	<section id="amz-tiendas" class="amz-shell amz-section">
		<div class="amz-h2row">
			<h2 class="amz-h2"><?php esc_html_e( 'Tiendas de la comunidad', 'amazonia-theme' ); ?></h2>
		</div>
		<p class="amz-sub"><?php esc_html_e( 'Cada tienda es un taller familiar dentro de la comunidad.', 'amazonia-theme' ); ?></p>

		<?php if ( empty( $vendors ) ) : ?>
			<div class="amz-empty">
				<span class="material-symbols-outlined" aria-hidden="true">storefront</span>
				<p><?php esc_html_e( 'Esta comunidad aún no tiene tiendas registradas.', 'amazonia-theme' ); ?></p>
			</div>
		<?php else : ?>
			<div class="amz-stores">
				<?php
				foreach ( $vendors as $vendor ) :
					$stats      = $store_stats[ $vendor->ID ];
					$store_name = function_exists( 'wcfm_get_vendor_store_name' ) ? wcfm_get_vendor_store_name( $vendor->ID ) : '';
					$store_name = $store_name ?: $vendor->display_name;
					$logo       = function_exists( 'wcfm_get_vendor_store_logo_by_vendor' ) ? wcfm_get_vendor_store_logo_by_vendor( $vendor->ID ) : '';
					$store_url  = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $vendor->ID ) : get_author_posts_url( $vendor->ID );
					$profile    = get_user_meta( $vendor->ID, 'wcfmmp_profile_settings', true );
					$banner     = isset( $profile['banner'] ) ? $profile['banner'] : '';
					?>
					<a class="amz-store" href="<?php echo esc_url( $store_url ); ?>">
						<div class="amz-store__banner"<?php echo $banner ? ' style="background-image:url(\'' . esc_url( $banner ) . '\')"' : ''; ?>></div>
						<div class="amz-store__body">
							<?php if ( $logo ) : ?>
								<img class="amz-store__logo" src="<?php echo esc_url( $logo ); ?>"
								     alt="<?php echo esc_attr( sprintf( __( 'Logo de %s', 'amazonia-theme' ), $store_name ) ); ?>"
								     width="52" height="52" loading="lazy" />
							<?php else : ?>
								<div class="amz-store__logo" aria-hidden="true"><?php echo esc_html( mb_substr( $store_name, 0, 1 ) ); ?></div>
							<?php endif; ?>

							<h3><?php echo esc_html( $store_name ); ?></h3>

							<div class="amz-store__facts">
								<?php if ( null !== $stats['rating'] ) : ?>
									<span class="amz-store__rating">
										<span class="material-symbols-outlined" aria-hidden="true">star</span>
										<?php echo esc_html( number_format_i18n( $stats['rating'], 1 ) ); ?>
									</span>
									<span aria-hidden="true">·</span>
								<?php endif; ?>
								<span>
									<?php
									printf(
										esc_html( _n( '%s producto', '%s productos', $stats['product_count'], 'amazonia-theme' ) ),
										esc_html( number_format_i18n( $stats['product_count'] ) )
									);
									?>
								</span>
							</div>

							<span class="amz-store__cta"><?php esc_html_e( 'Visitar tienda', 'amazonia-theme' ); ?></span>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<!-- ── Cierre ────────────────────────────────────────────────── -->
	<footer class="amz-close">
		<div class="amz-shell amz-close__inner">
			<h2><?php esc_html_e( 'Conoce sus productos', 'amazonia-theme' ); ?></h2>
			<p><?php esc_html_e( 'Cada compra sostiene a una familia y protege un saber que no se enseña en ninguna universidad.', 'amazonia-theme' ); ?></p>
			<div class="amz-actions">
				<?php if ( $vendors ) : ?>
					<a class="amz-btn amz-btn--primary" href="#amz-tiendas">
						<?php esc_html_e( 'Ver catálogo de tiendas', 'amazonia-theme' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( ! empty( $data['instagram'] ) ) : ?>
					<a class="amz-btn amz-btn--ghost" href="<?php echo esc_url( $data['instagram'] ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="material-symbols-outlined" style="font-size:18px" aria-hidden="true">photo_camera</span>
						Instagram
					</a>
				<?php endif; ?>
				<?php if ( ! empty( $data['facebook'] ) ) : ?>
					<a class="amz-btn amz-btn--ghost" href="<?php echo esc_url( $data['facebook'] ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="material-symbols-outlined" style="font-size:18px" aria-hidden="true">thumb_up</span>
						Facebook
					</a>
				<?php endif; ?>
			</div>
		</div>
	</footer>

</div>

<?php if ( $gallery ) : ?>
<script>
(function () {
	var lb  = document.getElementById('amz-lightbox');
	var img = document.getElementById('amz-lightbox-img');
	if (!lb || !img) return;

	document.querySelectorAll('.amz-gallery__item').forEach(function (btn) {
		btn.addEventListener('click', function () {
			img.src = this.dataset.full;
			img.alt = this.querySelector('img') ? this.querySelector('img').alt : '';
			lb.showModal();
		});
	});

	lb.addEventListener('click', function (e) {
		if (e.target === lb || e.target.closest('[data-close]')) lb.close();
	});
	lb.addEventListener('close', function () { img.src = ''; });
})();
</script>
<?php endif; ?>

<?php get_footer(); ?>
