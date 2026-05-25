(() => {
	document.addEventListener('keydown', (event) => {
		if (event.key !== 'Tab') {
			return;
		}

		const modal = document.querySelector('.stvl-modal:not([hidden])');
		if (!modal) {
			return;
		}

		const focusables = Array.from(
			modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')
		).filter((element) => !element.hasAttribute('disabled'));

		if (!focusables.length) {
			return;
		}

		const first = focusables[0];
		const last = focusables[focusables.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	});
})();
