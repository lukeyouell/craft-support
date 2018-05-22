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

use Craft;
use craft\elements\Asset;
use craft\helpers\Template;
use craft\web\Controller;

use yii\base\InvalidConfigException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class TicketsController extends Controller
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
        return $this->renderTemplate('support/_tickets/index');
    }

    public function actionNew()
    {
        $volume = $this->settings->volumeId ? Craft::$app->getVolumes()->getVolumeById($this->settings->volumeId) : null;

        $variables = [
            'volume' => $volume,
            'elementType' => Asset::class,
            'settings' => $this->settings,
        ];

        return $this->renderTemplate('support/_tickets/new', $variables);
    }

    public function actionView(string $ticketId = null)
    {
        $ticket = Support::getInstance()->ticketService->getTicketById($ticketId);

        if (!$ticket) {
            throw new NotFoundHttpException('Ticket not found');
        }

        $volume = $this->settings->volumeId ? Craft::$app->getVolumes()->getVolumeById($this->settings->volumeId) : null;

        $variables = [
            'ticket'   => $ticket,
            'ticketStatuses' => Support::getInstance()->ticketStatusService->getAllTicketStatuses(),
            'volume' => $volume,
            'assetElementType' => Asset::class,
            'settings' => $this->settings,
        ];

        return $this->renderTemplate('support/_tickets/ticket', $variables);
    }

    public function actionCreate()
    {
        $this->requirePostRequest();

        $settings = Support::$plugin->getSettings();
        $request = Craft::$app->getRequest();

        // First create ticket
        $ticket = Support::getInstance()->ticketService->createTicket($request);

        if (!$ticket) {
            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                ]);
            }

            Craft::$app->getSession()->setError('Couldn’t create the ticket.');

            Craft::$app->getUrlManager()->setRouteParams([
                'ticket' => $ticket,
            ]);

            return null;
        } else {

            // Ticket created, now create message but don't change ticket status id
            $message = Support::getInstance()->messageService->createMessage($ticket->id, $request, false);

            // Handle email notification after message is created
            if ($ticket->ticketStatus->emails) {
                Support::getInstance()->mailService->handleEmail($ticket);
            }

            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => true,
                ]);
            }

            Craft::$app->getSession()->setNotice('Ticket created.');

            return $this->redirectToPostedUrl();
        }
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $ticketId = Craft::$app->security->validateData($request->post('ticketId'));
        $ticketStatusId = $request->post('ticketStatusId');

        if ($ticketId) {
            $ticket = Support::getInstance()->ticketService->getTicketById($ticketId);

            if (!$ticket) {
                throw new NotFoundHttpException('Ticket not found');
            }

            if ($request->post('ticketStatusId')) {
                Support::getInstance()->ticketService->changeTicketStatus($ticket, $ticketStatusId);
            }

            Craft::$app->getElements()->saveElement($ticket, false);

            Craft::$app->getSession()->setNotice('Ticket updated.');
        }

        return $this->redirectToPostedUrl();
    }
}
