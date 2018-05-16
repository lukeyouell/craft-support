<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support\services;

use lukeyouell\support\Support;
use lukeyouell\support\elements\Ticket;
use lukeyouell\support\elements\db\TicketQuery;
use lukeyouell\support\services\TicketStatusService;

use Craft;
use craft\base\Component;

class TicketService extends Component
{
    // Static Methods
    // =========================================================================

    public static function createTicket($submission = null) {
        if ($submission) {
          $defaultTicketStatus = TicketStatusService::getDefaultTicketStatus();

          $ticket = new Ticket();
          $ticket->ticketStatusId = $defaultTicketStatus['id'];
          $ticket->title = $submission->post('title');
          $ticket->authorId = Craft::$app->getUser()->getIdentity()->id;

          $res = Craft::$app->getElements()->saveElement($ticket, true, false);

          if ($res) {
              return $ticket;
          }
        }

        return null;
    }

    public static function getTicketById($ticketId = null)
    {
        $userSessionService = Craft::$app->getUser();
        $userId = $userSessionService->getIdentity()->id;
        $canManageTickets = $userSessionService->checkPermission('support-manageTickets');

        if ($ticketId) {
            $query = new TicketQuery(Ticket::class);
            $query->id = $ticketId;
            $query->authorId = $canManageTickets ? null : $userId;

            return $query->one();
        }

        return null;
    }

    public static function saveTicketById($ticketId = null)
    {
        if ($ticketId) {
            $query = new TicketQuery(Ticket::class);
            $query->id = $ticketId;

            $ticket = $query->one();

            if ($ticket) {
                return Craft::$app->getElements()->saveElement($ticket, true, false);
            }
        }

        return null;
    }
}
