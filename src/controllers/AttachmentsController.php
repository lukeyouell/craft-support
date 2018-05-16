<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support\controllers;

use lukeyouell\support\Support;
use lukeyouell\support\services\TicketStatusService;

use Craft;
use craft\web\Controller;

use yii\base\InvalidConfigException;
use yii\web\Response;

class AttachmentsController extends Controller
{
    // Public Properties
    // =========================================================================

    public $settings;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        $this->settings = Support::$plugin->getSettings();
        if (!$this->settings->validate()) {
            throw new InvalidConfigException('Support settings don’t validate.');
        }
    }

    public function actionIndex()
    {
        $settings = $this->settings;
        $pluginHandle = Support::$plugin->handle;
        $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($pluginHandle));
        $volumes = Craft::$app->volumes->getAllVolumes();

        $variables = [
          'settings'       => $settings,
          'pluginHandle'   => $pluginHandle,
          'overrides'      => $overrides,
          'volumes'        => $volumes,
        ];

        return $this->renderTemplate('support/_settings/attachments/index', $variables);
    }
}
