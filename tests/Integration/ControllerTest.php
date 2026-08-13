<?php

/**
 * InnoCraft - the company of the makers of Piwik Analytics, the free/libre analytics platform
 *
 * @link https://www.innocraft.com
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerCustom\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\JsTrackerCustom\Controller;
use Piwik\Plugins\JsTrackerCustom\CustomJsFile;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group JsTrackerCustom
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    private $customJsDir;
    private $customJsFile;
    private $defaultFile;
    private $defaultFileContent;
    private $generatedTrackerFile;
    private $generatedTrackerContent;

    public function setUp(): void
    {
        parent::setUp();

        $this->customJsDir  = StaticContainer::get('path.tmp') . '/jstrackercustom';
        $this->customJsFile = $this->customJsDir . '/tracker.js';
        Filesystem::mkdir($this->customJsDir);

        // the plugin directory file and the generated tracker are part of the checkout, they need to be restored
        $this->defaultFile          = CustomJsFile::getDefaultPath();
        $this->defaultFileContent   = is_readable($this->defaultFile) ? file_get_contents($this->defaultFile) : null;
        $this->generatedTrackerFile = PIWIK_DOCUMENT_ROOT . '/matomo.js';
        $this->generatedTrackerContent = is_readable($this->generatedTrackerFile) ? file_get_contents($this->generatedTrackerFile) : null;

        Piwik::addAction('JsTrackerCustom.getCustomJsFilePath', function (&$customJsFile) {
            $customJsFile = $this->customJsFile;
        });

        // the admin page renders the menu, which needs a site to be selected
        Fixture::createWebsite('2020-01-01 00:00:00');
        $_GET['idSite'] = '1';
        $_GET['period'] = 'day';
        $_GET['date']   = 'today';
    }

    public function tearDown(): void
    {
        $this->restoreFile($this->defaultFile, $this->defaultFileContent);
        $this->restoreFile($this->generatedTrackerFile, $this->generatedTrackerContent);
        Filesystem::unlinkRecursive($this->customJsDir, true);

        parent::tearDown();
    }

    public function testIndexSavesCustomJsToTheFileProvidedByTheEvent()
    {
        $_POST['customJsNonce'] = Nonce::getNonce('JsTrackerCustom.save');
        $_POST['customJs']      = 'console.log("custom");';

        $output = $this->makeController()->index();

        $this->assertSame('console.log("custom");', file_get_contents($this->customJsFile));
        $this->assertSame('', (string) file_get_contents($this->defaultFile));
        $this->assertStringContainsString($this->renderedCustomJs('console.log("custom");'), $output);
    }

    public function testIndexReadsCustomJsFromTheFileProvidedByTheEvent()
    {
        file_put_contents($this->customJsFile, 'console.log("from event file");');
        file_put_contents($this->defaultFile, 'console.log("from plugin directory");');

        $output = $this->makeController()->index();

        $this->assertStringContainsString($this->renderedCustomJs('console.log("from event file");'), $output);
        $this->assertStringNotContainsString('from plugin directory', $output);
    }

    public function testIndexNotifiesAboutUnusedFileLeftBehindInThePluginDirectory()
    {
        file_put_contents($this->defaultFile, 'console.log("left behind");');

        $output = $this->makeController()->index();

        $expected = Piwik::translate('JsTrackerCustom_UnusedDefaultFile', array($this->customJsFile, $this->defaultFile));

        $this->assertStringContainsString($expected, $output);
    }

    public function testIndexDoesNotNotifyWhenNoUnusedFileIsLeftBehind()
    {
        file_put_contents($this->defaultFile, '');

        $output = $this->makeController()->index();

        $this->assertStringNotContainsString('is no longer used', $output);
    }

    /**
     * The custom JavaScript is passed to the Vue component as a JSON encoded HTML attribute
     */
    private function renderedCustomJs($customJs)
    {
        return htmlspecialchars(json_encode($customJs), ENT_QUOTES | ENT_SUBSTITUTE);
    }

    public function provideContainerConfig()
    {
        return [
            // rendering the admin page compiles templates, use a dedicated cache directory so the tests do not
            // depend on write access to a cache directory that may be owned by the web server
            'path.tmp.templates' => StaticContainer::get('path.tmp') . '/templates_c_jstrackercustom',
        ];
    }

    private function makeController()
    {
        return StaticContainer::getContainer()->make(Controller::class);
    }

    private function restoreFile($file, $content)
    {
        if (null === $content) {
            if (file_exists($file)) {
                unlink($file);
            }
            return;
        }

        file_put_contents($file, $content);
    }
}
