/**
 * Change content button theme dark/light
 */
const themeContentButton = (theme, button) => {
	button.className = '';
	button.classList.add(theme);

	if (theme === 'dark') {
		button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19.91 19.91"><path d="M19.72 12.42a.653.653 0 0 0-.69-.15c-4.63 1.65-9.73-.76-11.38-5.4a8.897 8.897 0 0 1 0-5.98.669.669 0 0 0-.4-.85.664.664 0 0 0-.44 0c-1.43.5-2.73 1.32-3.8 2.4-4 4-4 10.48 0 14.48s10.48 4 14.48 0a9.992 9.992 0 0 0 2.4-3.8c.09-.24.02-.51-.16-.69Zm-3.19 3.56c-3.49 3.47-9.13 3.46-12.6-.03s-3.46-9.13.03-12.6c.6-.6 1.29-1.11 2.04-1.52-.68 3.36.37 6.84 2.79 9.28a10.28 10.28 0 0 0 9.28 2.78c-.41.76-.93 1.46-1.54 2.08Z"/></svg>';
		button.setAttribute('aria-label', themeLabels?.toLight || 'Switch to light mode');
	} else {
		button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20.25 20.25"><path d="M10.12.75v1.88m0 14.99v1.88m-5.3-4.07-1.33 1.33m16.01-6.64h-1.88m-15 0H.75m16-6.62-1.33 1.33m-5.3.92c2.42 0 4.37 1.96 4.37 4.37 0 2.42-1.96 4.37-4.37 4.37-2.42 0-4.37-1.96-4.37-4.37 0-2.42 1.96-4.37 4.37-4.37Zm-5.3-.93L3.49 3.49m13.26 13.26-1.33-1.33" style="fill:none;stroke-linecap:round;stroke-width:1.5px"/></svg>';
		button.setAttribute('aria-label', themeLabels?.toDark || 'Switch to dark mode');
	}
}

/**
 * Toggle theme dark/light
 */
const themeToggle = () => {
	const button = document.getElementById('theme-toggle');
	if (button) {
		button.addEventListener('click', () => {
			const currentTheme = document.documentElement.getAttribute('data-theme');
			const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

			document.documentElement.setAttribute('data-theme', newTheme);
			localStorage.setItem('theme', newTheme);
			themeContentButton(newTheme, button);

			button.blur && button.blur();
		});

		const saved = localStorage.getItem('theme');
		themeContentButton(saved, button);
	}
}
themeToggle();
