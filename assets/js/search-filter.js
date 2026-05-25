(() => {
	const galleries = document.querySelectorAll('.stvl-video-gallery');

	galleries.forEach((gallery) => {
		const searchInput = gallery.querySelector('[data-stvl-search]');
		const countNode = gallery.querySelector('[data-stvl-count]');
		const emptyNode = gallery.querySelector('[data-stvl-empty]');
		const cards = Array.from(gallery.querySelectorAll('.stvl-video-card'));
		const filters = Array.from(gallery.querySelectorAll('.stvl-filter'));

		let activeFilter = 'all';

		const refresh = () => {
			const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
			let visible = 0;

			cards.forEach((card) => {
				const haystack = [
					card.dataset.title,
					card.dataset.description,
					card.dataset.category,
					card.dataset.topic,
					card.dataset.source,
				].join(' ');

				const matchesQuery = !query || haystack.includes(query);
				const matchesFilter = activeFilter === 'all' || (card.dataset.category || '').split(' ').includes(activeFilter);
				const show = matchesQuery && matchesFilter;

				card.hidden = !show;
				if (show) {
					visible += 1;
				}
			});

			if (countNode) {
				countNode.textContent = `${visible} ${gallery.dataset.countSuffix || ''}`.trim();
			}

			if (emptyNode) {
				emptyNode.classList.toggle('is-visible', visible === 0);
			}
		};

		if (searchInput) {
			searchInput.addEventListener('input', refresh);
		}

		filters.forEach((filter) => {
			filter.addEventListener('click', () => {
				activeFilter = filter.dataset.filter || 'all';
				filters.forEach((item) => item.classList.toggle('is-active', item === filter));
				refresh();
			});
		});

		refresh();
	});
})();
