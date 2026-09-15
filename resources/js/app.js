import './bootstrap';
import { initNavbar } from './navbar';
import { initAnimations } from './animations';
import { initContactForm } from './contact-form';
import { initPackageTabs, initPackageFields } from './packages';

document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initAnimations();
    initContactForm();
    initPackageTabs();
    initPackageFields();
});
