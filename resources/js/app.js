import '../css/app.css';
import './bootstrap';
import Aura from '../css/presets/aura'

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from "primevue/config";
import ToastService from 'primevue/toastservice';
import { i18nVue } from 'laravel-vue-i18n';
import ConfirmationService from 'primevue/confirmationservice';

const appName = import.meta.env.VITE_APP_NAME || 'Volta Asia Admin';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(i18nVue, {
                resolve: async lang => {
                    const langs = import.meta.glob('../../lang/*.json');
                    if (typeof langs[`../../lang/${lang}.json`] == "undefined") return; //Temporary workaround
                    return await langs[`../../lang/${lang}.json`]();
                }
            })
            .use(PrimeVue, {
                unstyled: true,
                pt: Aura
            })
            .use(ToastService)
            .use(ConfirmationService)

        app.mount(el);
    },
    progress: {
        color: '#cca05a',
    },
});
