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

        $customJsFile = __DIR__ . '/tracker.js';

        if (is_writable(__DIR__) && !file_exists($customJsFile)) {
            file_put_contents($customJsFile, '');
        }

        if (!is_writable(__DIR__) || !is_writable($customJsFile)) {
            $notification = new Notification(Piwik::translate('JsTrackerCustom_DirectoryOrFileNotWritable', array(__DIR__, $customJsFile)));
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

        $view = new View('@JsTrackerCustom/index');
        $this->setBasicVariablesView($view);
        $view->customJs = file_get_contents($customJsFile);
        $view->customJsNonce = Nonce::getNonce('JsTrackerCustom.save');

        return $view->render();
    }
}
