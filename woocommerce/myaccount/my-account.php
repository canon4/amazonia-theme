<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="max-w-[1440px] mx-auto w-full px-4 md:px-10 lg:px-20 py-10">
	<div class="flex flex-col lg:flex-row gap-8">
		<aside class="w-full lg:w-80 xl:w-[340px] flex-shrink-0 space-y-8">
			<?php
			/**
			 * My Account navigation.
			 *
			 * @since 2.6.0
			 */
			do_action( 'woocommerce_account_navigation' );
			?>
		</aside>

		<section class="flex-1 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-primary/5 p-6 md:p-10">
			<div class="woocommerce-MyAccount-content !w-full !float-none">
				<?php
					/**
					 * My Account content.
					 *
					 * @since 2.6.0
					 */
					do_action( 'woocommerce_account_content' );
				?>
			</div>
		</section>
	</div>
</div>
