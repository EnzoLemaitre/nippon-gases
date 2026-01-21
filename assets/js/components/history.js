import { tns } from "tiny-slider/src/tiny-slider";
import "tiny-slider/dist/tiny-slider.css";

tns({
	container: '.history__slider',
	items: 1,
	axis: "vertical",
	swipeAngle: false,
	speed: 400,
	autoHeight: true,
	lazyload: true,
	nav: true,
	controls: false,
	loop: false,
	autoplay: true,
	autoplayButtonOutput: false,
});

const customPagination = () => {
	const slides = document.querySelector('.history__slider').children;
	const pagination = document.querySelector('.tns-nav');
	for (let i = 0; i < pagination.children.length; i++) {
		pagination.children[i].innerHTML = slides[i].getAttribute('data-label');
	}
}
customPagination();