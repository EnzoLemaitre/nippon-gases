import Swiper from 'swiper';
import { Autoplay, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

new Swiper('.green-for__list', {
	modules: [Autoplay, Pagination],
	loop: true,
	spaceBetween: 50,
	slidesPerView: 1,
	autoHeight: true,
	autoplay: {
		delay: 5000,
		disableOnInteraction: false
	},
	pagination: {
	 	el: '.swiper-pagination-green-for',
	 	clickable: true
	},
	breakpoints: {
		530: { slidesPerView: 1 },
		768: { slidesPerView: 2 },
		1000: { slidesPerView: 3 },
		1450: { slidesPerView: 4 }
	}
});
