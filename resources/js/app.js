import './bootstrap';
import Alpine from 'alpinejs';
import { register } from 'swiper/element/bundle';

// Register Swiper WebComponents
register();

window.Alpine = Alpine;
Alpine.start();
