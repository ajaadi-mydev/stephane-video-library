(() => {
	if (typeof wp !== 'undefined' && wp.media) {
		document.addEventListener('click', (event) => {
			const selectButton = event.target.closest('.stvl-select-image');
			const removeButton = event.target.closest('.stvl-remove-image');

			if (removeButton) {
				const field = document.getElementById('stvl_video_custom_thumbnail');
				const preview = document.querySelector('.stvl-image-preview');
				if (field) {
					field.value = '';
				}
				if (preview) {
					preview.innerHTML = '';
				}
				return;
			}

			if (!selectButton) {
				return;
			}

			const field = document.getElementById('stvl_video_custom_thumbnail');
			const preview = document.querySelector('.stvl-image-preview');
			const frame = wp.media({
				title: 'Choose thumbnail',
				button: { text: 'Use image' },
				multiple: false,
			});

			frame.on('select', () => {
				const attachment = frame.state().get('selection').first().toJSON();
				if (field) {
					field.value = attachment.id;
				}
				if (preview) {
					preview.innerHTML = `<img src="${attachment.url}" alt="" />`;
				}
			});

			frame.open();
		});
	}

	const urlField = document.getElementById('stvl_video_url');
	const providerField = document.getElementById('stvl_video_provider');
	const idField = document.getElementById('stvl_video_id');

	if (!urlField || !providerField || !idField) {
		return;
	}

	const detect = () => {
		const url = urlField.value.trim();
		if (!url) {
			return;
		}

		if (/youtube\.com|youtu\.be/i.test(url)) {
			providerField.value = 'youtube';
			const match = url.match(/(?:v=|youtu\.be\/|embed\/|shorts\/)([A-Za-z0-9_-]{11})/);
			if (match) {
				idField.value = match[1];
			}
		} else if (/vimeo\.com/i.test(url)) {
			providerField.value = 'vimeo';
			const match = url.match(/vimeo\.com\/(?:video\/)?([0-9]+)/);
			if (match) {
				idField.value = match[1];
			}
		}
	};

	urlField.addEventListener('change', detect);
})();
