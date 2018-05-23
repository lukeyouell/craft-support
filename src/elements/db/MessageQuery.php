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

class MessageQuery extends ElementQuery
{
    public $ticketId;

    public $authorId;

    public $attachmentIds;

    public $content;

    public function ticketId($value)
    {
        $this->ticketId = $value;

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

    public function content($value)
    {
        $this->content = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('support_messages');

        // select the columns
        $this->query->select([
            'support_messages.ticketId',
            'support_messages.authorId',
            'support_messages.attachmentIds',
            'support_messages.content',
        ]);

        if ($this->ticketId) {
            $this->subQuery->andWhere(Db::parseParam('support_messages.ticketId', $this->ticketId));
        }

        if ($this->authorId) {
            $this->subQuery->andWhere(Db::parseParam('support_messages.authorId', $this->authorId));
        }

        if ($this->attachmentIds) {
            $this->subQuery->andWhere(Db::parseParam('support_messages.attachmentIds', $this->attachmentIds));
        }

        if ($this->content) {
            $this->subQuery->andWhere(Db::parseParam('support_messages.content', $this->content));
        }

        return parent::beforePrepare();
    }
}
