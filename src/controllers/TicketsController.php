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
use craft\web\Controller;

class TicketsController extends Controller
{
    public function actionShowTicket(string $ticketId = null)
    {
        $query = new TicketQuery(Ticket::class);
        $query->id = $ticketId;

        $ticket = $query->one();

        $variables = [
            'ticket' => $ticket,
        ];

        return $this->renderTemplate('support/_ticket', $variables);
    }
}
