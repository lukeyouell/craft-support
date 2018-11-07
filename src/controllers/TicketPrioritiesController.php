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
use lukeyouell\support\models\TicketPriority as TicketPriorityModel;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\web\Controller;

use yii\base\InvalidConfigException;
use yii\web\Response;

class TicketPrioritiesController extends Controller
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
        $ticketPriorities = Support::getInstance()->ticketPriorityService->getAllTicketPriorities();

        $variables = [
            'settings'       => $settings,
            'ticketPriorities' => $ticketPriorities,
        ];

        return $this->renderTemplate('support/_settings/ticket-priorities/index', $variables);
    }

    public function actionEdit(int $id = null, TicketPriorityModel $ticketPriority = null)
    {
        $variables = [
            'id'           => $id,
            'ticketPriority' => $ticketPriority,
        ];

        if (!$variables['ticketPriority']) {
            if ($variables['id']) {
                $variables['ticketPriority'] = Support::getInstance()->ticketPriorityService->getTicketPriorityById($variables['id']);

                if (!$variables['ticketPriority']) {
                    throw new NotFoundHttpException('Ticket priority not found');
                }
            } else {
                $variables['ticketPriority'] = new TicketPriorityModel();
            }
        }

        if ($variables['ticketPriority']->id) {
            $variables['title'] = $variables['ticketPriority']->name;
        } else {
            $variables['title'] = 'Create a new ticket priority';
        }

        $emails = Support::getInstance()->emailService->getAllEmails();
        $variables['emails'] = ArrayHelper::map($emails, 'id', 'name');

        return $this->renderTemplate('support/_settings/ticket-priorities/edit', $variables);
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $id = $request->post('id');
        $ticketPriority = Support::getInstance()->ticketPriorityService->getTicketPriorityById($id);

        if (!$ticketPriority) {
            $ticketPriority = new TicketPriorityModel();
        }

        $ticketPriority->name = $request->post('name');
        $ticketPriority->handle = $request->post('handle');
        $ticketPriority->colour = $request->post('colour');
        $ticketPriority->default = $request->post('default');
        // $emailIds = $request->post('emails');

        // if (!$emailIds) {
        //     $emailIds = [];
        // }

        // Save it
		// $save = Support::getInstance()->ticketPriorityService->saveTicketPriority($ticketPriority, $emailIds);
		$save = Support::getInstance()->ticketPriorityService->saveTicketPriority($ticketPriority);

        if ($save) {
            Craft::$app->getSession()->setNotice('Ticket priority saved.');

            $this->redirectToPostedUrl();
        } else {
            Craft::$app->getSession()->setError('Couldn’t save ticket priority.');
        }

        Craft::$app->getUrlManager()->setRouteParams(compact('ticketPriority'));
    }

    public function actionReorder(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $ids = Json::decode(Craft::$app->getRequest()->getRequiredBodyParam('ids'));

        if ($success = Support::getInstance()->ticketPriorityService->reorderTicketPriorities($ids)) {
            return $this->asJson(['success' => $success]);
        }

        return $this->asJson(['error' => 'Couldn’t reorder ticket priorities.']);
    }

    public function actionDelete()
    {
        $this->requireAcceptsJson();

        $ticketPriorityId = Craft::$app->getRequest()->getRequiredParam('id');

        if ($success = Support::getInstance()->ticketPriorityService->deleteTicketPriorityById($ticketPriorityId)) {
            return $this->asJson(['success' => true]);
        }

        return $this->asJson(['error' => 'Couldn’t delete ticket priority.']);
    }
}
