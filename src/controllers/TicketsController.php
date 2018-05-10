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
use lukeyouell\support\elements\Ticket;
use lukeyouell\support\elements\db\TicketQuery;

use Craft;
use craft\elements\Asset;
use craft\elements\User;
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

        $variables = [
            'ticket' => $ticket,
            'author' => $ticket->authorId ? Craft::$app->users->getUserById($ticket->authorId) : null,
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

        $ticket = new Ticket();
        $ticket->subject = $request->post('subject');
        $ticket->authorId = Craft::$app->getUser()->getIdentity()->id;
        $ticket->attachmentIds = Json::encode($request->post('attachments'));

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
