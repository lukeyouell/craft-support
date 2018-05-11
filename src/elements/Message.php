<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support\elements;

use lukeyouell\support\Support;

use Craft;
use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;

use lukeyouell\support\elements\db\MessageQuery;

class Message extends Element
{
    // Public Properties
    // =========================================================================

    public $ticketId;

    public $authorId;

    public $attachmentIds;

    public $content;

    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('support', 'Message');
    }

    public static function refHandle()
    {
        return 'message';
    }

    public static function hasContent(): bool
    {
        return true;
    }

    public static function hasTitles(): bool
    {
        return false;
    }

    public static function isLocalized(): bool
    {
        return true;
    }

    public static function hasStatuses(): bool
    {
        return false;
    }

    public static function find(): ElementQueryInterface
    {
        return new MessageQuery(static::class);
    }

    // Events
    // -------------------------------------------------------------------------

    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                ->insert('{{%support_messages}}', [
                    'id'            => $this->id,
                    'ticketId'      => $this->ticketId,
                    'authorId'      => $this->authorId,
                    'attachmentIds' => $this->attachmentIds,
                    'content'       => $this->content,
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update('{{%support_messages}}', [
                    'content' => $this->content,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }
}
