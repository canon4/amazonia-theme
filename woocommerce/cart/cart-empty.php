<?php
/**
 * Empty cart page — Override del tema Amazonia
 *
 * Sobreescribe: woocommerce/templates/cart/cart-empty.php
 * Versión base: 7.0.1
 *
 * Nota: no llamamos do_action('woocommerce_cart_is_empty') para evitar
 * el mensaje duplicado que inyecta WooCommerce por defecto (wc_empty_cart_message).
 */

defined( 'ABSPATH' ) || exit;
?>

<style>
.amazonia-empty-cart {
	display:        flex;
	flex-direction: column;
	align-items:    center;
	text-align:     center;
	padding:        64px 24px 80px;
	max-width:      560px;
	margin:         0 auto;
}
.amazonia-empty-cart__icon {
	width:           96px;
	height:          96px;
	border-radius:   50%;
	background:      #f0f7f3;
	display:         flex;
	align-items:     center;
	justify-content: center;
	margin-bottom:   28px;
}
.amazonia-empty-cart__icon .material-symbols-outlined {
	font-size: 48px;
	color:     #2F5C3E;
	opacity:   0.75;
}
.amazonia-empty-cart__title {
	font-size:   1.75rem;
	font-weight: 800;
	color:       #0f172a;
	margin:      0 0 12px;
	line-height: 1.2;
}
.amazonia-empty-cart__message {
	font-size:   1rem;
	color:       #64748b;
	line-height: 1.6;
	margin:      0 0 36px;
}
.amazonia-empty-cart__actions {
	display:         flex;
	gap:             12px;
	flex-wrap:       wrap;
	justify-content: center;
	margin-bottom:   48px;
}
.amazonia-empty-cart__btn-primary,
.amazonia-empty-cart__btn-secondary {
	display:         inline-flex;
	align-items:     center;
	gap:             8px;
	padding:         12px 24px;
	border-radius:   999px;
	font-size:       0.9rem;
	font-weight:     700;
	text-decoration: none !important;
	transition:      all 0.2s ease;
	line-height:     1;
}
.amazonia-empty-cart__btn-primary {
	background: #2F5C3E;
	color:      #fff !important;
	box-shadow: 0 4px 14px rgba(47,92,62,0.3);
	border:     none;
}
.amazonia-empty-cart__btn-primary:hover {
	background: #1f3d29;
	transform:  translateY(-1px);
	box-shadow: 0 6px 20px rgba(47,92,62,0.4);
}
.amazonia-empty-cart__btn-secondary {
	background: transparent;
	color:      #2F5C3E !important;
	border:     2px solid #2F5C3E;
}
.amazonia-empty-cart__btn-secondary:hover {
	background: #f0f7f3;
	transform:  translateY(-1px);
}
.amazonia-empty-cart__btn-primary .material-symbols-outlined,
.amazonia-empty-cart__btn-secondary .material-symbols-outlined {
	font-size: 18px;
}
.amazonia-empty-cart__categories-label {
	font-size:      0.8rem;
	font-weight:    600;
	color:          #94a3b8;
	text-transform: uppercase;
	letter-spacing: 0.08em;
	margin:         0 0 14px;
}
.amazonia-empty-cart__categories-list {
	display:         flex;
	gap:             8px;
	flex-wrap:       wrap;
	justify-content: center;
}
.amazonia-empty-cart__category-chip {
	padding:         6px 16px;
	border-radius:   999px;
	background:      #f1f5f9;
	color:           #334155 !important;
	font-size:       0.85rem;
	font-weight:     500;
	text-decoration: none !important;
	border:          1px solid #e2e8f0;
	transition:      all 0.2s ease;
}
.amazonia-empty-cart__category-chip:hover {
	background:   #2F5C3E;
	color:        #fff !important;
	border-color: #2F5C3E;
}
</style>

<div class="amazonia-empty-cart">

	<div class="amazonia-empty-cart__icon">
		<span class="material-symbols-outlined">shopping_cart</span>
	</div>

	<h2 class="amazonia-empty-cart__title">
		<?php esc_html_e( 'Tu carrito está vacío', 'amazonia-theme' ); ?>
	</h2>

	<p class="amazonia-empty-cart__message">
		<?php esc_html_e( 'Parece que aún no has añadido ningún producto. Explora nuestra tienda y descubre bioproductos sostenibles de la Amazonia.', 'amazonia-theme' ); ?>
	</p>

	<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
		<div class="amazonia-empty-cart__actions">
			<a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"
			   class="amazonia-empty-cart__btn-primary">
				<span class="material-symbols-outlined">storefront</span>
				<?php esc_html_e( 'Explorar la Tienda', 'amazonia-theme' ); ?>
			</a>

			<?php if ( ! is_user_logged_in() ) : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
				   class="amazonia-empty-cart__btn-secondary">
					<span class="material-symbols-outlined">person</span>
					<?php esc_html_e( 'Iniciar Sesión', 'amazonia-theme' ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => 4,
		'exclude'    => get_option( 'default_product_cat' ),
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) : ?>
		<div class="amazonia-empty-cart__categories">
			<p class="amazonia-empty-cart__categories-label">
				<?php esc_html_e( 'Categorías populares', 'amazonia-theme' ); ?>
			</p>
			<div class="amazonia-empty-cart__categories-list">
				<?php foreach ( $terms as $term ) : ?>
					<a href="<?php echo esc_url( get_term_link( $term ) ); ?>"
					   class="amazonia-empty-cart__category-chip">
						<?php echo esc_html( $term->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

</div>
