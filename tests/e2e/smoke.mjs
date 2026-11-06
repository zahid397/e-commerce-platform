import assert from 'node:assert/strict';
import { mkdir } from 'node:fs/promises';
import { resolve } from 'node:path';
import { chromium } from 'playwright';

const origin = new URL(process.env.E2E_BASE_URL || 'http://127.0.0.1:8000');
assert(['127.0.0.1', 'localhost'].includes(origin.hostname), 'Use an isolated local E2E app.');
const adminEmail = process.env.E2E_ADMIN_EMAIL;
const adminPassword = process.env.E2E_ADMIN_PASSWORD;
assert(adminEmail?.endsWith('@example.test') && adminPassword, 'Set E2E fixture credentials.');
const chromiumPath = process.env.E2E_CHROMIUM_PATH;
const screenshots = process.env.E2E_SCREENSHOT_DIR;
if (screenshots) await mkdir(screenshots, { recursive: true });
const run = Date.now().toString(36);
const category = 'Browser Audio ' + run;
const child = 'Browser Headphones ' + run;
const name = 'Browser Studio Headphones ' + run;
const slug = 'browser-studio-headphones-' + run;
const updated = name + ' Pro';
const image = resolve('public/images/demo/headphones.webp');
const replacement = resolve('public/images/demo/earbuds.webp');
const base = origin.origin;
const target = path => new URL(path, base).toString();
const chromeOptions = chromiumPath ? {
    executablePath: chromiumPath,
    args: ['--no-zygote', '--single-process', '--disable-dev-shm-usage', '--disable-gpu',
        '--disable-gpu-compositing', '--disable-software-rasterizer', '--use-gl=disabled'],
} : {};
let checks = 0;
const pass = message => { checks += 1; console.log('PASS ' + message); };
const shown = async locator => locator.waitFor({ state: 'visible' });
const go = async (page, path, code = 200) => {
    const response = await page.goto(target(path), { waitUntil: 'load' });
    assert.equal(response?.status(), code, path);
};
const shot = async (page, filename) => {
    if (screenshots) await page.screenshot({ path: resolve(screenshots, filename + '.png'), fullPage: true });
};
const noOverflow = async (page, label) => {
    const size = await page.evaluate(() => ({
        width: innerWidth, scroll: document.documentElement.scrollWidth,
    }));
    const viewport = page.viewportSize().width;
    assert(size.width <= viewport + 1 && size.scroll <= viewport + 1, label + ': ' + JSON.stringify(size));
};
const login = async page => {
    await go(page, '/admin');
    if (new URL(page.url()).pathname === '/admin') return;
    await page.getByLabel('Email address').fill(adminEmail);
    await page.getByLabel('Password').fill(adminPassword);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await page.waitForURL(target('/admin'));
    await shown(page.getByRole('heading', { name: 'Admin dashboard' }));
};
const remove = async (page, label) => {
    page.once('dialog', dialog => dialog.accept());
    await page.getByRole('button', { name: 'Delete ' + label, exact: true }).click();
    await shown(page.getByRole('status'));
};

const errors = [];
const withPage = async (options, flow) => {
    const browser = await chromium.launch(chromeOptions);
    try {
        const context = await browser.newContext(options);
        const page = await context.newPage();
        page.on('pageerror', error => errors.push(error.message));
        page.setDefaultTimeout(6000);
        await flow(page);
    } finally {
        await browser.close();
    }
};

await withPage({ viewport: { width: 1440, height: 1000 } }, async page => {
    await go(page, '/');
    await shown(page.getByRole('heading', { name: 'Featured products' }));
    await noOverflow(page, 'Storefront desktop');
    await shot(page, 'storefront-desktop');

    await go(page, '/admin/products');
    assert.equal(new URL(page.url()).pathname, '/login');
    const invalidCsrf = await fetch(target('/cart/1'), { method: 'POST', redirect: 'manual' });
    assert([419, 403].includes(invalidCsrf.status), 'CSRF protection on cart POST');
    await login(page);
    await noOverflow(page, 'Dashboard desktop');
    await shot(page, 'admin-desktop');
    pass('guest access denied, CSRF blocked, admin login');

    await go(page, '/admin/categories/create');
    await page.getByLabel('Category name').fill(category);
    await page.getByLabel('Description').fill('Browser test fixture.');
    await page.getByRole('button', { name: 'Create category' }).click();
    await page.waitForURL(target('/admin/categories'));
    await shown(page.getByText(category, { exact: true }).first());

    await go(page, '/admin/subcategories/create');
    await page.getByLabel('Subcategory name').fill(child);
    await page.getByLabel('Parent category').selectOption({ label: category });
    await page.getByRole('button', { name: 'Create subcategory' }).click();
    await page.waitForURL(target('/admin/subcategories'));
    await shown(page.getByText(child, { exact: true }));
    pass('category and subcategory created through admin forms');

    await go(page, '/admin/products/create');
    await page.getByLabel('Product name').fill(name);
    await page.getByLabel('Category', { exact: false }).first().selectOption({ label: category });
    await page.locator('#subcategory_id').selectOption({ label: child });
    assert(await page.locator('#subcategory_id option[data-category]:disabled').count() > 0);
    await page.getByLabel('Description').fill('Browser test product with a locally uploaded image.');
    await page.getByLabel('New price (BDT)').fill('1290.00');
    await page.getByLabel('Old price (BDT)').fill('2.00');
    await page.getByLabel('Choose image').setInputFiles(image);
    assert((await page.locator('[data-image-preview]').getAttribute('src'))?.startsWith('blob:'));
    await page.getByRole('button', { name: 'Create product' }).click();
    await shown(page.getByText('Old price must be at least the new price.').first());
    assert.equal(await page.locator('#old_price').getAttribute('aria-invalid'), 'true');
    await page.getByLabel('Old price (BDT)').fill('1490.00');
    await page.getByLabel('Choose image').setInputFiles(image);
    await page.getByRole('button', { name: 'Create product' }).click();
    await page.waitForURL(target('/admin/products'));
    await shown(page.getByRole('link', { name: 'View ' + name, exact: true }));
    pass('dependent dropdown, invalid price feedback, image preview and upload');

    await page.locator('.sidebar').getByRole('button', { name: 'Log out' }).click();
    await page.waitForURL(target('/'));
    await page.getByRole('textbox', { name: 'Search the catalogue' }).fill(name);
    await page.getByRole('button', { name: 'Search catalogue' }).click();
    await shown(page.getByRole('heading', { name: child }));
    await page.getByRole('link', { name: 'View ' + name, exact: true }).click();
    await page.waitForURL(target('/products/' + slug));
    await shown(page.getByRole('heading', { name, exact: true }));
    assert.equal(await page.locator('link[rel="canonical"]').getAttribute('href'), target('/products/' + slug));
    const firstImage = await page.locator('.detail-media img').getAttribute('src');
    assert(firstImage?.includes('/storage/products/'));
    const servedImage = await fetch(firstImage);
    assert.equal(servedImage.status, 200);
    assert.equal(servedImage.headers.get('content-type'), 'image/webp');
    pass('public search, subcategory grouping, stable SEO URL and real upload delivery');

    await page.getByRole('button', { name: 'Save product', exact: true }).click();
    await shown(page.getByRole('status'));
    await page.getByRole('link', { name: 'Saved products', exact: true }).first().click();
    await shown(page.getByRole('heading', { name, exact: true }));
    await go(page, '/products/' + slug);
    await page.getByRole('button', { name: 'Increase quantity' }).click();
    assert.equal(await page.getByLabel('Quantity', { exact: true }).inputValue(), '2');
    await page.getByRole('button', { name: 'Add to cart', exact: true }).click();
    await page.waitForURL(target('/cart'));
    await page.getByRole('spinbutton', { name: 'Quantity for ' + name, exact: true }).fill('3');
    await page.getByRole('button', { name: 'Update quantity for ' + name }).click();
    await shown(page.getByText('Quantity updated.'));
    await shown(page.getByText('Tk 3,870.00', { exact: true }).first());
    await page.getByRole('link', { name: 'Continue to checkout' }).click();
    await page.getByLabel('Full name').fill('Sample Shopper');
    await page.getByLabel('Email address').fill('browser-shopper@example.test');
    await page.getByLabel('Phone number').fill('+8801700000000');
    await page.getByLabel('Delivery address').fill('123 Example Avenue, Dhaka');
    await page.getByRole('button', { name: 'Place order' }).click();
    await shown(page.getByRole('heading', { name: 'Order placed' }));
    const receipt = page.url();
    const reference = new URL(receipt).pathname.split('/').at(-1);
    assert.equal((await fetch(receipt)).status, 404, 'receipt cannot be read in another session');
    pass('favorites, guest cart quantity, checkout and private receipt');

    await login(page);
    await go(page, '/admin/orders?q=' + reference);
    await page.getByRole('link', { name: 'View order ' + reference }).click();
    await shown(page.getByText(name, { exact: true }));
    await page.getByLabel('Change status').selectOption('processing');
    await page.getByRole('button', { name: 'Update status' }).click();
    await shown(page.getByText('Order status updated.'));
    await go(page, new URL(receipt).pathname);
    await shown(page.getByText('Processing', { exact: true }));
    pass('saved order appears in admin and status is visible on receipt');

    await go(page, '/admin/products?q=' + encodeURIComponent(name));
    await page.getByRole('link', { name: 'Edit ' + name, exact: true }).click();
    await page.getByLabel('Product name').fill(updated);
    await page.getByLabel('Choose image').setInputFiles(replacement);
    await page.getByRole('button', { name: 'Save product' }).click();
    await shown(page.getByText('Product updated.'));
    await go(page, '/products/' + slug);
    await shown(page.getByRole('heading', { name: updated, exact: true }));
    const secondImage = await page.locator('.detail-media img').getAttribute('src');
    assert.notEqual(secondImage, firstImage);
    assert([403, 404].includes((await fetch(firstImage)).status), 'obsolete image is no longer served');
    assert.equal((await fetch(secondImage)).status, 200, 'replacement image served');
    await go(page, '/admin/products?q=' + encodeURIComponent(updated));
    await remove(page, updated);
    await go(page, '/products/' + slug, 404);
    assert([403, 404].includes((await fetch(secondImage)).status), 'deleted image is no longer served');

    await go(page, '/admin/subcategories?q=' + encodeURIComponent(child));
    await remove(page, child);
    await go(page, '/admin/categories?q=' + encodeURIComponent(category));
    await remove(page, category);
    await go(page, '/admin/orders?q=' + reference);
    await page.getByRole('link', { name: 'View order ' + reference }).click();
    await shown(page.getByText(name, { exact: true }));
    pass('image replacement, slug stability, catalog deletion and preserved order history');
});

await withPage({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 }, async page => {
    await go(page, '/');
    await noOverflow(page, 'Storefront mobile');
    await shot(page, 'storefront-mobile');
    await go(page, '/products');
    await noOverflow(page, 'Catalogue mobile');
    await go(page, '/products/wireless-studio-headphones');
    await noOverflow(page, 'Product mobile');
    await login(page);
    await noOverflow(page, 'Dashboard mobile');
    await page.getByLabel('Admin menu').click();
    await page.locator('.mobile-navigation').getByRole('link', { name: 'Products', exact: true }).click();
    await page.waitForURL(target('/admin/products'));
    await noOverflow(page, 'Product management mobile');
    await shot(page, 'admin-mobile');
    await page.setViewportSize({ width: 320, height: 760 });
    await noOverflow(page, 'Product management 320 px');
    await go(page, '/');
    await noOverflow(page, 'Storefront 320 px');
    pass('mobile navigation and no page overflow at 390 px and 320 px');
});

assert.deepEqual(errors, [], 'unexpected browser JavaScript errors');
console.log(checks + ' Playwright workflow checks passed.');
