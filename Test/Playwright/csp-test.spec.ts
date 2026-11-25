import {PaymentMethod, PlaceOrderButton, ShippingMethod, SuccessPage} from '@loki-checkout/checkout-objects';
import {test, expect} from '@loki/test';
import merge from '@loki/util/merge';
import config, {themes} from './config/config';
import {
    collectCspViolations,
    getCspHeader,
    getCspViolations,
    getDirective,
    openPage,
    setupThemedCheckout,
    trackPageErrors,
} from './helpers/csp';

test.describe('LokiCheckout_Csp test', () => {
    for (const theme of themes) {
        test(`should enforce a restrictive script-src on the checkout (${theme.name})`, async ({page, context}) => {
            const getPageErrors = trackPageErrors(page);
            await setupThemedCheckout(page, context, config, theme);
            const response = await openPage(page, '/checkout/');

            const reportOnly = await getCspHeader(response, 'Content-Security-Policy-Report-Only');
            expect(getDirective(reportOnly, 'script-src'), 'Report-only script-src').toBe('');

            const scriptSrc = getDirective(await getCspHeader(response, 'Content-Security-Policy'), 'script-src');
            expect(scriptSrc, 'Enforced script-src').not.toBe('');
            expect(scriptSrc).not.toContain(`'unsafe-inline'`);
            expect(scriptSrc).not.toContain(`'unsafe-eval'`);
            expect(scriptSrc).toMatch(/'nonce-[^']+'/);

            expect(getPageErrors(), 'Uncaught page errors').toEqual([]);
        });

        test(`should keep CSP report-only outside the checkout (${theme.name})`, async ({page, context}) => {
            const getPageErrors = trackPageErrors(page);
            await setupThemedCheckout(page, context, config, theme);
            const response = await openPage(page, '/checkout/cart/');

            const enforced = await getCspHeader(response, 'Content-Security-Policy');
            expect(getDirective(enforced, 'script-src'), 'Enforced script-src on the cart').toBe('');

            expect(getPageErrors(), 'Uncaught page errors').toEqual([]);
        });

        test(`should load Alpine without CSP violations (${theme.name})`, async ({page, context}) => {
            const getPageErrors = trackPageErrors(page);
            await collectCspViolations(page);
            await setupThemedCheckout(page, context, config, theme);
            await openPage(page, '/checkout/');

            await expect.poll(() => page.evaluate(() => typeof (window as any).Alpine)).toBe('object');

            const alpineCspScripts = page.locator('script[src*="LokiCheckout_Csp/js/alpine-csp.min.js"]');
            if (theme.theme.startsWith('Hyva/')) {
                await expect(alpineCspScripts).toHaveCount(0);
            } else {
                await expect(alpineCspScripts).toHaveCount(1);
                expect(await alpineCspScripts.getAttribute('crossorigin'), 'crossorigin attribute').toBeNull();
            }

            expect(await getCspViolations(page), 'CSP violations').toEqual([]);

            expect(getPageErrors(), 'Uncaught page errors').toEqual([]);
        });

        test(`should place an order without CSP violations (${theme.name})`, async ({page, context}) => {
            const getPageErrors = trackPageErrors(page);
            await collectCspViolations(page);
            await setupThemedCheckout(page, context, config, theme);

            await new ShippingMethod(page, 'flatrate_flatrate').select();
            await new PaymentMethod(page, 'checkmo').select();
            expect(await getCspViolations(page), 'CSP violations on the checkout').toEqual([]);

            await new PlaceOrderButton(page).click();
            await new SuccessPage(page).expectToBeLoaded();

            expect(getPageErrors(), 'Uncaught page errors').toEqual([]);
        });
    }

    test('should load the Luma checkout of a logged-in customer without page errors', async ({page, context}) => {
        const getPageErrors = trackPageErrors(page);
        await collectCspViolations(page);
        const loggedInConfig = merge(config, {customer: {login: true}});
        await setupThemedCheckout(page, context, loggedInConfig, themes[0]);
        await openPage(page, '/checkout/');

        await expect(page.locator('.page-header .authorization-link')).toContainText('Sign Out');
        await expect(page.locator('[data-bind]')).toHaveCount(0);

        expect(await getCspViolations(page), 'CSP violations').toEqual([]);
        expect(getPageErrors(), 'Uncaught page errors').toEqual([]);
    });
});
