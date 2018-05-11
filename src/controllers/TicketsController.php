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
use lukeyouell\support\services\MessageService;
use lukeyouell\support\services\TicketService;

use Craft;
use craft\elements\Asset;
use craft\web\Controller;

use yii\base\InvalidConfigException;

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

    public function actionShowTicket(string $ticketId = null)
    {
        $ticket = TicketService::getTicketById($ticketId);
        $messages = MessageService::getMessagesByTicketId($ticket->id);

        $variables = [
            'ticket'   => $ticket,
            'author'   => $ticket->authorId ? Craft::$app->users->getUserById($ticket->authorId) : null,
            'messages' => $messages,
            'volume' => $this->settings->volumeId ? Craft::$app->getVolumes()->getVolumeById($this->settings->volumeId) : null,
            'assetElementType' => Asset::class,
        ];

        return $this->renderTemplate('support/_tickets/ticket', $variables);
    }

    public function actionNewTicketTemplate()
    {
        $variables = [
            'volume' => $this->settings->volumeId ? Craft::$app->getVolumes()->getVolumeById($this->settings->volumeId) : null,
            'elementType' => Asset::class,
        ];

        return $this->renderTemplate('support/_tickets/new', $variables);
    }

    public function actionNewTicket()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // First create ticket
        $ticket = TicketService::createTicket($request);

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

            // Ticket created, now create message
            $message = MessageService::createMessage($ticket->id, $request);

            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => true,
                ]);
            }

            Craft::$app->getSession()->setNotice('Ticket created.');

            return $this->redirectToPostedUrl();
        }
    }
}
