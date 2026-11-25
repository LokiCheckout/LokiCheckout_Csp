import {setupCheckout} from '@loki/setup-checkout';
import {expect, test} from '@loki/test';
import type {BrowserContext, Page, Response} from '@playwright/test';
import {Theme, withTheme} from '../config/config';

/**
 * setupCheckout() does not report theme errors, so check that the requested theme is actually active: Otherwise a
 * missing Hyvä theme would silently run the Hyvä tests against Luma
 */
export async function setupThemedCheckout(page: Page, context: BrowserContext, config: any, theme: Theme) {
    const token = encodeURIComponent(process.env.TEST_TOKEN ?? '');
    const response = await page.request.post('/loki_functional_tests/index/configure?token=' + token, {
        form: {config: JSON.stringify(withTheme(config, theme))},
    });

    const data = await response.json();
    const errors: string[] = data.result?.theme?.errors ?? [];
    test.skip(errors.includes(`Theme "${theme.theme}" not found`), `Theme "${theme.theme}" is not installed`);
    expect(errors, `Errors while switching to theme "${theme.theme}"`).toEqual([]);

    await setupCheckout(page as any, context, withTheme(config, theme));

    const isHyva = await page.evaluate(() => typeof (window as any).hyva !== 'undefined');
    expect(isHyva, `Theme "${theme.theme}" should be active`).toBe(theme.theme.startsWith('Hyva/'));
}

/**
 * Collect uncaught page errors
 */
export function trackPageErrors(page: Page): () => string[] {
    const errors: string[] = [];
    (page as any).setCatchErrors(false);
    page.on('pageerror', error => errors.push(error.message));

    return () => errors;
}

/**
 * Record CSP violations of all following navigations
 */
export async function collectCspViolations(page: Page) {
    await page.addInitScript(() => {
        const violations: string[] = [];
        (window as any).lokiCheckoutCspViolations = violations;
        document.addEventListener('securitypolicyviolation', event => {
            violations.push([event.effectiveDirective, event.blockedURI, event.sourceFile, event.sample].join(' | '));
        });
    });
}

export async function getCspViolations(page: Page): Promise<string[]> {
    return page.evaluate(() => (window as any).lokiCheckoutCspViolations ?? []);
}

/**
 * Open a page bypassing the full page cache, so the CSP header belongs to this very rendering
 */
export async function openPage(page: Page, path: string): Promise<Response> {
    const response = await page.goto(path + '?loki_csp=' + Date.now());
    expect(response, `Response of ${path}`).not.toBeNull();
    expect(response!.status()).toBe(200);
    return response!;
}

export async function getCspHeader(response: Response, name: string): Promise<string> {
    const headers = await response.headersArray();
    return headers
        .filter(header => header.name.toLowerCase() === name.toLowerCase())
        .map(header => header.value)
        .join(';');
}

export function getDirective(policy: string, name: string): string {
    return policy.split(';')
        .map(directive => directive.trim())
        .filter(directive => directive.startsWith(name + ' '))
        .join(' ');
}
