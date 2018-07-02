<?php
/**
 * InnoCraft - the company of the makers of Piwik Analytics, the free/libre analytics platform
 *
 * @link https://www.innocraft.com
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\JsTrackerCustom\tests\Fixtures;

use Piwik\Date;
use Piwik\Filesystem;
use Piwik\Plugin\Manager;
use Piwik\Plugins\SearchEngineKeywordsPerformance\MeasurableSettings;
use Piwik\Plugins\SearchEngineKeywordsPerformance\tests\Mocks\BingClient;
use Piwik\Plugins\SearchEngineKeywordsPerformance\tests\Mocks\GoogleClient;
use Piwik\Tests\Framework\Fixture;

class UITestFixture extends Fixture
{
    public $dateTime  = '2015-05-12 01:23:45';
    public $idSite    = 1;
    public $accountId = '234535673456';
    public $url       = 'http://piwik.net';
    public $apiKey   = 'dummyKey';

    public function setUp()
    {
        $this->setUpWebsite();

        $customJsFile = __DIR__ . '/../../tracker.js';
        file_put_contents($customJsFile, '');
    }

    public function tearDown()
    {
        Filesystem::remove(__DIR__ . '/../../tracker.js');
    }

    public function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime);
        }
    }
}