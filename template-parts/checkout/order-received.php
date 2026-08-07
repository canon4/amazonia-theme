<?php
/**
 * Vista "Pedido recibido" (pantalla de gracias tras el pago).
 *
 * Se renderiza desde woocommerce/checkout/thankyou.php mediante:
 *   get_template_part( 'template-parts/checkout/order-received', null, array( 'order' => $order ) );
 *
 * Sustituye por completo a la tabla por defecto de WooCommerce
 * (woocommerce_order_details_table), por lo que aquí se disparan a mano los
 * hooks de order-details / customer-details para que los plugins (WCFM,
 * Mercado Pago, Envia) sigan pudiendo inyectar su contenido.
 *
 * Estilos: Tailwind compilado localmente (assets/css/tailwind.css).
 * Tras editar clases nuevas hay que regenerar el CSS: `npm run build:css`.
 *
 * @package Amazonia_Theme
 *
 * @var array $args Argumentos recibidos desde get_template_part().
 */

defined( 'ABSPATH' ) || exit;

$order = isset( $args['order'] ) ? $args['order'] : false;

/**
 * Clases del distintivo de estado del pedido.
 * Se declara con guarda por si la vista se incluye más de una vez.
 */
if ( ! function_exists( 'amazonia_order_status_badge_classes' ) ) {
	function amazonia_order_status_badge_classes( $status ) {
		switch ( $status ) {
			case 'completed':
				return 'bg-primary/10 text-primary ring-primary/20';
			case 'processing':
			case 'on-hold':
			case 'pending':
				return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 ring-amber-500/20';
			case 'cancelled':
			case 'failed':
			case 'refunded':
				return 'bg-red-500/10 text-red-600 dark:text-red-400 ring-red-500/20';
			default:
				return 'bg-slate-500/10 text-slate-600 dark:text-slate-300 ring-slate-500/20';
		}
	}
}

/**
 * Imprime el HTML devuelto por un hook de terceros dentro de una tarjeta del
 * tema. Si el hook no produjo nada, no se dibuja la tarjeta vacía.
 *
 * @param string $html          HTML ya generado por los plugins enganchados.
 * @param string $extra_classes Clases adicionales para la tarjeta.
 */
if ( ! function_exists( 'amazonia_order_received_hook_card' ) ) {
	function amazonia_order_received_hook_card( $html, $extra_classes = '' ) {
		$html = trim( $html );

		if ( '' === $html ) {
			return;
		}

		$classes = 'amazonia-order-extras mt-6 rounded-[2rem] bg-white dark:bg-white/[0.04] ring-1 ring-black/5 dark:ring-white/10 px-6 sm:px-8 py-6 '
			. 'text-sm leading-relaxed text-slate-600 dark:text-slate-300 '
			. '[&>*:first-child]:mt-0 [&>*:last-child]:mb-0 '
			. '[&_h2]:text-lg [&_h2]:font-black [&_h2]:tracking-tight [&_h2]:text-slate-900 dark:[&_h2]:text-white '
			. '[&_h3]:font-bold [&_h3]:text-slate-900 dark:[&_h3]:text-white '
			. '[&_a]:text-primary [&_table]:w-full [&_table]:text-left [&_th]:font-semibold '
			. '[&_td]:py-2 [&_td]:border-t [&_td]:border-slate-100 dark:[&_td]:border-white/5 '
			. '[&_.button]:inline-flex [&_.button]:items-center [&_.button]:gap-2 [&_.button]:rounded-full '
			. '[&_.button]:bg-primary [&_.button]:px-6 [&_.button]:py-3 [&_.button]:text-sm [&_.button]:font-bold '
			. '[&_.button]:text-white [&_.button]:no-underline ' . $extra_classes;

		echo '<div class="' . esc_attr( trim( $classes ) ) . '">'
			. $html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML de hooks de terceros.
			. '</div>';
	}
}

/**
 * Mensaje de confirmación (filtrable por WooCommerce y por plugins).
 */
$received_text = apply_filters(
	'woocommerce_thankyou_order_received_text',
	esc_html__( 'Gracias. Hemos recibido tu pedido.', 'amazonia-theme' ),
	$order
);
?>

<div class="woocommerce-order w-full">
	<div class="mx-auto w-full max-w-5xl px-4 sm:px-6 py-12 lg:py-20">

	<?php if ( ! $order ) : ?>

		<?php /* Sin pedido: sólo el mensaje (p. ej. clave/ID inválidos). */ ?>
		<div class="rounded-[2rem] bg-white dark:bg-white/[0.04] ring-1 ring-black/5 dark:ring-white/10 px-8 py-16 text-center">
			<h1 class="text-2xl font-black tracking-tight text-forest-green dark:text-white">
				<?php esc_html_e( 'Pedido no disponible', 'amazonia-theme' ); ?>
			</h1>
			<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received mt-3 text-slate-500 dark:text-slate-400">
				<?php echo wp_kses_post( $received_text ); ?>
			</p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
			   class="mt-8 inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-bold text-white transition hover:bg-forest-green">
				<?php esc_html_e( 'Ir a la tienda', 'amazonia-theme' ); ?>
				<span class="material-symbols-outlined -mr-1 text-[18px]" aria-hidden="true">arrow_forward</span>
			</a>
		</div>

	<?php else : ?>

		<?php
		$is_failed          = $order->has_status( 'failed' );
		$order_items        = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
		$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
		$downloads          = $order->get_downloadable_items();
		$item_totals        = $order->get_order_item_totals();
		$status_badge       = amazonia_order_status_badge_classes( $order->get_status() );

		// Los datos del cliente sólo se muestran a quien hizo el pedido
		// (también funciona para invitados: ambos user_id valen 0).
		$show_customer_details = $order->get_user_id() === get_current_user_id();
		$show_shipping         = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
		?>

		<!-- ── Encabezado de confirmación ──────────────────────────────── -->
		<header class="text-center">
			<div class="relative mx-auto mb-8 flex h-24 w-24 items-center justify-center">
				<span class="absolute inset-0 rounded-full <?php echo $is_failed ? 'bg-red-500/10' : 'bg-primary/10'; ?>"></span>
				<span class="absolute inset-3 rounded-full <?php echo $is_failed ? 'bg-red-500/15' : 'bg-primary/20'; ?>"></span>
				<span class="relative flex h-14 w-14 items-center justify-center rounded-full text-white shadow-lg <?php echo $is_failed ? 'bg-red-500 shadow-red-500/30' : 'bg-primary shadow-primary/30'; ?>">
					<?php if ( $is_failed ) : ?>
						<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
							<path d="M18 6 6 18M6 6l12 12" />
						</svg>
					<?php else : ?>
						<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="m4.5 12.5 5 5 10-11" />
						</svg>
					<?php endif; ?>
				</span>
			</div>

			<?php /* El pr- descuenta el espacio que tracking-[0.2em] deja tras la última letra: sin eso el texto queda 2px a la izquierda dentro de la píldora. */ ?>
			<span class="inline-flex items-center rounded-full pl-3 pr-[calc(0.75rem-0.2em)] py-1 text-[10px] font-medium uppercase tracking-[0.2em] ring-1 <?php echo esc_attr( $status_badge ); ?>">
				<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
			</span>

			<h1 class="mt-5 text-4xl md:text-5xl font-black tracking-tight text-forest-green dark:text-white">
				<?php
				echo $is_failed
					? esc_html__( 'No pudimos procesar tu pago', 'amazonia-theme' )
					: esc_html__( '¡Gracias por tu compra!', 'amazonia-theme' );
				?>
			</h1>

			<?php if ( $is_failed ) : ?>
				<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed mx-auto mt-4 max-w-xl text-base leading-relaxed text-slate-500 dark:text-slate-400">
					<?php esc_html_e( 'Tu banco o el medio de pago rechazó la transacción. Puedes intentarlo de nuevo con otro método; tu pedido sigue reservado.', 'amazonia-theme' ); ?>
				</p>
			<?php else : ?>
				<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received mx-auto mt-4 max-w-xl text-base leading-relaxed text-slate-500 dark:text-slate-400">
					<?php echo wp_kses_post( $received_text ); ?>
				</p>
				<?php if ( $order->get_billing_email() && $show_customer_details ) : ?>
					<p class="mt-2 text-sm text-slate-400 dark:text-slate-500">
						<?php
						printf(
							/* translators: %s: correo de facturación del cliente. */
							esc_html__( 'Enviamos la confirmación a %s', 'amazonia-theme' ),
							'<span class="font-semibold text-slate-600 dark:text-slate-300">' . esc_html( $order->get_billing_email() ) . '</span>'
						);
						?>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		</header>

		<!-- ── Resumen en tarjetas ─────────────────────────────────────── -->
		<!-- <ul class="woocommerce-order-overview woocommerce-thankyou-order-details mt-12 grid grid-cols-2 lg:grid-cols-4 gap-3 list-none p-0"> -->
		<div class="w-full p-4 text-center">
			<li class="woocommerce-order-overview__order order rounded-2xl bg-white dark:bg-white/[0.04] ring-1 ring-black/5 dark:ring-white/10 p-5 flex flex-col justify-center">
				<div class="w-full  p-4 text-center">
					<span class="block text-[10px] font-medium uppercase tracking-[0.2em] text-slate-400"><?php esc_html_e( 'N.º de pedido', 'amazonia-theme' ); ?></span>
					<strong class="mt-2 block text-lg font-black tracking-tight text-slate-900 dark:text-white">
						#<?php echo esc_html( $order->get_order_number() ); ?>
					</strong>
				</div>
			</li>

			<li class="woocommerce-order-overview__date date rounded-2xl bg-white dark:bg-white/[0.04] ring-1 ring-black/5 dark:ring-white/10 p-5 flex flex-col justify-center">
				<div class="w-full  p-4 text-center">
					<span class="block text-[10px] font-medium uppercase tracking-[0.2em] text-slate-400"><?php esc_html_e( 'Fecha', 'amazonia-theme' ); ?></span>
					<strong class="mt-2 block text-lg font-black tracking-tight text-slate-900 dark:text-white">
						<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
					</strong>
				</div>
			</li>

			<li class="woocommerce-order-overview__payment-method method rounded-2xl bg-white dark:bg-white/[0.04] ring-1 ring-black/5 dark:ring-white/10 p-5 flex flex-col justify-center">
				<div class="w-full  p-4 text-center">
					<span class="block text-[10px] font-medium uppercase tracking-[0.2em] text-slate-400"><?php esc_html_e( 'Método de pago', 'amazonia-theme' ); ?></span>
					<strong class="mt-2 block text-base font-black leading-snug tracking-tight text-slate-900 dark:text-white break-words">
						<?php
						echo $order->get_payment_method_title()
							? wp_kses_post( $order->get_payment_method_title() )
							: '&mdash;';
					?>
				</strong>
			</li>

			<li class="woocommerce-order-overview__total total rounded-2xl bg-primary/5 dark:bg-primary/10 ring-1 ring-primary/15 p-5 flex flex-col justify-center">
				<div class="w-full  p-4 text-center">
					<span class="block text-[10px] font-medium uppercase tracking-[0.2em] text-primary/70"><?php esc_html_e( 'Total', 'amazonia-theme' ); ?></span>
					<strong class="mt-2 block text-lg font-black tracking-tight text-primary">
						<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
					</strong>
				</div>
			</li>
		</div>
		<!-- </ul> -->

		<?php
		// Instrucciones del medio de pago (Mercado Pago, transferencia, etc.).
		// Se capturan para poder enmarcarlas en una tarjeta del tema en lugar
		// de dejarlas sueltas a ancho completo fuera del contenedor.
		ob_start();
		do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
		amazonia_order_received_hook_card( ob_get_clean() );
		?>

		<?php
		// Descargas digitales (si el pedido las tiene).
		if ( $downloads ) :
			?>
			<section class="mt-6 rounded-[2rem] bg-white dark:bg-white/[0.04] ring-1 ring-black/5 dark:ring-white/10 px-6 sm:px-8 py-6 [&_table]:w-full [&_th]:text-left [&_th]:text-[10px] [&_th]:uppercase [&_th]:tracking-[0.2em] [&_th]:text-slate-400 [&_th]:font-medium [&_td]:py-3 [&_td]:border-t [&_td]:border-slate-100 dark:[&_td]:border-white/5">
				<?php
				wc_get_template(
					'order/order-downloads.php',
					array(
						'downloads'  => $downloads,
						'show_title' => true,
					)
				);
				?>
			</section>
		<?php endif; ?>

		<?php
		// Bloques previos a la tabla del pedido (p. ej. el seguimiento de
		// envíos "Fulfillments" de WooCommerce): en tarjeta propia.
		ob_start();
		do_action( 'woocommerce_order_details_before_order_table', $order );
		amazonia_order_received_hook_card( ob_get_clean() );
		?>

		<!-- ── Detalle de los productos ────────────────────────────────── -->
		<section class="woocommerce-order-details !mb-0 mt-6 rounded-[2rem] bg-white dark:bg-white/[0.04] ring-1 ring-black/5 dark:ring-white/10 overflow-hidden">

			<header class="flex items-center justify-between gap-4 px-6 sm:px-8 py-6 border-b border-slate-100 dark:border-white/5">
				<h2 class="woocommerce-order-details__title text-xl font-black tracking-tight text-slate-900 dark:text-white m-0">
					<?php esc_html_e( 'Detalle del pedido', 'amazonia-theme' ); ?>
				</h2>
				<span class="-mr-[0.2em] shrink-0 text-[10px] font-medium uppercase tracking-[0.2em] text-slate-400">
					<?php
					printf(
						/* translators: %d: número de artículos del pedido. */
						esc_html( _n( '%d artículo', '%d artículos', count( $order_items ), 'amazonia-theme' ) ),
						count( $order_items )
					);
					?>
				</span>
			</header>

			<ul class="m-0 list-none p-0 divide-y divide-slate-100 dark:divide-white/5">
				<?php
				do_action( 'woocommerce_order_details_before_order_table_items', $order );

				foreach ( $order_items as $item_id => $item ) :
					if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
						continue;
					}

					$product           = $item->get_product();
					$is_visible        = $product && $product->is_visible();
					$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
					$purchase_note     = $product ? $product->get_purchase_note() : '';

					$qty          = $item->get_quantity();
					$refunded_qty = $order->get_qty_refunded_for_item( $item_id );

					// Tienda (vendedor) del artículo — sólo si WCFM está activo.
					$store_name = '';
					$store_url  = '';
					if ( function_exists( 'wcfm_get_vendor_id_by_post' ) && function_exists( 'wcfmmp_get_store' ) ) {
						$vendor_id = wcfm_get_vendor_id_by_post( $item->get_product_id() );
						if ( $vendor_id ) {
							$store = wcfmmp_get_store( $vendor_id );
							if ( $store ) {
								$store_name = $store->get_shop_name();
								$store_url  = $store->get_shop_url();
							}
						}
					}
					?>
					<li class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?> flex items-start gap-4 px-6 sm:px-8 py-5">

						<div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-slate-100 dark:bg-white/5 [&_img]:h-full [&_img]:w-full [&_img]:object-cover">
							<?php
							if ( $product ) {
								echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) );
							}
							?>
						</div>

						<div class="min-w-0 flex-1">
							<h3 class="text-[15px] font-bold leading-snug text-slate-900 dark:text-white m-0 [&_a]:no-underline hover:[&_a]:text-primary [&_a]:transition">
								<?php
								echo wp_kses_post(
									apply_filters(
										'woocommerce_order_item_name',
										$product_permalink ? sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), esc_html( $item->get_name() ) ) : esc_html( $item->get_name() ),
										$item,
										$is_visible
									)
								);
								?>
							</h3>

							<?php if ( $store_name ) : ?>
								<p class="mt-1 flex items-center gap-1 text-xs text-slate-400 m-0">
									<span class="material-symbols-outlined text-[14px]" aria-hidden="true">storefront</span>
									<?php if ( $store_url ) : ?>
										<a href="<?php echo esc_url( $store_url ); ?>" class="no-underline hover:text-primary transition"><?php echo esc_html( $store_name ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $store_name ); ?>
									<?php endif; ?>
								</p>
							<?php endif; ?>

							<div class="mt-1 space-y-0.5 text-xs text-slate-500 dark:text-slate-400">
								<?php
								do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

								foreach ( $item->get_formatted_meta_data() as $meta ) {
									// WCFM guarda la tienda como metadato del artículo; se omite
									// porque ya se muestra arriba con su enlace.
									if ( $store_name && trim( wp_strip_all_tags( $meta->display_value ) ) === $store_name ) {
										continue;
									}
									printf(
										'<span class="block"><span class="font-semibold">%s:</span> <span class="[&_p]:m-0 [&_p]:inline">%s</span></span>',
										wp_kses_post( $meta->display_key ),
										wp_kses_post( $meta->display_value )
									);
								}

								do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
								?>
							</div>

							<p class="product-quantity mt-2 text-xs font-medium text-slate-400 m-0">
								<?php esc_html_e( 'Cantidad:', 'amazonia-theme' ); ?>
								<?php if ( $refunded_qty ) : ?>
									<del class="text-slate-300"><?php echo esc_html( $qty ); ?></del>
									<ins class="no-underline font-bold text-slate-600 dark:text-slate-300"><?php echo esc_html( $qty - ( $refunded_qty * -1 ) ); ?></ins>
								<?php else : ?>
									<span class="font-bold text-slate-600 dark:text-slate-300"><?php echo esc_html( $qty ); ?></span>
								<?php endif; ?>
							</p>

							<?php if ( $show_purchase_note && $purchase_note ) : ?>
								<div class="product-purchase-note mt-3 rounded-xl bg-primary/5 px-4 py-3 text-xs leading-relaxed text-slate-600 dark:text-slate-300 [&_p]:m-0">
									<?php echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) ); ?>
								</div>
							<?php endif; ?>
						</div>

						<div class="product-total shrink-0 text-right text-[15px] font-black tracking-tight text-slate-900 dark:text-white">
							<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
						</div>

					</li>
					<?php
				endforeach;

				do_action( 'woocommerce_order_details_after_order_table_items', $order );
				?>
			</ul>

			<!-- Totales -->
			<footer class="border-t border-slate-100 dark:border-white/5 bg-slate-50/70 dark:bg-white/[0.02] px-6 sm:px-8 py-6">
				<dl class="m-0 space-y-3">
					<?php foreach ( $item_totals as $key => $total ) : ?>
						<?php
						// El método de pago ya tiene su propia tarjeta arriba.
						if ( 'payment_method' === $key ) {
							continue;
						}
						$is_grand_total = ( 'order_total' === $key );
						?>
						<?php /* items-baseline: la etiqueta y el importe tienen tamaños distintos y con items-start no compartían línea base. */ ?>
						<div class="flex items-baseline justify-between gap-6 <?php echo $is_grand_total ? 'pt-3 mt-3 border-t border-slate-200 dark:border-white/10' : ''; ?>">
							<dt class="m-0 <?php echo $is_grand_total ? 'text-base font-black tracking-tight text-slate-900 dark:text-white' : 'text-sm text-slate-500 dark:text-slate-400'; ?>">
								<?php echo esc_html( $total['label'] ); ?>
							</dt>
							<dd class="m-0 text-right <?php echo $is_grand_total ? 'text-xl font-black tracking-tight text-primary' : 'text-sm font-semibold text-slate-700 dark:text-slate-200'; ?> [&_small]:block [&_small]:text-[11px] [&_small]:font-normal [&_small]:text-slate-400">
								<?php echo wp_kses_post( $total['value'] ); ?>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>

				<?php if ( $order->get_customer_note() ) : ?>
					<div class="mt-6 rounded-2xl bg-white dark:bg-white/[0.04] ring-1 ring-black/5 dark:ring-white/10 px-5 py-4">
						<span class="block text-[10px] font-medium uppercase tracking-[0.2em] text-slate-400"><?php esc_html_e( 'Nota del pedido', 'amazonia-theme' ); ?></span>
						<p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300 m-0">
							<?php echo wp_kses( nl2br( wc_wptexturize_order_note( $order->get_customer_note() ) ), array( 'br' => array() ) ); ?>
						</p>
					</div>
				<?php endif; ?>
			</footer>

		</section>

		<?php
		// Contenido que los plugins añaden tras la tabla del pedido
		// (políticas de la tienda en WCFM, botón "pedir de nuevo", recibos…).
		// WCFM imprime su bloque con estilos en línea (títulos cian, tablas
		// grises), así que se neutralizan con `!` para respetar la paleta.
		$vendor_block_reset = implode(
			' ',
			array(
				// Sólo los <br> sueltos que WCFM deja como hijos directos;
				// los de dentro de las celdas separan líneas y deben quedarse.
				'[&>br]:hidden',
				'[&_h2]:!m-0 [&_h2]:!mb-2 [&_h2]:!p-0 [&_h2]:!text-base [&_h2]:!font-black [&_h2]:!leading-snug',
				'[&_h2]:!text-slate-900 dark:[&_h2]:!text-white [&_h2]:!no-underline',
				'[&_table]:!w-full [&_table]:!m-0 [&_table]:!border-0',
				'[&_th]:!w-2/5 [&_th]:!bg-transparent [&_th]:!py-3 [&_th]:!pl-0 [&_th]:!pr-4',
				'[&_th]:!align-top [&_th]:!text-left [&_th]:!text-[11px] [&_th]:!font-semibold',
				'[&_th]:!uppercase [&_th]:!tracking-wider [&_th]:!leading-relaxed [&_th]:!text-slate-400',
				'[&_td]:!bg-transparent [&_td]:!py-3 [&_td]:!pl-0 [&_td]:!align-top',
				'[&_th]:!border-t [&_td]:!border-t [&_th]:!border-slate-100 [&_td]:!border-slate-100',
				'dark:[&_th]:!border-white/5 dark:[&_td]:!border-white/5',
				'[&_tr:first-child_th]:!border-t-0 [&_tr:first-child_td]:!border-t-0',
			)
		);

		ob_start();
		do_action( 'woocommerce_order_details_after_order_table', $order );
		do_action( 'woocommerce_after_order_details', $order );
		amazonia_order_received_hook_card( ob_get_clean(), $vendor_block_reset );
		?>

		<?php if ( $show_customer_details ) : ?>
			<!-- ── Direcciones ─────────────────────────────────────────── -->
			<section class="woocommerce-customer-details !mb-0 mt-6 grid grid-cols-1 <?php echo $show_shipping ? 'md:grid-cols-2' : ''; ?> gap-6">

				<div class="woocommerce-column woocommerce-column--billing-address rounded-[2rem] bg-white dark:bg-white/[0.04] ring-1 ring-black/5 dark:ring-white/10 px-6 sm:px-8 py-6">
					<h2 class="woocommerce-column__title flex items-center gap-2 text-[10px] font-medium uppercase tracking-[0.2em] text-slate-400 m-0">
						<span class="material-symbols-outlined text-[16px]" aria-hidden="true">payments</span>
						<?php esc_html_e( 'Dirección de facturación', 'amazonia-theme' ); ?>
					</h2>
					<address class="mt-3 not-italic text-sm leading-relaxed text-slate-600 dark:text-slate-300">
						<?php echo wp_kses_post( $order->get_formatted_billing_address( esc_html__( 'No disponible', 'amazonia-theme' ) ) ); ?>

						<?php if ( $order->get_billing_phone() ) : ?>
							<span class="woocommerce-customer-details--phone before:hidden !pl-0 mt-2 flex items-center gap-2 text-slate-500 dark:text-slate-400">
								<span class="material-symbols-outlined text-[16px] text-slate-400" aria-hidden="true">call</span>
								<?php echo esc_html( $order->get_billing_phone() ); ?>
							</span>
						<?php endif; ?>

						<?php if ( $order->get_billing_email() ) : ?>
							<span class="woocommerce-customer-details--email before:hidden !pl-0 mt-1 flex items-center gap-2 text-slate-500 dark:text-slate-400 break-all">
								<span class="material-symbols-outlined text-[16px] text-slate-400" aria-hidden="true">mail</span>
								<?php echo esc_html( $order->get_billing_email() ); ?>
							</span>
						<?php endif; ?>

						<?php do_action( 'woocommerce_order_details_after_customer_address', 'billing', $order ); ?>
					</address>
				</div>

				<?php if ( $show_shipping ) : ?>
					<div class="woocommerce-column woocommerce-column--shipping-address rounded-[2rem] bg-white dark:bg-white/[0.04] ring-1 ring-black/5 dark:ring-white/10 px-6 sm:px-8 py-6">
						<h2 class="woocommerce-column__title flex items-center gap-2 text-[10px] font-medium uppercase tracking-[0.2em] text-slate-400 m-0">
							<span class="material-symbols-outlined text-[16px]" aria-hidden="true">local_shipping</span>
							<?php esc_html_e( 'Dirección de envío', 'amazonia-theme' ); ?>
						</h2>
						<address class="mt-3 not-italic text-sm leading-relaxed text-slate-600 dark:text-slate-300">
							<?php echo wp_kses_post( $order->get_formatted_shipping_address( esc_html__( 'No disponible', 'amazonia-theme' ) ) ); ?>

							<?php if ( $order->get_shipping_phone() ) : ?>
								<span class="woocommerce-customer-details--phone before:hidden !pl-0 mt-2 flex items-center gap-2 text-slate-500 dark:text-slate-400">
									<span class="material-symbols-outlined text-[16px] text-slate-400" aria-hidden="true">call</span>
									<?php echo esc_html( $order->get_shipping_phone() ); ?>
								</span>
							<?php endif; ?>

							<?php do_action( 'woocommerce_order_details_after_customer_address', 'shipping', $order ); ?>
						</address>
					</div>
				<?php endif; ?>
			</section>

			<?php
			// Campos extra del cliente que añadan los plugins (fuera de la
			// cuadrícula para que no se cuelen como una tercera columna).
			ob_start();
			do_action( 'woocommerce_order_details_after_customer_details', $order );
			amazonia_order_received_hook_card( ob_get_clean() );
			?>
		<?php endif; ?>

		<?php
		// Hook general de la pantalla de gracias. La tabla por defecto de
		// WooCommerce ya se desenganchó en checkout/thankyou.php, así que aquí
		// sólo llega lo que añadan los plugins.
		ob_start();
		do_action( 'woocommerce_thankyou', $order->get_id() );
		amazonia_order_received_hook_card( ob_get_clean() );
		?>

		<!-- ── Acciones ────────────────────────────────────────────────── -->
		<div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-3">

			<?php if ( $is_failed ) : ?>
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"
				   class="button pay inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-full bg-primary px-7 py-3.5 text-sm font-bold text-white no-underline transition hover:bg-forest-green">
					<span class="material-symbols-outlined -ml-1 text-[18px]" aria-hidden="true">payments</span>
					<?php esc_html_e( 'Reintentar el pago', 'amazonia-theme' ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
				   class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-full bg-primary px-7 py-3.5 text-sm font-bold text-white no-underline transition hover:bg-forest-green">
					<?php esc_html_e( 'Seguir comprando', 'amazonia-theme' ); ?>
					<span class="material-symbols-outlined -mr-1 text-[18px]" aria-hidden="true">arrow_forward</span>
				</a>
			<?php endif; ?>

			<?php if ( is_user_logged_in() && $show_customer_details ) : ?>
				<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>"
				   class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-full bg-white dark:bg-white/[0.06] px-7 py-3.5 text-sm font-bold text-slate-700 dark:text-slate-200 no-underline ring-1 ring-black/5 dark:ring-white/10 transition hover:ring-primary/30 hover:text-primary">
					<span class="material-symbols-outlined -ml-1 text-[18px]" aria-hidden="true">inventory_2</span>
					<?php esc_html_e( 'Ver mi pedido', 'amazonia-theme' ); ?>
				</a>
			<?php endif; ?>

		</div>

		<p class="mt-8 text-center text-xs text-slate-400">
			<?php esc_html_e( '¿Necesitas ayuda con este pedido? Escríbenos y te acompañamos.', 'amazonia-theme' ); ?>
		</p>

	<?php endif; ?>

	</div>
</div>
