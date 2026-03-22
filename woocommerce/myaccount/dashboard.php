<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_html = array(
	'a'      => array(
		'href' => array(),
		'class' => array(),
	),
	'strong' => array(),
	'span' => array(
		'class' => array(),
	),
);
?>

<div class="mb-10">
	<h2 class="text-3xl md:text-4xl font-black text-slate-800 dark:text-slate-100 tracking-tight mb-4 flex items-center flex-wrap gap-2">
		<?php
		printf(
			/* translators: 1: user display name */
			wp_kses( __( 'Hola %1$s', 'woocommerce' ), $allowed_html ),
			'<span class="text-primary">' . esc_html( $current_user->display_name ) . '</span>'
		);
		?>
		<span class="text-primary font-black">!</span>
	</h2>
	<p class="text-slate-500 text-sm md:text-base mb-2">
		<?php
		printf(
			/* translators: 1: logout url */
			wp_kses( __( '¿No eres %1$s? <a href="%2$s" class="text-primary hover:underline font-medium ml-1">Cerrar sesión</a>', 'woocommerce' ), $allowed_html ),
			'<strong>' . esc_html( $current_user->display_name ) . '</strong>',
			esc_url( wc_logout_url() )
		);
		?>
	</p>
	<p class="text-slate-500 text-sm md:text-base leading-relaxed max-w-2xl mt-4">
		Desde el panel de control de tu cuenta puedes ver tus pedidos recientes, gestionar tus direcciones de envío y facturación, y editar tu contraseña y los detalles de la cuenta.
	</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
	<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>" class="group bg-slate-50 dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:border-primary/30 transition-all duration-300 hover:shadow-xl flex flex-col items-center text-center">
		<div class="h-16 w-16 bg-white dark:bg-slate-800 shadow-sm border border-primary/10 rounded-full flex items-center justify-center text-primary mb-4 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all duration-300">
			<span class="material-symbols-outlined text-[32px]">shopping_bag</span>
		</div>
		<h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 mb-2 group-hover:text-primary transition-colors">Mis Pedidos</h3>
		<p class="text-sm text-slate-500">Ver el estado de tus compras y descargas</p>
	</a>
	
	<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); ?>" class="group bg-slate-50 dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:border-primary/30 transition-all duration-300 hover:shadow-xl flex flex-col items-center text-center">
		<div class="h-16 w-16 bg-white dark:bg-slate-800 shadow-sm border border-primary/10 rounded-full flex items-center justify-center text-primary mb-4 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all duration-300">
			<span class="material-symbols-outlined text-[32px]">location_on</span>
		</div>
		<h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 mb-2 group-hover:text-primary transition-colors">Direcciones</h3>
		<p class="text-sm text-slate-500">Gestionar opciones de envío y facturación</p>
	</a>
	
	<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); ?>" class="group bg-slate-50 dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:border-primary/30 transition-all duration-300 hover:shadow-xl flex flex-col items-center text-center">
		<div class="h-16 w-16 bg-white dark:bg-slate-800 shadow-sm border border-primary/10 rounded-full flex items-center justify-center text-primary mb-4 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all duration-300">
			<span class="material-symbols-outlined text-[32px]">manage_accounts</span>
		</div>
		<h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 mb-2 group-hover:text-primary transition-colors">Detalles de Cuenta</h3>
		<p class="text-sm text-slate-500">Actualizar datos personales y contraseña</p>
	</a>
</div>

<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );
?>
