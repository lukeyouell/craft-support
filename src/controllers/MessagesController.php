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
        $messageVal = $request->post('message');

        if (!$messageVal) {
            Craft::$app->getSession()->setError('Message field is required.');
        } else {
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
        }

        return $this->redirectToPostedUrl();
    }

    public function actionDeleteMessage()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $messageId = $request->post('messageId');

        if ($messageId) {
            $res = MessageService::deleteMessage($messageId);

            if (!$res) {
                Craft::$app->getSession()->setError('Couldn’t delete the message.');
            } else {
                Craft::$app->getSession()->setNotice('Message deleted.');
            }
        }

        return $this->redirectToPostedUrl();
    }
}
