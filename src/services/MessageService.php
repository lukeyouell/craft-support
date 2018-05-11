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
use lukeyouell\support\services\TicketService;

use Craft;
use craft\base\Component;

class MessageService extends Component
{
    // Static Methods
    // =========================================================================

    public static function getMessageById($messageId = null)
    {
        if ($messageId) {
          $query = new MessageQuery(Message::class);
          $query->id = $messageId;

          return $query->one();
        }

        return null;
    }

    public static function getMessagesByTicketId($ticketId = null)
    {
        if ($ticketId) {
          $query = new MessageQuery(Message::class);
          $query->ticketId = $ticketId;

          return $query->all();
        }

        return null;
    }

    public static function createMessage($ticketId = null, $submission = null)
    {
        if ($ticketId and $submission) {
            $message = new Message();
            $message->ticketId = $ticketId;
            $message->authorId = Craft::$app->getUser()->getIdentity()->id;
            $message->attachmentIds = $submission->post('attachments') ? implode(',', $submission->post('attachments')) : null;
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

    public static function deleteMessage($messageId = null)
    {
        if ($messageId) {
            $message = self::getMessageById($messageId);

            if ($message) {
                // Check user is message author
                $owner = self::isMessageAuthor($message->authorId, Craft::$app->getUser()->getIdentity()->id);

                if ($owner) {
                    Craft::$app->getElements()->deleteElement($message);

                    return true;
                }
            }
        }

        return null;
    }

    public static function isMessageAuthor($authorId = null, $userId = null)
    {
        if ($authorId and $userId) {
            if ($authorId === $userId) {
                return true;
            }
        }

        return false;
    }
}
