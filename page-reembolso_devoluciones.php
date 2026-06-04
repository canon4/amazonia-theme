<?php
/**
 * Template Name: Política de Devoluciones y Reembolsos
 * Description: Plantilla dedicada para mostrar la Política de Devoluciones y Reembolsos de Amazonia Market.
 */

get_header();
?>

<main id="primary" class="site-main bg-slate-50 dark:bg-background-dark pb-20">

    <!-- Header Banner -->
    <header class="entry-header bg-white dark:bg-slate-900 py-10 lg:py-16 mb-12 border-b border-primary/10 relative overflow-hidden">
        <!-- Decorative blur -->
        <div style="position:absolute; top:-50px; right:-50px; width:250px; height:250px; background:radial-gradient(circle, rgba(74,222,128,0.1) 0%, transparent 70%); border-radius:50%; pointer-events:none;"></div>
        
        <div class="max-w-7xl mx-auto px-6 lg:px-20 relative z-10">
            <?php 
                if ( function_exists( 'woocommerce_breadcrumb' ) ) {
                    woocommerce_breadcrumb( array(
                        'delimiter'   => ' <span class="material-symbols-outlined text-[16px] text-slate-400 align-middle">chevron_right</span> ',
                        'wrap_before' => '<nav class="woocommerce-breadcrumb flex items-center gap-1 text-slate-500 font-medium text-sm mb-4" itemprop="breadcrumb">',
                        'wrap_after'  => '</nav>',
                    ) );
                }
            ?>
            <h1 class="text-4xl lg:text-6xl font-black text-green-900 dark:text-slate-100 mb-2">
                Política de Devoluciones y Reembolsos
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm lg:text-base max-w-2xl font-light leading-relaxed">
                Condiciones y procesos para cambios y reembolsos en Amazonia Market de forma justa y equitativa.
            </p>
        </div>
    </header>

    <!-- Main Content Container -->
    <div class="max-w-4xl mx-auto px-6 text-slate-700 dark:text-slate-300 leading-relaxed font-sans">
        
        <!-- Intro Box -->
        <div class="mb-12 p-6 bg-green-50 dark:bg-green-950/20 border-l-4 border-green-600 rounded-r-2xl shadow-sm">
            <p class="text-lg font-semibold text-green-900 dark:text-green-300 mb-2">Compromiso de Comercio Justo</p>
            <p class="text-sm text-green-800 dark:text-green-400">
                En <strong>Amazonia Market</strong>, cada producto es una pieza única elaborada a mano por artesanos de comunidades indígenas y afrocolombianas. Valoramos y respetamos el tiempo, la dedicación y las técnicas ancestrales invertidas en cada obra. Por ello, nuestra política busca equilibrar la satisfacción de nuestros compradores con el sustento justo de nuestros productores asociados.
            </p>
        </div>

        <!-- Section 1: Naturaleza del Producto -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">nature_people</span>
                1. Naturaleza del Producto Artesanal
            </h2>
            <p class="mb-4">
                Debido a que nuestros productos son elaborados utilizando materias primas 100% orgánicas y procesos manuales tradicionales (como tintes naturales y tejido de fibras de cumare, yaré, etc.), <strong>las ligeras variaciones en tonos, texturas, dimensiones o patrones no se consideran defectos</strong>, sino firmas de autenticidad cultural y valor artístico del producto.
            </p>
        </section>

        <!-- Section 2: Plazos -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">schedule</span>
                2. Plazos para Solicitar Devoluciones
            </h2>
            <p>
                Dispones de un plazo máximo de <strong>14 días calendario</strong> a partir de la fecha de entrega del paquete registrado por la transportadora para reportar cualquier novedad y solicitar una devolución o reembolso.
            </p>
        </section>

        <!-- Section 3: Condiciones -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">assignment_turned_in</span>
                3. Condiciones para la Aceptación
            </h2>
            <p class="mb-4">Para que la devolución sea procesada con éxito, el artículo debe cumplir los siguientes requisitos:</p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>Estar en perfectas condiciones, sin rastros de uso, lavado o alteraciones.</li>
                <li>Conservar su empaque original, etiquetas de la comunidad y certificados de autenticidad cultural (si venían incluidos).</li>
                <li>Presentar el comprobante de compra o factura electrónica de la plataforma.</li>
            </ul>
        </section>

        <!-- Section 4: Excepciones -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">block</span>
                4. Artículos no Sujetos a Devolución
            </h2>
            <p class="mb-4">Por razones de higiene y naturaleza del producto, quedan excluidos de cambios o reembolsos:</p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>Productos personalizados o fabricados bajo pedido especial de diseño.</li>
                <li>Productos de uso cosmético natural, aceites esenciales o aseo personal con sellos abiertos.</li>
                <li>Alimentos perecederos o productos culinarios típicos ya destapados.</li>
            </ul>
        </section>

        <!-- Section 5: Gastos de Envío -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">local_shipping</span>
                5. Gastos de Envío y Retorno
            </h2>
            <ul class="list-disc pl-6 space-y-3 mb-4">
                <li><strong>Por daño o error en el envío:</strong> Si el producto llega roto, defectuoso o no corresponde al comprado, Amazonia Market asumirá el 100% de los costos de envío de retorno y reemplazo.</li>
                <li><strong>Por retracto del comprador:</strong> Si decides desistir de la compra por razones personales o gusto, deberás asumir el costo del envío de retorno hacia el centro de acopio de la comunidad productora correspondiente.</li>
            </ul>
        </section>

        <!-- Section 6: Reembolsos -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">payments</span>
                6. Procesamiento de Reembolsos
            </h2>
            <p class="mb-4">
                Una vez recibido el producto en bodega e inspeccionado su estado, te notificaremos por correo electrónico sobre la aprobación o rechazo del reembolso.
            </p>
            <p class="mb-4">
                De ser aprobado, se procesará el reembolso de la siguiente forma:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li><strong>Opción recomendada:</strong> Un saldo a favor o bono digital para redimir en cualquier tienda de Amazonia Market (apoyo continuo a las comunidades).</li>
                <li><strong>Reverso de pago:</strong> Reembolso al medio original de pago (tarjeta de crédito o transferencia bancaria) en un plazo aproximado de 5 a 10 días hábiles.</li>
            </ul>
        </section>

        <!-- Contact Box -->
        <div class="mt-14 p-8 bg-white dark:bg-slate-900 rounded-2xl text-center border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-lg font-bold text-green-900 dark:text-slate-100 mb-2">¿Necesitas ayuda con un pedido o devolución?</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 max-w-lg mx-auto">
                Escríbenos y con gusto te acompañaremos en todo el proceso de soporte con el productor.
            </p>
            <a href="mailto:soporte@amazoniamarket.com" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-8 py-3 rounded-full transition shadow-md hover:shadow-lg">
                <span class="material-symbols-outlined text-[18px]">mail</span>
                XXXXXX@amazoniamarket.com
            </a>
        </div>

    </div>

</main>

<?php
get_footer();
