<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support\variables;

use lukeyouell\support\Support;
use lukeyouell\support\elements\Message;
use lukeyouell\support\elements\Ticket;
use lukeyouell\support\elements\db\MessageQuery;
use lukeyouell\support\elements\db\TicketQuery;

use Craft;

class SupportVariable
{
    // Public Methods
    // =========================================================================

    public function tickets(array $criteria = []): TicketQuery
    {
        $query = Ticket::find();
        Craft::configure($query, $criteria);

        return $query;
    }

    public function messages(array $criteria = []): MessageQuery
    {
        $query = Message::find();
        Craft::configure($query, $criteria);

        return $query;
    }
}
