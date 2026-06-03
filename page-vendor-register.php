<?php
/**
 * Template Name: Vendor Register Page
 *
 * Full-screen split-screen layout for the WCFM vendor registration form.
 * Assign this template to the "vendor-register" page in WordPress Admin.
 *
 * @package Amazonia_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<style>
/* Ocultar header, footer y breadcrumbs para experiencia full-screen */
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

<div class="w-full flex flex-col lg:flex-row bg-white" style="height:100vh;overflow:hidden;">

	<!-- Panel izquierdo: imagen y propuesta de valor -->
	<div class="hidden lg:flex lg:w-5/12 xl:w-1/2 relative flex-col justify-between overflow-hidden bg-slate-900" style="height:100vh;">
		<div class="absolute inset-0 z-0">
			<img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAq_j6ErCDkwbEdYp9Q1sGPnpTM3ydBN291StZX9oPUDId6IhpaKQxrBm2MG2ziGJQLy2iwxlJfh8LSgZ-WeJf-h5iA-cd6cphz2ls2ogwXffrZdcggDaQtMwR8QvKYE5ceOs71iYlTuClyoyRgUJqItga_Fc8O0pyzl67oeLFu0LUcWa4Mn2ciQgsWs-nXKDFnpC3os_LNzu6mbhHGTEopssvW6y9T6QhDNtJXdk0gBNWYw1UZt1NXc3Hfh-HupmO4CLnDmhYtKhs"
				 alt="Selva Amazónica"
				 class="w-full h-full object-cover opacity-70" />
			<div class="absolute inset-0 bg-gradient-to-t from-slate-900/95 via-slate-900/50 to-slate-900/20"></div>
		</div>

		<!-- Logo -->
		<div class="relative z-10 p-10">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
			   class="inline-flex items-center gap-2 text-primary hover:text-white transition-colors duration-300">
				<span class="material-symbols-outlined text-4xl font-bold">eco</span>
				<span class="text-white text-2xl font-black tracking-tight">Amazonia Market</span>
			</a>
		</div>

		<!-- Propuesta de valor -->
		<div class="relative z-10 p-10 max-w-xl">
			<span class="inline-block px-3 py-1 bg-primary text-white text-xs font-bold rounded-full mb-6 uppercase tracking-wider">
				Únete como Vendedor
			</span>
			<h1 class="text-white text-4xl lg:text-5xl font-black leading-tight mb-6">
				Lleva tus productos al mundo.
			</h1>
			<p class="text-slate-200 text-lg leading-relaxed mb-8">
				Regístrate como vendedor en Amazonia Market y conecta tus bioproductos sostenibles con clientes en todo el mundo.
			</p>

			<!-- Beneficios -->
			<div class="space-y-4">
				<div class="flex items-center gap-3 text-slate-200">
					<span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center">
						<span class="material-symbols-outlined text-primary text-[18px]">storefront</span>
					</span>
					<span class="text-sm font-medium">Tu propia tienda personalizada</span>
				</div>
				<div class="flex items-center gap-3 text-slate-200">
					<span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center">
						<span class="material-symbols-outlined text-primary text-[18px]">sell</span>
					</span>
					<span class="text-sm font-medium">Gestión de productos y pedidos</span>
				</div>
				<div class="flex items-center gap-3 text-slate-200">
					<span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center">
						<span class="material-symbols-outlined text-primary text-[18px]">payments</span>
					</span>
					<span class="text-sm font-medium">Pagos seguros y retiros automáticos</span>
				</div>
				<div class="flex items-center gap-3 text-slate-200">
					<span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center">
						<span class="material-symbols-outlined text-primary text-[18px]">public</span>
					</span>
					<span class="text-sm font-medium">Alcance internacional</span>
				</div>
			</div>
		</div>
	</div>

	<!-- Panel derecho: formulario -->
	<div class="w-full lg:w-7/12 xl:w-1/2 flex items-start justify-center p-6 sm:p-10 lg:p-12 relative overflow-y-auto" style="height:100vh;">

		<!-- Logo móvil -->
		<div class="absolute top-6 left-6 lg:hidden flex items-center gap-2 text-primary z-20">
			<span class="material-symbols-outlined text-3xl font-bold">eco</span>
			<span class="text-slate-900 text-xl font-black tracking-tight">Amazonia Market</span>
		</div>

		<div class="w-full max-w-md pt-16 lg:pt-2 pb-4">

			<!-- Botón volver atrás -->
			<div class="mb-6">
				<button onclick="history.back()"
				        class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-primary transition-colors duration-200 group cursor-pointer bg-transparent border-none p-0">
					<span class="material-symbols-outlined text-[20px] group-hover:-translate-x-1 transition-transform duration-200">arrow_back</span>
					Volver atrás
				</button>
			</div>

			<!-- Encabezado del formulario -->
			<div class="mb-4">
				<div class="inline-flex items-center gap-2 text-primary mb-2">
					<span class="material-symbols-outlined text-2xl">storefront</span>
					<span class="text-sm font-bold uppercase tracking-widest">Registro de Vendedor</span>
				</div>
				<h2 class="text-3xl font-black text-slate-900 mb-2">
					Crea tu tienda
				</h2>
				<p class="text-slate-500">
					Completa el formulario para empezar a vender en Amazonia Market.
				</p>
			</div>

			<!-- Formulario WCFM -->
			<div class="vendor-register-form-wrapper">
				<?php echo do_shortcode( '[wcfm_vendor_registration]' ); ?>
			</div>

			<!-- Link a login -->
			<div class="mt-4 pt-4 border-t border-primary/10 text-center">
				<p class="text-sm text-slate-500 mb-3">
					¿Ya tienes una cuenta?
				</p>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
				   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full border-2 border-primary text-primary font-bold text-sm hover:bg-primary hover:text-white transition-all duration-300 group">
					<span class="material-symbols-outlined text-[18px]">login</span>
					Iniciar sesión
					<span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
				</a>
			</div>

		</div>
	</div>

</div>

<?php get_footer(); ?>
