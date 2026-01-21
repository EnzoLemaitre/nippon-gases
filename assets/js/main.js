const menuHover = () => {
	const menuItems = document.querySelectorAll('.menu-item-has-children');

	menuItems.forEach((item) => {
		// Remove href from parent menu items
		const link = item.querySelector('a');
		if (link) {link.removeAttribute('href');}

		item.addEventListener('click', () => {
			// If the clicked item is already hovered, remove the class and return
			if (item.classList.contains('hovered')) {
				item.classList.remove('hovered');
				return;
			}

			// Remove 'hovered' class from all menu items
			menuItems.forEach((el) => el.classList.remove('hovered'));

			item.classList.add('hovered');
		});

		const subMenu = item.querySelector('.sub-menu');
		if (subMenu) {
			subMenu.addEventListener('mouseleave', () => {
				item.classList.remove('hovered');
			});
		}
	});
}
menuHover();

/**
 * Responsive menu
 */
const menuResponsive = function () {
    const button = document.getElementById('button-menu');
    const buttonClose = document.getElementById('close-button');
    const menu = document.getElementById('menu-responsive');
	const body = document.body;

    function toggleMenu(event) {
        event.stopPropagation();
        menu.classList.toggle('open-menu');
		body.classList.toggle('no-scroll', menu.classList.contains('open-menu'));
    }	

    function closeMenu(event) {
        if (!menu.contains(event.target) && event.target !== button) {
            menu.classList.remove('open-menu');
			body.classList.remove('no-scroll');
        }
    }

	if (button) {
		button.addEventListener('click', toggleMenu);
		buttonClose.addEventListener('click', (event) => {
			toggleMenu(event);
			// Remove all hovered classes from menu items
			const menuItems = document.querySelectorAll('.menu-item-has-children');
			menuItems.forEach((el) => el.classList.remove('hovered'));
		});
		// document.addEventListener('click', closeMenu);
		menu.addEventListener('click', (event) => event.stopPropagation());
	}
}
menuResponsive();

function reportWindowSize() {
    let menu = document.getElementById('menu-responsive');
	const width = 1000;
	if (menu) {
		if (menu.classList.contains('open-menu') && window.innerWidth >= width) {
			menu.classList.remove('open-menu');
			body.classList.remove('no-scroll');
		}
	}
}
window.onresize = reportWindowSize;