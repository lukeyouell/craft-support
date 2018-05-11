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

use Craft;
use craft\base\Component;

class TicketService extends Component
{
    // Static Methods
    // =========================================================================

    public static function createTicket($submission = null) {
        if ($submission) {
          $ticket = new Ticket();
          $ticket->subject = $submission->post('subject');
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
        if ($ticketId) {
            $query = new TicketQuery(Ticket::class);
            $query->id = $ticketId;

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
