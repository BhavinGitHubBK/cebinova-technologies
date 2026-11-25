import './bootstrap';
import { initNavbar } from './navbar';
import { initAnimations } from './animations';
import { initContactForm } from './contact-form';
import { initPackageTabs, initPackageFields } from './packages';
import { initProblemPicker } from './problem-picker';
import { initWhatsappFloat } from './whatsapp-float';
import { initLiveChat } from './live-chat';
import { initCareerApplications } from './career-applications';

const initializeApp = () => {
    initNavbar();
    initAnimations();
    initContactForm();
    initPackageTabs();
    initPackageFields();
    initProblemPicker();
    initWhatsappFloat();
    initLiveChat();
    initCareerApplications();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp, { once: true });
} else {
    initializeApp();
}
