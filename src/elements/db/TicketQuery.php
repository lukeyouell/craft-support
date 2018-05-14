<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class TicketQuery extends ElementQuery
{
    public $ticketStatus;

    public $authorId;

    public function ticketStatus($value)
    {
        $this->ticketStatus = $value;

        return $this;
    }

    public function authorId($value)
    {
        $this->authorId = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('support_tickets');

        // select the columns
        $this->query->select([
            'support_tickets.ticketStatus',
            'support_tickets.authorId',
        ]);

        if ($this->ticketStatus) {
            $this->subQuery->andWhere(Db::parseParam('support_tickets.ticketStatus', $this->ticketStatus));
        }

        if ($this->authorId) {
            $this->subQuery->andWhere(Db::parseParam('support_tickets.authorId', $this->authorId));
        }

        return parent::beforePrepare();
    }
}
