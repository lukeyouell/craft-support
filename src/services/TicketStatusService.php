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

use Craft;
use craft\base\Component;
use craft\db\Query;

class TicketStatusService extends Component
{
    // Static Methods
    // =========================================================================

    public static function getAllTicketStatuses()
    {
      return (new Query())
          ->orderBy('sortOrder')
          ->from(['{{%support_ticketstatuses}}'])
          ->all();
    }

    public static function getTicketStatusById($id)
    {
      return (new Query())
          ->where(['id' => $id])
          ->from(['{{%support_ticketstatuses}}'])
          ->one();
    }

    public static function getDefaultTicketStatus()
    {
      return (new Query())
          ->where(['default' => 1])
          ->from(['{{%support_ticketstatuses}}'])
          ->one();
    }

    public static function reorderTicketStatuses(array $ids): bool
    {
        foreach ($ids as $sortOrder => $id) {
            Craft::$app->getDb()->createCommand()
                ->update('{{%support_ticketstatuses}}', ['sortOrder' => $sortOrder + 1], ['id' => $id])
                ->execute();
        }

        return true;
    }
}
