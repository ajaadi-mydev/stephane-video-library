(() => {
	const createIframe = (embedUrl, title) => {
		if (!embedUrl) {
			return null;
		}

		const iframe = document.createElement('iframe');
		iframe.src = embedUrl;
		iframe.loading = 'lazy';
		iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
		iframe.allowFullscreen = true;
		iframe.title = title || 'Video';
		return iframe;
	};

	const createVideo = (videoUrl) => {
		if (!videoUrl) {
			return null;
		}

		const video = document.createElement('video');
		video.src = videoUrl;
		video.controls = true;
		video.preload = 'metadata';
		return video;
	};

	const closeModal = (modal) => {
		if (!modal) {
			return;
		}

		const frame = modal.querySelector('[data-stvl-modal-frame]');
		modal.hidden = true;
		frame.innerHTML = '';
		document.body.classList.remove('stvl-modal-open');
	};

	document.addEventListener('click', (event) => {
		const opener = event.target.closest('[data-stvl-open-video]');
		const closer = event.target.closest('[data-stvl-close-modal]');

		if (closer) {
			const modal = closer.closest('.stvl-modal');
			closeModal(modal);
			return;
		}

		if (!opener) {
			return;
		}

		const behavior = opener.dataset.behavior || 'modal';
		const embedUrl = opener.dataset.embedUrl || '';
		const videoUrl = opener.dataset.videoUrl || '';
		const actionUrl = opener.dataset.actionUrl || '';
		const title = opener.dataset.title || '';
		const description = opener.dataset.description || '';
		const gallery = opener.closest('.stvl-video-gallery');

		if (behavior === 'new_tab') {
			window.open(actionUrl, '_blank', 'noopener,noreferrer');
			return;
		}

		if (behavior === 'same_tab') {
			window.location.href = actionUrl;
			return;
		}

		if (behavior === 'inline_embed') {
			const card = opener.closest('.stvl-video-card');
			if (!card) {
				return;
			}

			let inline = card.querySelector('.stvl-inline-embed');
			if (inline) {
				inline.remove();
				return;
			}

			inline = document.createElement('div');
			inline.className = 'stvl-inline-embed';
			inline.appendChild(createIframe(embedUrl, title) || createVideo(videoUrl));
			card.appendChild(inline);
			return;
		}

		const modal = gallery ? gallery.querySelector('.stvl-modal') : null;
		if (!modal) {
			return;
		}

		const frame = modal.querySelector('[data-stvl-modal-frame]');
		const titleEl = modal.querySelector('.stvl-modal-title');
		const descriptionEl = modal.querySelector('[data-stvl-modal-description]');

		frame.innerHTML = '';
		titleEl.textContent = title;
		descriptionEl.textContent = description;
		frame.appendChild(createIframe(embedUrl, title) || createVideo(videoUrl));
		modal.hidden = false;
		document.body.classList.add('stvl-modal-open');
	});

	document.addEventListener('keydown', (event) => {
		if (event.key !== 'Escape') {
			return;
		}

		document.querySelectorAll('.stvl-modal:not([hidden])').forEach((modal) => {
			closeModal(modal);
		});
	});
})();
