import Swiper from 'swiper';
import { Autoplay, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

new Swiper('.last-gases__list', {
	modules: [Autoplay, Pagination],
	loop: true,
	spaceBetween: 15,
	slidesPerView: 1,
	autoplay: {
		delay: 5000,
		disableOnInteraction: false
	},
	pagination: {
	 	el: '.swiper-pagination-last-gases',
	 	clickable: true
	},
	breakpoints: {
		400: { slidesPerView: 2 },
		620: { slidesPerView: 3 },
		900: { slidesPerView: 4 },
		1450: { slidesPerView: 5 }
	}
});