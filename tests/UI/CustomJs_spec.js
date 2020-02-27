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

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('manage');
    });

});