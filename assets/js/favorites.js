jQuery(document).ready(function ($) {
	const StorageKey = 'amazonia_favorites';
	const $body = $('body');
	let is_logged_in = amazonia_favorites_data.is_logged_in === '1';
	let favoritesArray = [];

	// Initialize favorites array
	if (is_logged_in) {
		favoritesArray = Object.values(amazonia_favorites_data.user_favorites).map(id => parseInt(id, 10));
	} else {
		let storage = localStorage.getItem(StorageKey);
		if (storage) {
			try {
				favoritesArray = JSON.parse(storage).map(id => parseInt(id, 10));
			} catch (e) {
				favoritesArray = [];
			}
		}
	}

	// Function to visually update hearts across the page
	function updateHearts() {
		$('.amazonia-favorite-btn').each(function () {
			let $btn = $(this);
			let id = parseInt($btn.data('product-id'), 10);
			if (favoritesArray.includes(id)) {
				$btn.addClass('text-red-500 is-favorited').removeClass('text-slate-400');
			} else {
				$btn.addClass('text-slate-400').removeClass('text-red-500 is-favorited');
			}
		});
	}

	// Initial render
	updateHearts();

	// Handle clicking a heart button
	$body.on('click', '.amazonia-favorite-btn', function (e) {
		e.preventDefault();
		let $btn = $(this);
		let id = parseInt($btn.data('product-id'), 10);

		if (!id) return;

		// Optimistic UI update
		if (favoritesArray.includes(id)) {
			favoritesArray = favoritesArray.filter(favId => favId !== id);
		} else {
			favoritesArray.push(id);
		}
		
		// If we are on the favorites page grid and removed an item, we visually hide it
		if ($('#amazonia-favorites-grid').length && !favoritesArray.includes(id)) {
			$btn.closest('li, .product-card-wrapper').fadeOut(300, function() {
				$(this).remove();
				checkEmptyGrid();
			});
		}

		updateHearts();

		if (is_logged_in) {
			// AJAX Sync
			$.ajax({
				url: amazonia_favorites_data.ajax_url,
				type: 'POST',
				data: {
					action: 'amazonia_toggle_favorite',
					product_id: id,
					nonce: amazonia_favorites_data.nonce
				},
				success: function (res) {
					// We could handle errors if it fails to sync, but optimistic is fine
				}
			});
		} else {
			// LocalStorage Sync
			localStorage.setItem(StorageKey, JSON.stringify(favoritesArray));
		}
	});

	// If we are on the "My Favorites" custom page, fetch products
	const $favoritesGrid = $('#amazonia-favorites-grid');
	if ($favoritesGrid.length) {
		loadFavoritesGrid();
	}

	function loadFavoritesGrid() {
		// If the user has no favorites, show empty state immediately
		if (favoritesArray.length === 0) {
			$favoritesGrid.html('<div class="col-span-full text-center py-12"><p class="text-slate-500">No tienes artículos favoritos aún.</p></div>');
			return;
		}

		$favoritesGrid.html('<div class="col-span-full text-center py-12"><span class="material-symbols-outlined animate-spin text-4xl text-primary">autorenew</span><p class="text-slate-500 mt-4">Cargando favoritos...</p></div>');

		$.ajax({
			url: amazonia_favorites_data.ajax_url,
			type: 'POST',
			data: {
				action: 'amazonia_get_favorites',
				product_ids: favoritesArray,
				nonce: amazonia_favorites_data.nonce
			},
			success: function (res) {
				if (res.success) {
					$favoritesGrid.html(res.data.html);
					// Re-run update on newly injected HTML
					updateHearts();
				} else {
					$favoritesGrid.html('<div class="col-span-full text-center py-12"><p class="text-red-500">Error al cargar favoritos.</p></div>');
				}
			},
			error: function () {
				$favoritesGrid.html('<div class="col-span-full text-center py-12"><p class="text-red-500">Error de conexión.</p></div>');
			}
		});
	}

	function checkEmptyGrid() {
		if ($favoritesGrid.children().length === 0) {
			$favoritesGrid.html('<div class="col-span-full text-center py-12"><p class="text-slate-500">No tienes artículos favoritos aún.</p></div>');
		}
	}

});
