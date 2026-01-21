import Swiper from 'swiper';
import { Autoplay, Pagination, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';

new Swiper('.supply-gases__list', {
	modules: [Autoplay, Pagination, Navigation],
	// loop: true,
	spaceBetween: 15,
	slidesPerView: 1,
	// autoplay: {
	// 	delay: 5000,
	// 	disableOnInteraction: false
	// },
	pagination: {
	 	el: '.swiper-pagination-last-solutions',
	 	clickable: true
	},
	navigation: {
		nextEl: '.swiper-button-next',
		prevEl: '.swiper-button-prev',
	},
	breakpoints: {
		400: { slidesPerView: 2 },
		620: { slidesPerView: 3 },
		900: { slidesPerView: 4 },
		1450: { slidesPerView: 5 }
	}
});