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

use Craft;
use craft\elements\Asset;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\web\Controller;

use yii\base\InvalidConfigException;

class TicketsController extends Controller
{
    public function actionShowTicket(string $ticketId = null)
    {
        $query = new TicketQuery(Ticket::class);
        $query->id = $ticketId;

        $ticket = $query->one();

        $query = new MessageQuery(Message::class);
        $query->ticketId = $ticket->id;

        $messageResults = $query->all();

        $messages = [];

        foreach ($messageResults as $message) {
          $message = ArrayHelper::toArray($message);

          $messages[] = array_merge($message, [
            'author' => $message['authorId'] ? Craft::$app->users->getUserById($message['authorId']) : null,
          ]);
        }

        $variables = [
            'ticket'   => $ticket,
            'author'   => $ticket->authorId ? Craft::$app->users->getUserById($ticket->authorId) : null,
            'messages' => $messages,
        ];

        return $this->renderTemplate('support/tickets/_ticket', $variables);
    }

    public function actionNewTicket()
    {
        // Get the plugin settings and make sure they validate before doing anything
        $settings = Support::$plugin->getSettings();
        if (!$settings->validate()) {
            throw new InvalidConfigException('Support settings don’t validate.');
        }

        $variables = [
            'volume' => $settings->volumeId ? Craft::$app->getVolumes()->getVolumeById($settings->volumeId) : null,
            'elementType' => Asset::class,
        ];

        return $this->renderTemplate('support/tickets/_new', $variables);
    }

    public function actionCreateTicket()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // First create ticket
        $ticket = new Ticket();
        $ticket->subject = $request->post('subject');
        $ticket->authorId = Craft::$app->getUser()->getIdentity()->id;

        $res = Craft::$app->getElements()->saveElement($ticket, true, false);

        if (!$res) {
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
            $message = new Message();
            $message->ticketId = $ticket->id;
            $message->authorId = Craft::$app->getUser()->getIdentity()->id;
            $message->attachmentIds = Json::encode($request->post('attachments'));
            $message->content = $request->post('message');

            $res = Craft::$app->getElements()->saveElement($message, true, false);

            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => true,
                ]);
            }

            Craft::$app->getSession()->setNotice('Ticket created.');

            return $this->redirectToPostedUrl();
        }
    }

    public function actionCreateMessage()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $ticketId = Craft::$app->security->validateData($request->post('ticketId'));

        // First check ticket exists
        $query = new TicketQuery(Ticket::class);
        $query->id = $ticketId;

        $ticket = $query->one();

        if (!$ticket) {
            Craft::$app->getSession()->setError('Couldn’t find the ticket.');
        } else {
            // Ticket exists, now create message
            $message = new Message();
            $message->ticketId = $ticket->id;
            $message->authorId = Craft::$app->getUser()->getIdentity()->id;
            $message->attachmentIds = Json::encode($request->post('attachments'));
            $message->content = $request->post('message');

            $res = Craft::$app->getElements()->saveElement($message, true, false);

            if (!$res) {
                Craft::$app->getSession()->setError('Couldn’t send the message.');
            } else {
                Craft::$app->getSession()->setNotice('Message sent.');
            }
        }

        return $this->redirectToPostedUrl();
    }
}
