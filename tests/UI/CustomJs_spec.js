/*!
 * InnoCraft - the company of the makers of Piwik Analytics, the free/libre analytics platform
 *
 * @link https://www.innocraft.com
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("CustomJs", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\JsTrackerCustom\\tests\\Fixtures\\UITestFixture";

    it('should show custom js admin', async function () {
        await page.goto("?module=JsTrackerCustom&action=index");
        await page.waitForNetworkIdle();
        await page.type('[name="customJs"]', 'console.log("new code");');
        await page.click('button[type=submit]');
        await page.waitForNetworkIdle();
        await page.waitFor(250);
        await page.evaluate(function() {
            $('textarea').css('resize', 'none');
        });

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('manage');
    });

    it('should not save custom js given in the URL instead of the submitted form field', async function () {
        const urlCode = ';window.customJsFromUrl = true;';

        await page.goto("?module=JsTrackerCustom&action=index&customJs=" + encodeURIComponent(urlCode));
        await page.waitForNetworkIdle();

        // the editor always shows the stored code, never a value given in the URL
        expect(await page.evaluate(() => document.querySelector('[name="customJs"]').value))
            .to.not.contain('customJsFromUrl');

        await page.click('[name="customJs"]', { clickCount: 3 });
        await page.type('[name="customJs"]', 'console.log("submitted code");');
        await page.click('button[type=submit]');
        await page.waitForNetworkIdle();

        expect(await page.evaluate(() => document.querySelector('[name="customJs"]').value))
            .to.equal('console.log("submitted code");');
    });

    it('should be possible to remove all custom js again', async function () {
        await page.goto("?module=JsTrackerCustom&action=index");
        await page.waitForNetworkIdle();

        await page.click('[name="customJs"]', { clickCount: 3 });
        await page.keyboard.press('Backspace');
        await page.click('button[type=submit]');
        await page.waitForNetworkIdle();

        expect(await page.evaluate(() => document.querySelector('[name="customJs"]').value)).to.equal('');
    });

});