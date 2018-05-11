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
use lukeyouell\support\elements\Message;
use lukeyouell\support\elements\db\MessageQuery;
use lukeyouell\support\services\AttachmentService;
use lukeyouell\support\services\TicketService;

use Craft;
use craft\base\Component;

class MessageService extends Component
{
    // Static Methods
    // =========================================================================

    public static function getMessagesByTicketId($ticketId = null)
    {
        if ($ticketId) {
          $query = new MessageQuery(Message::class);
          $query->ticketId = $ticketId;

          $messages = $query->all();

          if ($messages) {
              // Convert attachment id's back to an array
              foreach ($messages as $message) {
                //$message->attachmentIds = AttachmentService::getMessageAttachments($message->attachmentIds);
              }

              return $messages;
          }
        }

        return null;
    }

    public static function createMessage($ticketId = null, $submission = null)
    {
        if ($ticketId and $submission) {
            $message = new Message();
            $message->ticketId = $ticketId;
            $message->authorId = Craft::$app->getUser()->getIdentity()->id;
            $message->attachmentIds = implode(',', $submission->post('attachments'));
            $message->content = $submission->post('message');

            $res = Craft::$app->getElements()->saveElement($message, true, false);

            if ($res) {
                // Save ticket to update the 'dateUpdated' value
                TicketService::saveTicketById($ticketId);

                return $message;
            }
        }

        return null;
    }
}
