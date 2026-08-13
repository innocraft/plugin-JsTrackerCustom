<?php

/**
 * InnoCraft - the company of the makers of Piwik Analytics, the free/libre analytics platform
 *
 * @link https://www.innocraft.com
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerCustom\tests\Integration;

use Piwik\Piwik;
use Piwik\Plugins\JsTrackerCustom\CustomJsFile;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group JsTrackerCustom
 * @group CustomJsFileTest
 * @group Plugins
 */
class CustomJsFileTest extends IntegrationTestCase
{
    /**
     * The default location must not change, an existing file needs to keep being used after an update
     */
    public function testGetPathUsesTrackerJsNextToThePluginFilesByDefault()
    {
        $expected = dirname(__DIR__, 2) . '/tracker.js';
        $actual   = CustomJsFile::getPath();

        $this->assertSame(basename($expected), basename($actual));
        $this->assertSame(realpath(dirname($expected)), realpath(dirname($actual)));
    }

    public function testGetDefaultPathIsUsedWhenNoPluginChangesTheLocation()
    {
        $this->assertSame(CustomJsFile::getDefaultPath(), CustomJsFile::getPath());
    }

    public function testGetPathCanBeChangedThroughEvent()
    {
        Piwik::addAction('JsTrackerCustom.getCustomJsFilePath', function (&$customJsFile) {
            $customJsFile = '/path/to/site/specific/tracker.js';
        });

        $this->assertSame('/path/to/site/specific/tracker.js', CustomJsFile::getPath());
    }

    /**
     * @dataProvider getInvalidPaths
     */
    public function testGetPathIgnoresInvalidPathsProvidedByEvent($invalidPath)
    {
        Piwik::addAction('JsTrackerCustom.getCustomJsFilePath', function (&$customJsFile) use ($invalidPath) {
            $customJsFile = $invalidPath;
        });

        $this->assertSame(CustomJsFile::getDefaultPath(), CustomJsFile::getPath());
    }

    public function getInvalidPaths()
    {
        return [
            'empty string' => [''],
            'relative path' => ['tmp/tracker.js'],
            'relative path with dot' => ['./tmp/tracker.js'],
            'null' => [null],
            'not a string' => [['/path/to/tracker.js']],
        ];
    }
}
