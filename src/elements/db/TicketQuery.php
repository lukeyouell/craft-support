<?php

namespace lukeyouell\support\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class TicketQuery extends ElementQuery
{
    public $subject;

    public $authorId;

    public $attachmentIds;

    public function subject($value)
    {
        $this->subject = $value;

        return $this;
    }

    public function authorId($value)
    {
        $this->authorId = $value;

        return $this;
    }

    public function attachmentIds($value)
    {
        $this->attachmentIds = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('support_tickets');

        // select the columns
        $this->query->select([
            'support_tickets.subject',
            'support_tickets.authorId',
            'support_tickets.attachmentIds',
        ]);

        if ($this->subject) {
            $this->subQuery->andWhere(Db::parseParam('support_tickets.subject', $this->subject));
        }

        if ($this->authorId) {
            $this->subQuery->andWhere(Db::parseParam('support_tickets.authorId', $this->authorId));
        }

        if ($this->attachmentIds) {
            $this->subQuery->andWhere(Db::parseParam('support_tickets.attachmentIds', $this->attachmentIds));
        }

        return parent::beforePrepare();
    }
}
