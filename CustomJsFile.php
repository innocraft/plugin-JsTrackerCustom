<?php

/**
 * InnoCraft - the company of the makers of Piwik Analytics, the free/libre analytics platform
 *
 * @link https://www.innocraft.com
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerCustom;

use Piwik\Piwik;
use Piwik\Plugin\Manager;

/**
 * Resolves the file the custom tracker JavaScript is stored in.
 */
class CustomJsFile
{
    /**
     * Needs to be kept in sync with `PluginTrackerFiles::TRACKER_FILE`, otherwise the file stored in the plugin
     * directory would no longer be added to the JavaScript tracker.
     */
    public const FILE_NAME = 'tracker.js';

    /**
     * Returns the absolute path of the file within the plugin directory. This file is added to the JavaScript
     * tracker by the CustomJsTracker plugin as long as it exists.
     *
     * @return string
     */
    public static function getDefaultPath()
    {
        return rtrim(Manager::getPluginDirectory('JsTrackerCustom'), '/') . '/' . self::FILE_NAME;
    }

    /**
     * Returns the absolute path of the file the custom tracker JavaScript is read from and written to.
     *
     * @return string
     */
    public static function getPath()
    {
        $customJsFile = self::getDefaultPath();

        /**
         * Triggered when the file that holds the custom tracker JavaScript is resolved.
         *
         * By default the JavaScript is stored in the plugin directory. This event lets a plugin store it
         * somewhere else. This is needed when several Matomo instances share the same plugin directory but
         * each instance needs its own custom tracker JavaScript, for example when Matomo for WordPress runs
         * in a multisite network where each blog is administered separately.
         *
         * A plugin that changes the path is responsible for the generated JavaScript tracker as well. The
         * CustomJsTracker plugin only looks for a `tracker.js` in the directory of each activated plugin, so
         * a file stored anywhere else is not added to the tracker unless the plugin also replaces the
         * `Piwik\Plugins\CustomJsTracker\TrackingCode\PluginTrackerFiles` definition in the dependency
         * injection container. Any file left behind in the plugin directory keeps being added to the tracker.
         *
         * The path needs to be absolute, otherwise it is ignored and the default location is used.
         *
         * **Example**
         *
         *     public function setCustomJsFilePath(&$customJsFile)
         *     {
         *         $customJsFile = '/path/to/site/specific/tracker.js';
         *     }
         *
         * @param string &$customJsFile The absolute path of the file the custom JavaScript is stored in.
         */
        Piwik::postEvent('JsTrackerCustom.getCustomJsFilePath', [&$customJsFile]);

        if (!is_string($customJsFile) || !self::isAbsolutePath($customJsFile)) {
            return self::getDefaultPath();
        }

        return $customJsFile;
    }

    private static function isAbsolutePath(string $path): bool
    {
        return 0 === strpos($path, '/') || 1 === preg_match('/^[a-zA-Z]:[\\\\\/]/', $path);
    }
}
