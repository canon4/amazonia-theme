<?php
defined( 'ABSPATH' ) || exit;

// ─── Registro de meta fields ──────────────────────────────────────────────────

add_action( 'init', function () {
    foreach ( [ '_storytelling_img_1', '_storytelling_img_2', '_storytelling_img_3' ] as $key ) {
        register_post_meta( 'product', $key, [
            'type'              => 'string',
            'single'            => true,
            'sanitize_callback' => 'esc_url_raw',
            'show_in_rest'      => false,
        ] );
    }
} );

// ─── WP Admin — Meta box ─────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
    add_meta_box(
        'amazonia_storytelling_images',
        'Imágenes de Storytelling',
        'amazonia_render_storytelling_meta_box',
        'product',
        'normal',
        'default'
    );
} );

function amazonia_render_storytelling_meta_box( $post ) {
    wp_nonce_field( 'amazonia_storytelling_save', 'amazonia_storytelling_nonce' );

    $fields = [
        '_storytelling_img_1' => 'Card 1 — La Comunidad',
        '_storytelling_img_2' => 'Card 2 — Tradición & Cultura',
        '_storytelling_img_3' => 'Card 3 — Valores',
    ];

    echo '<style>
        .amz-st-row { display:flex; gap:20px; align-items:flex-start; margin-bottom:20px; }
        .amz-st-preview { width:120px; height:80px; object-fit:cover; border-radius:6px; border:1px solid #ddd; display:block; }
        .amz-st-preview.hidden { display:none; }
        .amz-st-placeholder { width:120px; height:80px; background:#f0f0f0; border-radius:6px; border:1px dashed #ccc; display:flex; align-items:center; justify-content:center; font-size:11px; color:#999; }
        .amz-st-btns { display:flex; gap:8px; margin-top:8px; }
        .amz-st-label { font-weight:600; margin-bottom:6px; display:block; }
    </style>';

    foreach ( $fields as $meta_key => $label ) {
        $url      = get_post_meta( $post->ID, $meta_key, true );
        $input_id = 'amz_' . ltrim( $meta_key, '_' );
        ?>
        <div class="amz-st-row">
            <div>
                <?php if ( $url ) : ?>
                    <img id="<?php echo esc_attr( $input_id . '_preview' ); ?>" src="<?php echo esc_url( $url ); ?>" class="amz-st-preview" />
                <?php else : ?>
                    <img id="<?php echo esc_attr( $input_id . '_preview' ); ?>" src="" class="amz-st-preview hidden" />
                    <div id="<?php echo esc_attr( $input_id . '_placeholder' ); ?>" class="amz-st-placeholder">Sin imagen</div>
                <?php endif; ?>
                <div class="amz-st-btns">
                    <button type="button" class="button" data-target="<?php echo esc_attr( $input_id ); ?>">Seleccionar imagen</button>
                    <button type="button" class="button" data-remove="<?php echo esc_attr( $input_id ); ?>" <?php echo $url ? '' : 'style="display:none"'; ?>>Eliminar</button>
                </div>
            </div>
            <div style="flex:1">
                <span class="amz-st-label"><?php echo esc_html( $label ); ?></span>
                <input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" value="<?php echo esc_url( $url ); ?>" />
                <?php if ( $url ) : ?>
                    <p style="font-size:12px; color:#666; word-break:break-all; margin:0;"><?php echo esc_html( $url ); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    ?>
    <script>
    jQuery(function($){
        $('[data-target]').on('click', function(){
            var inputId = $(this).data('target');
            var frame = wp.media({ title: 'Seleccionar imagen', button: { text: 'Usar esta imagen' }, multiple: false });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                $('#' + inputId).val(att.url);
                $('#' + inputId + '_preview').attr('src', att.url).removeClass('hidden');
                $('#' + inputId + '_placeholder').hide();
                $('[data-remove="' + inputId + '"]').show();
            });
            frame.open();
        });
        $('[data-remove]').on('click', function(){
            var inputId = $(this).data('remove');
            $('#' + inputId).val('');
            $('#' + inputId + '_preview').attr('src','').addClass('hidden');
            $('#' + inputId + '_placeholder').show();
            $(this).hide();
        });
    });
    </script>
    <?php
}

// Guardar desde WP Admin
add_action( 'save_post_product', function ( $post_id ) {
    if ( ! isset( $_POST['amazonia_storytelling_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['amazonia_storytelling_nonce'], 'amazonia_storytelling_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    foreach ( [ '_storytelling_img_1', '_storytelling_img_2', '_storytelling_img_3' ] as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, esc_url_raw( $_POST[ $key ] ) );
        }
    }
} );

// Encolar wp.media solo en la pantalla de edición de producto
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) return;
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'product' ) return;
    wp_enqueue_media();
} );
