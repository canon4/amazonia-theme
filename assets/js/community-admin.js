/* Community Admin Panel — Amazonia Theme */
(function ($) {
  'use strict';

  const { ajaxUrl, nonce, i18n } = window.amazoniaCommunityAdmin || {};

  // ─── Crear nueva tienda ──────────────────────────────────────
  $('#ca-create-form').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn  = $form.find('.ca-btn');
    const $fb   = $('#ca-create-feedback');

    $btn.prop('disabled', true).text(i18n.creating);
    $fb.hide().removeClass('success error');

    $.post(ajaxUrl, {
      action:     'amazonia_create_vendor',
      nonce:      nonce,
      store_name: $form.find('[name="store_name"]').val(),
      email:      $form.find('[name="email"]').val(),
      first_name: $form.find('[name="first_name"]').val(),
      last_name:  $form.find('[name="last_name"]').val(),
    })
    .done(function (res) {
      if (res.success) {
        $fb.addClass('success').text(res.data.message).show();
        $form[0].reset();
        // Agregar la tienda nueva a la lista sin recargar
        addStoreToList(res.data);
      } else {
        $fb.addClass('error').text(res.data.message).show();
      }
    })
    .fail(function () {
      $fb.addClass('error').text('Error de conexión. Intenta de nuevo.').show();
    })
    .always(function () {
      $btn.prop('disabled', false).text($btn.data('original-text'));
    });
  });

  // Guardar texto original del botón
  $('#ca-create-form .ca-btn').each(function () {
    $(this).data('original-text', $(this).text());
  });

  // ─── Buscar vendedores para vincular ────────────────────────
  let searchTimeout;
  $('#ca-search-input').on('input', function () {
    clearTimeout(searchTimeout);
    const term  = $(this).val().trim();
    const $res  = $('#ca-search-results');

    if (term.length < 2) { $res.hide().empty(); return; }

    searchTimeout = setTimeout(function () {
      $.post(ajaxUrl, {
        action: 'amazonia_search_vendors',
        nonce:  nonce,
        term:   term,
      })
      .done(function (res) {
        $res.empty();
        if (res.success && res.data.length) {
          res.data.forEach(function (vendor) {
            const taken  = vendor.community ? `<span class="ca-search-result-taken">Ya en: ${vendor.community}</span>` : '';
            const $item  = $(`
              <div class="ca-search-result-item">
                <div class="ca-search-result-info">
                  <div class="ca-search-result-store">${vendor.store}</div>
                  <div class="ca-search-result-email">${vendor.email}</div>
                  ${taken}
                </div>
                <button class="ca-link-btn" data-id="${vendor.id}" ${vendor.community ? 'disabled' : ''}>
                  Vincular
                </button>
              </div>
            `);
            $res.append($item);
          });
          $res.show();
        } else {
          $res.html('<div style="padding:.75rem 1rem;font-size:.875rem;color:#94a3b8;">Sin resultados.</div>').show();
        }
      });
    }, 350);
  });

  // ─── Vincular vendedor existente ─────────────────────────────
  $(document).on('click', '.ca-link-btn', function () {
    if (!confirm(i18n.confirm_link)) return;
    const $btn    = $(this);
    const user_id = $btn.data('id');
    const $fb     = $('#ca-link-feedback');

    $btn.prop('disabled', true).text(i18n.linking);
    $fb.hide().removeClass('success error');

    $.post(ajaxUrl, {
      action:  'amazonia_link_vendor',
      nonce:   nonce,
      user_id: user_id,
    })
    .done(function (res) {
      if (res.success) {
        $fb.addClass('success').text(res.data.message).show();
        $btn.closest('.ca-search-result-item').fadeOut(300);
        // Recargar la lista de tiendas en 800ms
        setTimeout(function () { location.reload(); }, 1200);
      } else {
        $fb.addClass('error').text(res.data.message).show();
        $btn.prop('disabled', false).text('Vincular');
      }
    });
  });

  // ─── Agregar tienda a la lista sin recargar ──────────────────
  function addStoreToList(data) {
    const $list  = $('#ca-store-list');
    const $empty = $list.find('.ca-empty');
    if ($empty.length) $empty.remove();

    $list.append(`
      <li class="ca-store-item">
        <div class="ca-store-avatar-placeholder">
          <span class="material-symbols-outlined">storefront</span>
        </div>
        <div class="ca-store-info">
          <div class="ca-store-name">${data.store}</div>
          <div class="ca-store-email">${data.email}</div>
        </div>
      </li>
    `);

    // Actualizar contador de tiendas
    const $count = $('#ca-metric-stores');
    if ($count.length) $count.text(parseInt($count.text() || 0) + 1);
  }

})(jQuery);
