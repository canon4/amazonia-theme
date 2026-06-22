/* Product Storytelling Fields — Amazonia Theme */
(function ($) {
  'use strict';

  const cfg = window.amazoniaStorytelling || {};

  // ─── Upload helper (mismo patrón que community-admin.js) ─────────────────────
  function uploadFile(file, onSuccess, onError) {
    var fd = new FormData();
    fd.append('action', 'amazonia_upload_image');
    fd.append('nonce', cfg.nonce);
    fd.append('file', file);
    $.ajax({ url: cfg.ajaxUrl, type: 'POST', data: fd, processData: false, contentType: false })
      .done(function (res) {
        if (res.success) onSuccess(res.data);
        else onError(res.data && res.data.message ? res.data.message : 'Error al subir.');
      })
      .fail(function () { onError('Error de conexión.'); });
  }

  // ─── Manejar cada campo de imagen ────────────────────────────────────────────
  $(document).on('change', '.amz-story-file', function () {
    var $input   = $(this);
    var file     = $input[0].files[0];
    var $wrapper = $input.closest('.amz-story-field');
    var $hidden  = $wrapper.find('.amz-story-url');
    var $preview = $wrapper.find('.amz-story-preview');
    var $remove  = $wrapper.find('.amz-story-remove');
    var $status  = $wrapper.find('.amz-story-status');

    if (!file) return;

    $status.text('Subiendo...').show();
    uploadFile(file, function (data) {
      $hidden.val(data.url);
      $preview.attr('src', data.url).show();
      $remove.show();
      $status.hide();
    }, function (msg) {
      $status.text(msg).show();
    });
  });

  $(document).on('click', '.amz-story-remove', function () {
    var $wrapper = $(this).closest('.amz-story-field');
    $wrapper.find('.amz-story-url').val('');
    $wrapper.find('.amz-story-preview').attr('src', '').hide();
    $wrapper.find('.amz-story-file').val('');
    $(this).hide();
  });

}(jQuery));
