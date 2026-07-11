<?php
/**
 * Simple product add to cart — Amazonia override (estilizado con Tailwind)
 *
 * Sobrescribe la plantilla de WooCommerce para aplicar clases utilitarias de
 * Tailwind directamente al selector de cantidad (stepper +/−) y al botón de
 * añadir al carrito, evitando bloques <style> embebidos.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.2.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart flex flex-wrap items-stretch gap-3" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<div class="amz-qty flex items-center overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
			<button type="button" class="amz-qty-minus flex min-h-[52px] w-11 shrink-0 items-center justify-center text-primary transition-colors hover:bg-green-50" aria-label="Reducir cantidad">
				<span class="material-symbols-outlined text-[18px] leading-none">remove</span>
			</button>
			<?php
			do_action( 'woocommerce_before_add_to_cart_quantity' );

			woocommerce_quantity_input(
				array(
					'min_value'   => $product->get_min_purchase_quantity(),
					'max_value'   => $product->get_max_purchase_quantity(),
					'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
					'classes'     => array(
						'input-text', 'qty', 'text',
						'!w-14', '!m-0', '!border-x', '!border-y-0', '!border-solid', '!border-slate-200',
						'!py-3', '!px-0', '!text-center', '!bg-transparent',
						'font-[\'Outfit\']', '!text-lg', '!font-bold', '!text-slate-700',
						'!shadow-none', 'focus:!outline-none', 'focus:!ring-0',
						'[appearance:textfield]',
						'[&::-webkit-inner-spin-button]:appearance-none',
						'[&::-webkit-outer-spin-button]:appearance-none',
					),
				)
			);

			do_action( 'woocommerce_after_add_to_cart_quantity' );
			?>
			<button type="button" class="amz-qty-plus flex min-h-[52px] w-11 shrink-0 items-center justify-center text-primary transition-colors hover:bg-green-50" aria-label="Aumentar cantidad">
				<span class="material-symbols-outlined text-[18px] leading-none">add</span>
			</button>
		</div>

		<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"
			class="single_add_to_cart_button button alt inline-flex min-h-[52px] min-w-[180px] flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border-0 bg-primary px-8 font-['Outfit'] text-base font-semibold tracking-wide text-white shadow-sm transition-all duration-200 hover:bg-green-700 hover:shadow-md active:scale-[0.98]">
			<span class="material-symbols-outlined text-[20px] leading-none">shopping_bag</span>
			<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
		</button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<script>
	(function () {
		document.querySelectorAll('.amz-qty').forEach(function (box) {
			var input = box.querySelector('input.qty');
			if (!input) return;
			box.querySelector('.amz-qty-minus').addEventListener('click', function () {
				var v = parseInt(input.value) || 1;
				var min = parseInt(input.getAttribute('min')) || 1;
				if (v > min) { input.value = v - 1; input.dispatchEvent(new Event('change', { bubbles: true })); }
			});
			box.querySelector('.amz-qty-plus').addEventListener('click', function () {
				var v = parseInt(input.value) || 1;
				var max = parseInt(input.getAttribute('max')) || 9999;
				if (v < max) { input.value = v + 1; input.dispatchEvent(new Event('change', { bubbles: true })); }
			});
		});
	})();
	</script>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
