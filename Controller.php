<?php

/**
 * InnoCraft - the company of the makers of Piwik Analytics, the free/libre analytics platform
 *
 * @link https://www.innocraft.com
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerCustom;

use Piwik\Nonce;
use Piwik\Notification;
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\CustomJsTracker\CustomJsTracker;
use Piwik\Request;
use Piwik\View;

/**
 *
 */
class Controller extends ControllerAdmin
{
    public function index()
    {
        Piwik::checkUserHasSuperUserAccess();

        $customJsFile = CustomJsFile::getPath();
        $customJsDir  = dirname($customJsFile);

        if (is_writable($customJsDir) && !file_exists($customJsFile)) {
            file_put_contents($customJsFile, '');
        }

        if (!is_writable($customJsDir) || !is_writable($customJsFile)) {
            $notification = new Notification(Piwik::translate('JsTrackerCustom_DirectoryOrFileNotWritable', array($customJsDir, $customJsFile)));
            $notification->context = Notification::CONTEXT_ERROR;
            Notification\Manager::notify('JsTrackerCustom_FileNotWritable', $notification);
        } elseif ($nonce = Request::fromPost()->getStringParameter('customJsNonce', '')) {
            Nonce::checkNonce('JsTrackerCustom.save', $nonce);

            // an empty value is saved as well, so all custom JavaScript can be removed again
            $customJs = Request::fromPost()->getStringParameter('customJs', '');

            file_put_contents($customJsFile, $customJs);

            $instance = new CustomJsTracker();
            $instance->updateTracker();

            $notification = new Notification(Piwik::translate('General_YourChangesHaveBeenSaved'));
            $notification->context = Notification::CONTEXT_SUCCESS;
            Notification\Manager::notify('JsTrackerCustom_Saved', $notification);
        }

        $this->checkForUnusedDefaultFile($customJsFile);

        $view = new View('@JsTrackerCustom/index');
        $this->setBasicVariablesView($view);
        $view->customJs = file_exists($customJsFile) ? file_get_contents($customJsFile) : '';
        $view->customJsNonce = Nonce::getNonce('JsTrackerCustom.save');

        return $view->render();
    }

    /**
     * The file within the plugin directory is added to the JavaScript tracker no matter where the custom JavaScript
     * is stored. When another plugin changed the location, any code left behind in the plugin directory would still
     * be tracked while it can no longer be edited or removed through this page.
     *
     * @param string $customJsFile The file the custom JavaScript is currently stored in
     */
    private function checkForUnusedDefaultFile($customJsFile)
    {
        if (!CustomJsFile::hasUnusedDefaultFile($customJsFile)) {
            return;
        }

        $notification = new Notification(Piwik::translate('JsTrackerCustom_UnusedDefaultFile', array($customJsFile, CustomJsFile::getDefaultPath())));
        $notification->context = Notification::CONTEXT_WARNING;
        Notification\Manager::notify('JsTrackerCustom_UnusedDefaultFile', $notification);
    }
}
