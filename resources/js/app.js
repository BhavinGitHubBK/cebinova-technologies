import './bootstrap';
import { initNavbar } from './navbar';
import { initAnimations } from './animations';
import { initContactForm } from './contact-form';
import { initPackageTabs, initPackageFields } from './packages';
import { initProblemPicker } from './problem-picker';
import { initWhatsappFloat } from './whatsapp-float';
import { initLiveChat } from './live-chat';

document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initAnimations();
    initContactForm();
    initPackageTabs();
    initPackageFields();
    initProblemPicker();
    initWhatsappFloat();
    initLiveChat();
});
