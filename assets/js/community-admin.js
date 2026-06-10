/* Community Admin Panel — Amazonia Theme */
(function ($) {
  'use strict';

  const { ajaxUrl, nonce, i18n } = window.amazoniaCommunityAdmin || {};

  // ─── Upload helper ───────────────────────────────────────────────────────────
  function uploadFile(file, onSuccess, onError) {
    var fd = new FormData();
    fd.append('action', 'amazonia_upload_image');
    fd.append('nonce', nonce);
    fd.append('file', file);
    $.ajax({ url: ajaxUrl, type: 'POST', data: fd, processData: false, contentType: false })
      .done(function (res) {
        if (res.success) onSuccess(res.data);
        else onError(res.data && res.data.message ? res.data.message : 'Error al subir.');
      })
      .fail(function () { onError('Error de conexión.'); });
  }

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

  // ─── Toggle: abrir/cerrar panel de edición ──────────────────
  $(document).on('click keydown', '.ca-edit-toggle', function (e) {
    if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
    e.preventDefault();
    const $body    = $(this).closest('.ca-edit-card').find('.ca-edit-body');
    const $chevron = $(this).find('.ca-edit-chevron');
    const open     = $body.is(':visible');
    $body.slideToggle(220);
    $chevron.css('transform', open ? 'rotate(0deg)' : 'rotate(180deg)');
    $(this).attr('aria-expanded', String(!open));
  });

  // ─── Media uploader: LOGO ────────────────────────────────────
  $('#ca-logo-file').on('change', function () {
    var file = this.files[0];
    if (!file) return;
    var $btn = $('#ca-logo-upload-btn');
    $btn.prop('disabled', true).html('<span class="material-symbols-outlined">hourglass_empty</span> Subiendo...');
    uploadFile(file,
      function (data) {
        $('#ca-logo-url').val(data.url);
        setLogoPreview(data.url);
        $btn.html('<span class="material-symbols-outlined">upload</span> Cambiar imagen').prop('disabled', false);
        $('#ca-logo-remove-btn').show();
      },
      function (msg) {
        alert(msg);
        $btn.html('<span class="material-symbols-outlined">upload</span> Subir imagen').prop('disabled', false);
      }
    );
    this.value = '';
  });

  $('#ca-logo-upload-btn').on('click', function (e) {
    e.preventDefault();
    $('#ca-logo-file').trigger('click');
  });

  $('#ca-logo-remove-btn').on('click', function (e) {
    e.preventDefault();
    $('#ca-logo-url').val('');
    clearLogoPreview();
    $('#ca-logo-upload-btn').html('<span class="material-symbols-outlined">upload</span> Subir imagen');
    $(this).hide();
  });

  function setLogoPreview(url) {
    var $p = $('#ca-logo-preview');
    $p.addClass('has-image').find('.ca-logo-placeholder-icon').remove();
    if ($p.find('img').length) { $p.find('img').attr('src', url); }
    else { $p.html('<img id="ca-logo-img" src="' + url + '" alt="Logo" />'); }
  }

  function clearLogoPreview() {
    $('#ca-logo-preview').removeClass('has-image')
      .html('<span class="material-symbols-outlined ca-logo-placeholder-icon">add_photo_alternate</span>');
  }

  // ─── Media uploader: BANNER ──────────────────────────────────
  $('#ca-banner-file').on('change', function () {
    var file = this.files[0];
    if (!file) return;
    var $btn = $('#ca-banner-upload-btn');
    $btn.prop('disabled', true).html('<span class="material-symbols-outlined">hourglass_empty</span> Subiendo...');
    uploadFile(file,
      function (data) {
        $('#ca-banner-url').val(data.url);
        $('#ca-banner-preview').addClass('has-image')
          .css('background-image', 'url(' + data.url + ')')
          .find('.ca-logo-placeholder-icon').hide();
        $btn.html('<span class="material-symbols-outlined">upload</span> Cambiar portada').prop('disabled', false);
        $('#ca-banner-remove-btn').show();
      },
      function (msg) {
        alert(msg);
        $btn.html('<span class="material-symbols-outlined">upload</span> Subir portada').prop('disabled', false);
      }
    );
    this.value = '';
  });

  $('#ca-banner-upload-btn').on('click', function (e) {
    e.preventDefault();
    $('#ca-banner-file').trigger('click');
  });

  $('#ca-banner-remove-btn').on('click', function (e) {
    e.preventDefault();
    $('#ca-banner-url').val('');
    $('#ca-banner-preview').removeClass('has-image')
      .css('background-image', '')
      .find('.ca-logo-placeholder-icon').show();
    $('#ca-banner-upload-btn').html('<span class="material-symbols-outlined">upload</span> Subir portada');
    $(this).hide();
  });

  // ─── Media uploader: GALERÍA (múltiple) ──────────────────────
  var galleryIds = [];

  try { galleryIds = JSON.parse($('#ca-galeria-ids').val() || '[]'); } catch(e) { galleryIds = []; }
  if (!Array.isArray(galleryIds)) galleryIds = [];

  $('#ca-gallery-file').on('change', function () {
    var files   = Array.from(this.files);
    if (!files.length) return;
    var $btn    = $('#ca-gallery-add-btn');
    var pending = files.length;
    var errors  = [];
    $btn.prop('disabled', true).html('<span class="material-symbols-outlined">hourglass_empty</span> Subiendo...');

    files.forEach(function (file) {
      uploadFile(file,
        function (data) {
          if (galleryIds.indexOf(data.id) === -1) {
            galleryIds.push(data.id);
            addGalleryThumb(data.id, data.thumbnail_url || data.url);
            updateGaleriaInput();
          }
          if (--pending === 0) doneGallery($btn, errors);
        },
        function (msg) {
          errors.push(file.name + ': ' + msg);
          if (--pending === 0) doneGallery($btn, errors);
        }
      );
    });
    this.value = '';
  });

  function doneGallery($btn, errors) {
    $btn.prop('disabled', false).html('<span class="material-symbols-outlined">add_photo_alternate</span> Añadir imágenes');
    if (errors.length) alert(errors.join('\n'));
  }

  $('#ca-gallery-add-btn').on('click', function (e) {
    e.preventDefault();
    $('#ca-gallery-file').trigger('click');
  });

  function addGalleryThumb(id, thumbUrl) {
    $('#ca-gallery-grid').append(
      '<div class="ca-gallery-item" data-id="' + id + '">' +
        '<img src="' + thumbUrl + '" alt="" width="80" height="80" loading="lazy" />' +
        '<button type="button" class="ca-gallery-remove" title="Eliminar">' +
          '<span class="material-symbols-outlined">close</span>' +
        '</button>' +
      '</div>'
    );
  }

  $(document).on('click', '.ca-gallery-remove', function () {
    var id = parseInt($(this).closest('.ca-gallery-item').data('id'), 10);
    galleryIds = galleryIds.filter(function (i) { return i !== id; });
    $(this).closest('.ca-gallery-item').remove();
    updateGaleriaInput();
  });

  function updateGaleriaInput() {
    $('#ca-galeria-ids').val(JSON.stringify(galleryIds));
  }

  // ─── Video: preview embed ─────────────────────────────────────
  function getEmbedUrl(url) {
    var yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (yt) return 'https://www.youtube.com/embed/' + yt[1] + '?rel=0&modestbranding=1';
    var vm = url.match(/vimeo\.com\/(\d+)/);
    if (vm) return 'https://player.vimeo.com/video/' + vm[1];
    return null;
  }

  $('#ca-video-url').on('input', function () {
    var embedUrl = getEmbedUrl($(this).val().trim());
    var $preview = $('#ca-video-preview');
    if (embedUrl) {
      $preview.find('iframe').attr('src', embedUrl);
      $preview.show();
    } else {
      $preview.find('iframe').attr('src', '');
      $preview.hide();
    }
  });

  // ─── Valores dinámicos ────────────────────────────────────────
  var valorIconsData = {};
  try { valorIconsData = JSON.parse($('#ca-valor-icons-data').text() || '{}'); } catch(e) {}

  function buildIconOptions(selected) {
    return Object.keys(valorIconsData).map(function (icon) {
      return '<option value="' + icon + '"' + (icon === selected ? ' selected' : '') + '>' +
        icon + ' — ' + valorIconsData[icon] + '</option>';
    }).join('');
  }

  function addValorRow(icono, texto) {
    if ($('#ca-valores-list .ca-valor-row').length >= 4) return;
    var $row = $(
      '<div class="ca-valor-row">' +
        '<span class="material-symbols-outlined ca-valor-icon-preview">' + (icono || 'eco') + '</span>' +
        '<select class="ca-icon-select">' + buildIconOptions(icono || 'eco') + '</select>' +
        '<input type="text" class="ca-valor-texto" placeholder="Ej: Sostenibilidad" value="' + (texto || '') + '" />' +
        '<button type="button" class="ca-valor-remove" title="Eliminar"><span class="material-symbols-outlined">close</span></button>' +
      '</div>'
    );
    $('#ca-valores-list').append($row);
    toggleValorAddBtn();
  }

  $(document).on('change', '.ca-icon-select', function () {
    $(this).prev('.ca-valor-icon-preview').text($(this).val());
  });

  $(document).on('click', '.ca-valor-remove', function () {
    $(this).closest('.ca-valor-row').remove();
    toggleValorAddBtn();
  });

  $('#ca-valor-add-btn').on('click', function () {
    addValorRow('eco', '');
  });

  function toggleValorAddBtn() {
    var count = $('#ca-valores-list .ca-valor-row').length;
    $('#ca-valor-add-btn').prop('disabled', count >= 4);
  }

  function serializeValores() {
    var valores = [];
    $('#ca-valores-list .ca-valor-row').each(function () {
      var icono = $(this).find('.ca-icon-select').val();
      var texto = $(this).find('.ca-valor-texto').val().trim();
      if (icono && texto) valores.push({ icono: icono, texto: texto });
    });
    $('#ca-valores-json').val(JSON.stringify(valores));
  }

  // ─── Guardar información de la comunidad ─────────────────────
  $('#ca-edit-form').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn  = $form.find('.ca-btn');
    const $fb   = $('#ca-edit-feedback');

    const nombre = $form.find('[name="nombre"]').val().trim();
    if (!nombre) {
      $fb.removeClass('success').addClass('error')
        .text('El nombre de la comunidad es obligatorio.').show();
      return;
    }

    serializeValores(); // sincronizar antes del POST

    $btn.prop('disabled', true).text(i18n.saving);
    $fb.hide().removeClass('success error');

    $.post(ajaxUrl, {
      action:          'amazonia_save_community_info',
      nonce:           nonce,
      nombre:          nombre,
      descripcion:     $form.find('[name="descripcion"]').val(),
      historia:        $form.find('[name="historia"]').val(),
      pais:            $form.find('[name="pais"]').val(),
      departamento:    $form.find('[name="departamento"]').val(),
      municipio:       $form.find('[name="municipio"]').val(),
      categoria:       $form.find('[name="categoria"]').val(),
      logo_url:        $form.find('[name="logo_url"]').val(),
      banner_url:      $form.find('[name="banner_url"]').val(),
      galeria_ids:     $form.find('[name="galeria_ids"]').val(),
      video_url:       $form.find('[name="video_url"]').val(),
      fundacion:       $form.find('[name="fundacion"]').val(),
      num_familias:    $form.find('[name="num_familias"]').val(),
      valores:         $form.find('[name="valores"]').val(),
      instagram:       $form.find('[name="instagram"]').val(),
      facebook:        $form.find('[name="facebook"]').val(),
      certificaciones: $form.find('[name="certificaciones"]').val(),
    })
    .done(function (res) {
      if (res.success) {
        $fb.addClass('success').text(res.data.message).show();
        // Actualizar nombre en el hero sin recargar
        if (res.data.nombre) {
          $('.ca-hero-info h1').text(res.data.nombre);
        }
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
