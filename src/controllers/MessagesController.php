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
use lukeyouell\support\elements\Message;
use lukeyouell\support\elements\db\MessageQuery;
use lukeyouell\support\elements\Ticket;
use lukeyouell\support\elements\db\TicketQuery;
use lukeyouell\support\services\MessageService;
use lukeyouell\support\services\TicketService;

use Craft;
use craft\elements\Asset;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\web\Controller;

use yii\base\InvalidConfigException;

class MessagesController extends Controller
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

    public function actionNewMessage()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $ticketId = Craft::$app->security->validateData($request->post('ticketId'));

        // First check ticket exists
        $ticket = TicketService::getTicketById($ticketId);

        if (!$ticket) {
            Craft::$app->getSession()->setError('Couldn’t find the ticket.');
        } else {
            // Ticket exists, now create message
            $message = MessageService::createMessage($ticket->id, $request);

            if (!$message) {
                Craft::$app->getSession()->setError('Couldn’t send the message.');
            } else {
                Craft::$app->getSession()->setNotice('Message sent.');
            }
        }

        return $this->redirectToPostedUrl();
    }
}
