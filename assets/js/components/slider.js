import Swiper from 'swiper';
import { Autoplay, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

new Swiper('.slider__list', {
	modules: [Autoplay, Pagination],
	loop: true,
	spaceBetween: 0,
	slidesPerView: 1,
	autoHeight: true,
	autoplay: {
		delay: 5000,
		disableOnInteraction: false
	},
	pagination: {
	 	el: '.swiper-pagination-slider',
	 	clickable: true
	},
});