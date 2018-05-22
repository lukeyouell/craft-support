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

use yii\web\NotFoundHttpException;

class TicketService extends Component
{
    // Public Methods
    // =========================================================================

    public function createTicket($submission = null) {
        if ($submission) {
          $defaultTicketStatus = Support::getInstance()->ticketStatusService->getDefaultTicketStatus();

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

    public function getTicketById($ticketId = null)
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

    public function changeTicketStatus($ticket = null, $ticketStatusId = null)
    {
        if ($ticket->id && $ticketStatusId) {
            $status = Support::getInstance()->ticketStatusService->getTicketStatusById($ticketStatusId);

            if (!$status->id) {
                throw new NotFoundHttpException('Ticket status not found');
            }

            $ticket->ticketStatusId = $status->id;

            Craft::$app->getElements()->saveElement($ticket, false);

            // Handle ticket status emails after saving ticket
            if ($status->emails) {
                Support::getInstance()->mailService->handleEmail($ticket);
            }

            return true;
        }

        return false;
    }

    public function saveTicketById($ticketId = null)
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
