<?php
/**
 * Login Form
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<style>
/* Forzar que la página de login sea completamente independiente */
header, 
.site-header, 
footer, 
.site-footer, 
.woocommerce-breadcrumb, 
.entry-header, 
.page-header,
h1.page-title,
h1.entry-title {
	display: none !important;
}

main#primary, 
.site-main, 
.page-content, 
.type-page {
	padding: 0 !important;
	margin: 0 !important;
	max-width: 100% !important;
	width: 100% !important;
}

.woocommerce {
	margin: 0 !important;
	padding: 0 !important;
	width: 100% !important;
}

body,
html {
	margin: 0;
	padding: 0;
	height: 100%;
	overflow: hidden;
	background-color: #f6f8f6;
}
</style>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="w-full flex flex-col lg:flex-row bg-white dark:bg-slate-900" id="customer_login" style="height:100vh;overflow:hidden;">

	<!-- Left Side: Image / Branding (Hidden on small screens) -->
	<div class="hidden lg:flex lg:w-5/12 xl:w-1/2 relative flex-col justify-between overflow-hidden bg-slate-900" style="height:100vh;">
		<div class="absolute inset-0 z-0">
			<img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAq_j6ErCDkwbEdYp9Q1sGPnpTM3ydBN291StZX9oPUDId6IhpaKQxrBm2MG2ziGJQLy2iwxlJfh8LSgZ-WeJf-h5iA-cd6cphz2ls2ogwXffrZdcggDaQtMwR8QvKYE5ceOs71iYlTuClyoyRgUJqItga_Fc8O0pyzl67oeLFu0LUcWa4Mn2ciQgsWs-nXKDFnpC3os_LNzu6mbhHGTEopssvW6y9T6QhDNtJXdk0gBNWYw1UZt1NXc3Hfh-HupmO4CLnDmhYtKhs"
				 alt="Selva Amazónica" class="w-full h-full object-cover opacity-80"
				 fetchpriority="high" width="1920" height="1080" />
			<div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-transparent"></div>
		</div>

		<!-- Logo Top Left -->
		<div class="relative z-10 p-10">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center gap-2 text-primary hover:text-white transition-colors duration-300">
				<span class="material-symbols-outlined text-4xl font-bold">eco</span>
				<span class="text-white text-2xl font-black tracking-tight">Amazonia Market</span>
			</a>
		</div>

		<div class="relative z-10 p-10 max-w-xl">
			<span class="inline-block px-3 py-1 bg-primary text-white text-xs font-bold rounded-full mb-4 uppercase tracking-wider">
				Bienvenido de vuelta
			</span>
			<h1 class="text-white text-4xl lg:text-5xl font-black leading-tight mb-4">
				Conectando la riqueza de la selva con el mundo.
			</h1>
			<p class="text-slate-200 text-lg leading-relaxed">
				Accede a tu cuenta para gestionar tus productos, pedidos o descubrir bioproductos sostenibles.
			</p>
		</div>
	</div>

	<!-- Right Side: Forms Container -->
	<div class="w-full lg:w-7/12 xl:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-16 relative overflow-y-auto" style="height:100vh;">

		<!-- Botón volver a la tienda -->
		<div class="absolute top-6 right-6 z-20">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
			   class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-primary transition-colors duration-200 group">
				<span class="material-symbols-outlined text-[18px] group-hover:-translate-x-0.5 transition-transform duration-200">storefront</span>
				Volver a la tienda
			</a>
		</div>

		<!-- Mobile Logo (only shows on mobile) -->
		<div class="absolute top-6 left-6 lg:hidden flex items-center gap-2 text-primary z-20">
			<span class="material-symbols-outlined text-3xl font-bold">eco</span>
			<span class="text-slate-900 dark:text-slate-100 text-xl font-black tracking-tight">Amazonia Market</span>
		</div>

		<div class="w-full max-w-md pt-12 lg:pt-0">

			<!-- Woocommerce Registration Check -->
			<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
			
			<div class="flex border-b border-primary/20 mb-10" role="tablist">
				<button class="flex-1 py-4 text-center font-bold text-lg text-primary border-b-2 border-primary focus:outline-none transition-all" onclick="document.getElementById('login-form').classList.remove('hidden'); document.getElementById('register-form').classList.add('hidden'); this.classList.add('text-primary', 'border-primary'); this.classList.remove('text-slate-400', 'border-transparent'); this.nextElementSibling.classList.add('text-slate-400', 'border-transparent'); this.nextElementSibling.classList.remove('text-primary', 'border-primary');" type="button">
					<?php esc_html_e( 'Login', 'woocommerce' ); ?>
				</button>
				<button class="flex-1 py-4 text-center font-bold text-lg text-slate-400 border-b-2 border-transparent hover:text-primary transition-all focus:outline-none" onclick="document.getElementById('register-form').classList.remove('hidden'); document.getElementById('login-form').classList.add('hidden'); this.classList.add('text-primary', 'border-primary'); this.classList.remove('text-slate-400', 'border-transparent'); this.previousElementSibling.classList.add('text-slate-400', 'border-transparent'); this.previousElementSibling.classList.remove('text-primary', 'border-primary');" type="button">
					<?php esc_html_e( 'Register', 'woocommerce' ); ?>
				</button>
			</div>

			<div class="u-column1 col-1 w-full animate-fade-in" id="login-form">

			<?php else : ?>

			<div class="u-column1 col-1 w-full" id="login-form">
				<div class="mb-10 text-center lg:text-left">
					<h2 class="text-3xl font-black text-slate-900 dark:text-white mb-3"><?php esc_html_e( 'Login', 'woocommerce' ); ?></h2>
					<p class="text-slate-500 dark:text-slate-400">Ingresa tus datos para continuar.</p>
				</div>

			<?php endif; ?>

				<form class="woocommerce-form woocommerce-form-login login space-y-6" method="post" novalidate>

					<?php do_action( 'woocommerce_login_form_start' ); ?>

					<div class="woocommerce-form-row form-row">
						<label for="username" class="block font-semibold text-sm mb-2 text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Email', 'woocommerce' ); ?>&nbsp;<span class="text-red-500">*</span></label>
						<div class="relative">
							<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-primary/60">
								<span class="material-symbols-outlined text-[20px]">person</span>
							</div>
							<input type="text" class="w-full pl-11 pr-4 py-3 bg-primary/5 border border-primary/20 rounded-xl text-slate-900 dark:text-white dark:bg-slate-800 focus:ring-1 focus:ring-primary focus:border-primary transition-all duration-300 outline-none placeholder:text-slate-400" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
						</div>
					</div>

					<div class="woocommerce-form-row form-row">
						<label for="password" class="block font-semibold text-sm mb-2 text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="text-red-500">*</span></label>
						<div class="relative">
							<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-primary/60">
								<span class="material-symbols-outlined text-[20px]">lock</span>
							</div>
							<input class="w-full pl-11 pr-4 py-3 bg-primary/5 border border-primary/20 rounded-xl text-slate-900 dark:text-white dark:bg-slate-800 focus:ring-1 focus:ring-primary focus:border-primary transition-all duration-300 outline-none placeholder:text-slate-400" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
						</div>
					</div>

					<?php do_action( 'woocommerce_login_form' ); ?>

					<div class="flex items-center justify-between pt-2">
						<label class="flex items-center gap-2 cursor-pointer group">
							<input class="rounded text-primary focus:ring-primary h-4 w-4 bg-primary/5 border-primary/20 transition-all" name="rememberme" type="checkbox" id="rememberme" value="forever" /> 
							<span class="text-sm font-medium text-slate-600 dark:text-slate-400 group-hover:text-primary transition-colors"><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
						</label>
						<a class="text-sm text-primary font-bold hover:underline transition-all" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
					</div>
					
					<div class="pt-4">
						<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
						<button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3.5 px-8 rounded-full transition-all duration-300 transform hover:scale-[1.02] shadow-lg shadow-primary/20 flex items-center justify-center gap-2 group" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
							<span><?php esc_html_e( 'Log in', 'woocommerce' ); ?></span>
							<span class="material-symbols-outlined text-[20px] group-hover:translate-x-1 transition-transform">login</span>
						</button>
					</div>

					<?php do_action( 'woocommerce_login_form_end' ); ?>

				</form>
			</div>

			<!-- Vendor Registration CTA -->
			<div class="mt-8 pt-8 border-t border-primary/10 text-center">
				<p class="text-sm text-slate-500 dark:text-slate-400 mb-3">
					¿Quieres vender en Amazonia Market?
				</p>
				<a href="<?php echo esc_url( home_url( '/vendor-register' ) ); ?>"
				   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full border-2 border-primary text-primary font-bold text-sm hover:bg-primary hover:text-white transition-all duration-300 group">
					<span class="material-symbols-outlined text-[18px]">storefront</span>
					Regístrate como Vendedor
					<span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
				</a>
			</div>

<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

			<div class="u-column2 col-2 hidden w-full animate-fade-in" id="register-form">

				<form method="post" class="woocommerce-form woocommerce-form-register register space-y-6" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

						<div class="woocommerce-form-row form-row">
							<label for="reg_username" class="block font-semibold text-sm mb-2 text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="text-red-500">*</span></label>
							<div class="relative">
								<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-primary/60">
									<span class="material-symbols-outlined text-[20px]">badge</span>
								</div>
								<input type="text" class="w-full pl-11 pr-4 py-3 bg-primary/5 border border-primary/20 rounded-xl text-slate-900 dark:text-white dark:bg-slate-800 focus:ring-1 focus:ring-primary focus:border-primary transition-all duration-300 outline-none placeholder:text-slate-400" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
							</div>
						</div>

					<?php endif; ?>

					<div class="woocommerce-form-row form-row">
						<label for="reg_email" class="block font-semibold text-sm mb-2 text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="text-red-500">*</span></label>
						<div class="relative">
							<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-primary/60">
								<span class="material-symbols-outlined text-[20px]">mail</span>
							</div>
							<input type="email" class="w-full pl-11 pr-4 py-3 bg-primary/5 border border-primary/20 rounded-xl text-slate-900 dark:text-white dark:bg-slate-800 focus:ring-1 focus:ring-primary focus:border-primary transition-all duration-300 outline-none placeholder:text-slate-400" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" />
						</div>
					</div>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

						<div class="woocommerce-form-row form-row">
							<label for="reg_password" class="block font-semibold text-sm mb-2 text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="text-red-500">*</span></label>
							<div class="relative">
								<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-primary/60">
									<span class="material-symbols-outlined text-[20px]">lock_reset</span>
								</div>
								<input type="password" class="w-full pl-11 pr-4 py-3 bg-primary/5 border border-primary/20 rounded-xl text-slate-900 dark:text-white dark:bg-slate-800 focus:ring-1 focus:ring-primary focus:border-primary transition-all duration-300 outline-none placeholder:text-slate-400" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
							</div>
						</div>

					<?php else : ?>

						<p class="text-sm text-slate-500 bg-primary/5 p-4 rounded-xl border border-primary/10 flex gap-2 items-center">
							<span class="material-symbols-outlined text-primary">info</span>
							<?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?>
						</p>

					<?php endif; ?>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<div class="pt-4">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="w-full border-2 border-primary text-primary dark:text-white hover:bg-primary hover:text-white font-bold py-3.5 px-8 rounded-full transition-all duration-300 transform hover:scale-[1.02] flex items-center justify-center gap-2 group" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
							<span><?php esc_html_e( 'Register', 'woocommerce' ); ?></span>
							<span class="material-symbols-outlined text-[20px] group-hover:translate-x-1 transition-transform">person_add</span>
						</button>
					</div>

					<?php do_action( 'woocommerce_register_form_end' ); ?>

				</form>

			</div>

			<!-- Vendor Registration CTA -->
			<div class="mt-8 pt-8 border-t border-primary/10 text-center">
				<p class="text-sm text-slate-500 dark:text-slate-400 mb-3">
					¿Quieres vender en Amazonia Market?
				</p>
				<a href="<?php echo esc_url( home_url( '/vendor-register' ) ); ?>"
				   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full border-2 border-primary text-primary font-bold text-sm hover:bg-primary hover:text-white transition-all duration-300 group">
					<span class="material-symbols-outlined text-[18px]">storefront</span>
					Regístrate como Vendedor
					<span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
				</a>
			</div>

<?php endif; ?>
		</div>
	</div>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
