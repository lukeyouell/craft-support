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
use craft\helpers\Json;
use craft\web\Controller;

use yii\base\InvalidConfigException;
use yii\web\Response;

class TicketStatusesController extends Controller
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
        $ticketStatuses = TicketStatusService::getAllTicketStatuses();

        $variables = [
          'settings'       => $settings,
          'ticketStatuses' => $ticketStatuses,
        ];

        return $this->renderTemplate('support/_settings/ticket-statuses/index', $variables);
    }

    public function actionEdit(int $id = null, TicketStatusModel $ticketStatus = null)
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

    public function actionReorder(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $ids = Json::decode(Craft::$app->getRequest()->getRequiredBodyParam('ids'));

        if ($success = TicketStatusService::reorderTicketStatuses($ids)) {
            return $this->asJson(['success' => $success]);
        }

        return $this->asJson(['error' => 'Couldn’t reorder ticket statuses.']);
    }

    public function actionDelete()
    {
        $this->requireAcceptsJson();

        $ticketStatusId = Craft::$app->getRequest()->getRequiredParam('id');

        if ($success = TicketStatusService::deleteTicketStatusById($ticketStatusId)) {
            return $this->asJson(['success' => true]);
        }

        return $this->asJson(['error' => 'Couldn’t delete ticket status.']);
    }
}
