import './bootstrap';
import { initNavbar } from './navbar';
import { initAnimations } from './animations';
import { initContactForm } from './contact-form';
import { initPackageTabs, initPackageFields } from './packages';
import { initProblemPicker } from './problem-picker';
import { initWhatsappFloat } from './whatsapp-float';
import { initLiveChat } from './live-chat';

const initializeApp = () => {
    initNavbar();
    initAnimations();
    initContactForm();
    initPackageTabs();
    initPackageFields();
    initProblemPicker();
    initWhatsappFloat();
    initLiveChat();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp, { once: true });
} else {
    initializeApp();
}
