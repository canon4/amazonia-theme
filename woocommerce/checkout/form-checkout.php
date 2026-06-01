<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout flex flex-col lg:flex-row gap-12 max-w-[1440px] mx-auto py-12 lg:py-24 px-4 md:px-8" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="lg:w-7/12 space-y-12" id="customer_details">
            <!-- Double-Bezel Outer Shell -->
			<div class="bg-black/5 dark:bg-white/5 ring-1 ring-black/5 dark:ring-white/10 p-2 rounded-[2rem]">
                <!-- Inner Core -->
                <div class="bg-white dark:bg-[#0a0a0a] rounded-[calc(2rem-0.5rem)] p-8 lg:p-12 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]">
                    <h2 class="text-3xl font-black mb-8 tracking-tight">Detalles de Facturación</h2>
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
                    
                    <div class="mt-12 pt-12 border-t border-slate-100 dark:border-white/5">
					    <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                    </div>
                </div>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>
	
	<div class="lg:w-5/12">
        <div class="sticky top-32">
            <!-- Double-Bezel Outer Shell -->
            <div class="bg-primary/5 dark:bg-primary/10 ring-1 ring-primary/10 p-2 rounded-[2rem]">
                <!-- Inner Core -->
                <div class="bg-slate-50 dark:bg-[#111] rounded-[calc(2rem-0.5rem)] p-8 lg:p-10 shadow-[inset_0_1px_1px_rgba(255,255,255,0.5)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]">
                    <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
                    
                    <div class="mb-8">
                        <span class="rounded-full px-3 py-1 text-[10px] uppercase tracking-[0.2em] font-medium bg-primary/10 text-primary">Resumen</span>
                        <h3 id="order_review_heading" class="text-2xl font-black text-slate-900 dark:text-slate-100 mt-3"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
                    </div>
                    
                    <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

                    <div id="order_review" class="woocommerce-checkout-review-order">
                        <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                    </div>

                    <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
                </div>
            </div>
        </div>
	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
