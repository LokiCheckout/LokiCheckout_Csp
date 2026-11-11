import coreConfig from '@loki-checkout/config';
import merge from '@loki/util/merge';

export type Theme = {
    name: string;
    theme: string;
};

export const themes: Theme[] = [
    {name: 'Luma', theme: 'Magento/luma'},
    {name: 'Hyvä', theme: 'Hyva/default-csp'},
];

export const withTheme = (config: any, theme: Theme) => merge(config, {theme: theme.theme});

const config = merge(coreConfig, {
    modules: [
        'LokiCheckout_Csp',
    ],
    profile: 'netherlands',
    config: {
        'loki_checkout/general/enable': 1,
        'loki_checkout/general/theme': 'onestep',
        'carriers/flatrate/active': 1,
        'payment/checkmo/active': 1,
    }
});

export default config;
