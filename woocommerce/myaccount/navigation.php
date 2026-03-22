<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/navigation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation bg-white dark:bg-slate-800 p-6 rounded-2xl border border-primary/5 shadow-sm !w-full !float-none" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
	<div class="flex items-center justify-between mb-6 md:mb-4 lg:mb-6">
		<h3 class="font-bold text-lg flex items-center gap-2 text-slate-800 dark:text-slate-100">
			<span class="material-symbols-outlined text-primary">account_circle</span> Mi Cuenta
		</h3>
	</div>

	<ul class="flex flex-col md:flex-row lg:flex-col space-y-2 md:space-y-0 md:space-x-2 lg:space-x-0 lg:space-y-2 md:overflow-x-auto lg:overflow-visible pb-2 md:pb-4 lg:pb-0">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : 
			$is_current = wc_is_current_account_menu_item( $endpoint );
			$active_class = $is_current ? 'bg-primary/10 text-primary font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-primary hover:bg-slate-50 dark:hover:bg-slate-700/50';
			
			$icon = 'chevron_right';
			if ($endpoint === 'dashboard') $icon = 'dashboard';
			if ($endpoint === 'orders') $icon = 'shopping_bag';
			if ($endpoint === 'downloads') $icon = 'download';
			if ($endpoint === 'edit-address') $icon = 'location_on';
			if ($endpoint === 'edit-account') $icon = 'manage_accounts';
			if ($endpoint === 'customer-logout') $icon = 'logout';
		?>
			<li class="flex-shrink-0 <?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors whitespace-nowrap <?php echo $active_class; ?>" <?php echo $is_current ? 'aria-current="page"' : ''; ?>>
					<span class="material-symbols-outlined text-[20px]"><?php echo $icon; ?></span>
					<span><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
