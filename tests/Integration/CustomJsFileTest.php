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
     * @var string|null Content of the file within the plugin directory before the test, null when it does not exist
     */
    private $defaultFileContent;

    public function setUp(): void
    {
        parent::setUp();

        // the file within the plugin directory belongs to the installation, it is not part of the repository
        $this->defaultFileContent = $this->readDefaultFile();
    }

    public function tearDown(): void
    {
        if (null === $this->defaultFileContent) {
            if (file_exists(CustomJsFile::getDefaultPath())) {
                unlink(CustomJsFile::getDefaultPath());
            }
        } else {
            file_put_contents(CustomJsFile::getDefaultPath(), $this->defaultFileContent);
        }

        parent::tearDown();
    }

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

    public function testHasUnusedDefaultFileIsFalseWhenTheDefaultLocationIsUsed()
    {
        $this->writeDefaultFile('console.log("in use");');

        $this->assertFalse(CustomJsFile::hasUnusedDefaultFile(CustomJsFile::getDefaultPath()));
    }

    public function testHasUnusedDefaultFileIsTrueWhenAFileIsLeftBehindInThePluginDirectory()
    {
        $this->writeDefaultFile('console.log("left behind");');

        $this->assertTrue(CustomJsFile::hasUnusedDefaultFile('/path/to/site/specific/tracker.js'));
    }

    public function testHasUnusedDefaultFileIsFalseWhenNoFileIsLeftBehindInThePluginDirectory()
    {
        $this->removeDefaultFile();

        $this->assertFalse(CustomJsFile::hasUnusedDefaultFile('/path/to/site/specific/tracker.js'));
    }

    /**
     * @dataProvider getEmptyFileContents
     */
    public function testHasUnusedDefaultFileIsFalseWhenTheFileLeftBehindHasNoCode($content)
    {
        $this->writeDefaultFile($content);

        $this->assertFalse(CustomJsFile::hasUnusedDefaultFile('/path/to/site/specific/tracker.js'));
    }

    public function getEmptyFileContents()
    {
        return [
            'empty' => [''],
            'whitespace only' => ["\n \n"],
        ];
    }

    private function readDefaultFile()
    {
        return is_readable(CustomJsFile::getDefaultPath()) ? file_get_contents(CustomJsFile::getDefaultPath()) : null;
    }

    private function writeDefaultFile($content)
    {
        file_put_contents(CustomJsFile::getDefaultPath(), $content);
    }

    private function removeDefaultFile()
    {
        if (file_exists(CustomJsFile::getDefaultPath())) {
            unlink(CustomJsFile::getDefaultPath());
        }
    }
}
