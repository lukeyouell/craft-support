<?php

namespace lukeyouell\support\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class TicketQuery extends ElementQuery
{
    public $subject;

    public function subject($value)
    {
        $this->subject = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('support_tickets');

        // select the columns
        $this->query->select([
            'support_tickets.subject',
        ]);

        if ($this->subject) {
            $this->subQuery->andWhere(Db::parseParam('support_tickets.subject', $this->subject));
        }

        return parent::beforePrepare();
    }
}
