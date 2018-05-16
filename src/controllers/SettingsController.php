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
use lukeyouell\support\models\TicketStatus as TicketStatusModel;
use lukeyouell\support\services\TicketStatusService;

use Craft;
use craft\web\Controller;

use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

class SettingsController extends Controller
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

    public function actionShowSettings(string $category = null)
    {
        if (!$category) {
            throw new NotFoundHttpException('Category not found');
        }

        $settings = $this->settings;
        $pluginHandle = Support::$plugin->handle;
        $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($pluginHandle));
        $volumes = Craft::$app->volumes->getAllVolumes();
        $ticketStatuses = TicketStatusService::getAllTicketStatuses();

        $variables = [
          'settings'       => $settings,
          'pluginHandle'   => $pluginHandle,
          'overrides'      => $overrides,
          'volumes'        => $volumes,
          'ticketStatuses' => $ticketStatuses,
        ];

        return $this->renderTemplate('support/_settings/'.$category, $variables);
    }

    public function actionEditTicketStatus(int $id = null, TicketStatusModel $ticketStatus = null)
    {
        $variables = [
            'id'           => $id,
            'ticketStatus' => $ticketStatus,
        ];

        if (!$variables['ticketStatus']) {
            if ($variables['id']) {
                $variables['ticketStatus'] = TicketStatusService::getTicketStatusById($variables['id']);

                if (!$variables['ticketStatus']) {
                    throw new NotFoundHttpException('Ticket status not found');
                }
            } else {
                $variables['ticketStatus'] = new TicketStatusModel();
            }
        }

        if ($variables['ticketStatus']->id) {
            $variables['title'] = $variables['ticketStatus']->name;
        } else {
            $variables['title'] = 'Create a new ticket status';
        }

        return $this->renderTemplate('support/_settings/edit-ticket-status', $variables);
    }
}
