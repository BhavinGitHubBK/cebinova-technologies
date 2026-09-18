import './bootstrap';
import { initNavbar } from './navbar';
import { initAnimations } from './animations';
import { initContactForm } from './contact-form';
import { initPackageTabs, initPackageFields } from './packages';
import { initWhatsappFloat } from './whatsapp-float';

document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initAnimations();
    initContactForm();
    initPackageTabs();
    initPackageFields();
    initWhatsappFloat();
});
