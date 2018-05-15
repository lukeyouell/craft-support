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
    public $ticketStatusId;

    public $authorId;

    public function ticketStatusId($value)
    {
        $this->ticketStatusId = $value;

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
            'support_tickets.ticketStatusId',
            'support_tickets.authorId',
        ]);

        if ($this->ticketStatusId) {
            $this->subQuery->andWhere(Db::parseParam('support_tickets.ticketStatusId', $this->ticketStatusId));
        }

        if ($this->authorId) {
            $this->subQuery->andWhere(Db::parseParam('support_tickets.authorId', $this->authorId));
        }

        return parent::beforePrepare();
    }
}
