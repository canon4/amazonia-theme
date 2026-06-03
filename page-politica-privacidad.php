<?php
/**
 * Template Name: Política de Privacidad
 * Description: Plantilla dedicada para mostrar la Política de Privacidad de Amazonia Market.
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
                Política de Privacidad
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm lg:text-base max-w-2xl font-light leading-relaxed">
                Tratamiento de datos personales y compromiso de confidencialidad en Amazonia Market de acuerdo con la Ley 1581 de 2012.
            </p>
        </div>
    </header>

    <!-- Main Content Container -->
    <div class="max-w-4xl mx-auto px-6 text-slate-700 dark:text-slate-300 leading-relaxed font-sans">
        
        <!-- Intro Box -->
        <div class="mb-12 p-6 bg-green-50 dark:bg-green-950/20 border-l-4 border-green-600 rounded-r-2xl shadow-sm">
            <p class="text-lg font-semibold text-green-900 dark:text-green-300 mb-2">Protección de Datos y Confidencialidad</p>
            <p class="text-sm text-green-800 dark:text-green-400">
                En <strong>Amazonia Market</strong> nos comprometemos a proteger la privacidad de nuestros usuarios (compradores, artesanos, productores y visitantes). Tratamos tus datos de acuerdo con la legislación colombiana de protección de datos personales (<strong>Ley 1581 de 2012 / Habeas Data</strong>) y estándares internacionales de privacidad.
            </p>
        </div>

        <!-- Section 1: Quiénes Somos -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">info</span>
                1. ¿Quiénes Somos?
            </h2>
            <p class="mb-4">
                Nuestra dirección web es: <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-green-600 dark:text-green-400 underline font-medium hover:text-green-700 dark:hover:text-green-300 transition-colors"><?php echo esc_url( home_url( '/' ) ); ?></a>.
            </p>
            <p>
                Amazonia Market es una plataforma de comercio electrónico diseñada para visibilizar y conectar a artesanos, productores y comunidades indígenas y afrocolombianas de la cuenca amazónica con compradores conscientes a nivel local, nacional e internacional.
            </p>
        </section>

        <!-- Section 2: Datos Recopilados -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">badge</span>
                2. Datos Personales que Recopilamos
            </h2>
            <p class="mb-4">Recopilamos información para garantizar el correcto funcionamiento del marketplace y mejorar tu experiencia:</p>
            <ul class="list-disc pl-6 space-y-3 mb-4">
                <li><strong>Para Compradores:</strong> Nombre, correo electrónico, dirección de envío, teléfono de contacto y datos de facturación al momento de realizar pedidos.</li>
                <li><strong>Para Vendedores / Comunidades:</strong> Datos de la tienda, nombre del representante, ubicación geográfica (municipio, departamento, coordenadas opcionales de la comunidad) e historias culturales del proceso de elaboración de sus productos.</li>
                <li><strong>Comentarios y Soporte:</strong> Cuando dejas comentarios o reseñas de productos, recopilamos los datos que se muestran en el formulario, así como la dirección IP y la cadena del agente de usuario del navegador para ayudar a la detección de spam.</li>
                <li><strong>Multimedia:</strong> Si eres un productor y subes imágenes a la plataforma, recomendamos evitar subir fotos con datos de ubicación incrustados (GPS EXIF) para proteger la privacidad territorial de tu comunidad.</li>
            </ul>
        </section>

        <!-- Section 3: Uso de la Información -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">rule</span>
                3. Finalidad del Tratamiento de Datos
            </h2>
            <p class="mb-4">Tus datos personales se recopilan para las siguientes finalidades exclusivas:</p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>Procesar, despachar y dar seguimiento a las compras que realices en el marketplace.</li>
                <li>Vincular el origen cultural y geográfico de los productos con la historia de sus creadores en la ficha del producto.</li>
                <li>Facilitar la comunicación interna entre compradores y tiendas aliadas (gestión de pedidos, soporte de garantías).</li>
                <li>Mejorar la plataforma mediante análisis comerciales anónimos impulsados por herramientas de análisis técnico.</li>
            </ul>
        </section>

        <!-- Section 4: Cookies -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">cookie</span>
                4. Política de Cookies
            </h2>
            <p class="mb-4">Utilizamos cookies técnicas y funcionales esenciales para la navegación:</p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>Si dejas un comentario o reseña, puedes elegir guardar tu nombre y correo electrónico en cookies. Esto es para tu comodidad, de modo que no tengas que volver a rellenar tus datos en futuras interacciones. Estas cookies duran un año.</li>
                <li>Si tienes una cuenta de cliente o vendedor e inicias sesión, instalamos cookies temporales para verificar si tu navegador acepta cookies (se eliminan al cerrar el navegador) y para recordar tus datos de inicio de sesión y visualización de pantalla (duran entre dos días y un año).</li>
                <li>Utilizamos cookies del sistema WooCommerce para mantener los productos en tu carrito de compras mientras navegas por la tienda.</li>
            </ul>
        </section>

        <!-- Section 5: Compartir Datos -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">share</span>
                5. Compartición de Información con Terceros
            </h2>
            <p class="mb-4">
                Tus datos de entrega (nombre, dirección y teléfono) serán compartidos estrictamente con las empresas de logística aliadas y con el vendedor directo (artesano o comunidad encargada del despacho) con el único fin de hacer llegar tu pedido.
            </p>
            <p>
                No vendemos, alquilamos ni comercializamos tus datos personales con empresas publicitarias de terceros bajo ninguna circunstancia.
            </p>
        </section>

        <!-- Section 6: Tus Derechos -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-green-800 dark:text-green-400 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined align-middle">admin_panel_settings</span>
                6. Tus Derechos como Titular (Habeas Data)
            </h2>
            <p class="mb-4">
                De acuerdo con la <strong>Ley 1581 de 2012</strong> de la legislación colombiana, tienes derecho a:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>Conocer, actualizar y rectificar tus datos personales recolectados en la plataforma.</li>
                <li>Solicitar la eliminación de tus datos personales cuando consideres que no están siendo tratados de acuerdo con los principios legales.</li>
                <li>Revocar la autorización otorgada para el tratamiento de tus datos en cualquier momento.</li>
            </ul>
        </section>

        <!-- Contact Box -->
        <div class="mt-14 p-8 bg-white dark:bg-slate-900 rounded-2xl text-center border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-lg font-bold text-green-900 dark:text-slate-100 mb-2">¿Deseas ejercer tus derechos de Habeas Data?</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 max-w-lg mx-auto">
                Contáctanos a nuestro canal oficial de privacidad y te responderemos en un plazo máximo de 10 días hábiles de acuerdo con los términos de ley.
            </p>
            <a href="mailto:privacidad@amazoniamarket.com" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-8 py-3 rounded-full transition shadow-md hover:shadow-lg">
                <span class="material-symbols-outlined text-[18px]">security</span>
                XXXXXXXX@amazoniamarket.com
            </a>
        </div>

    </div>

</main>

<?php
get_footer();
