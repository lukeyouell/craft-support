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

use lukeyouell\support\elements\db\TicketQuery;

class Ticket extends Element
{
    // Constants
    // =========================================================================

    const STATUS_NEW = 'new';
    const STATUS_IN_PROGRESS = 'inProgress';
    const STATUS_SOLVED = 'solved';
    const STATUS_CLOSED = 'closed';

    // Public Properties
    // =========================================================================

    public $subject;

    public $authorId;

    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('support', 'Ticket');
    }

    public static function refHandle()
    {
        return 'ticket';
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
        return true;
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => Craft::t('support', 'New'),
            self::STATUS_IN_PROGRESS => Craft::t('support', 'In Progress'),
            self::STATUS_SOLVED => Craft::t('support', 'Solved'),
            self::STATUS_CLOSED => Craft::t('support', 'Closed')
        ];
    }

    public static function find(): ElementQueryInterface
    {
        return new TicketQuery(static::class);
    }

    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key'      => '*',
                'label'    => 'All tickets',
                'criteria' => [],
            ],
        ];

        return $sources;
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['subject'];
    }

    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $actions[] = Craft::$app->getElements()->createAction([
            'type'                => Delete::class,
            'confirmationMessage' => Craft::t('support', 'Are you sure you want to delete the selected tickets?'),
            'successMessage'      => Craft::t('support', 'Tickets deleted.'),
        ]);

        return $actions;
    }

    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'id'     => Craft::t('support', 'ID'),
            'subject'     => Craft::t('support', 'Subject'),
            'dateCreated' => Craft::t('support', 'Date Created'),
            'dateUpdated' => Craft::t('support', 'Date Updated'),
        ];

        return $attributes;
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = ['id', 'subject', 'dateCreated', 'dateUpdated'];

        return $attributes;
    }

    // Public Methods
    // =========================================================================

    public function getIsEditable(): bool
    {
        return false;
    }

    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('support/tickets/'.$this->id);
    }

    // Indexes, etc.
    // -------------------------------------------------------------------------

    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'subject':
                $author = $this->subject;
        }
        return parent::tableAttributeHtml($attribute);
    }

    protected static function defineSortOptions(): array
    {
        $sortOptions = [
            'support_tickets.dateCreated' => 'Date Created',
            'support_tickets.dateUpdated' => 'Date Updated',
        ];

        return $sortOptions;
    }

    // Events
    // -------------------------------------------------------------------------

    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                ->insert('{{%support_tickets}}', [
                    'id'            => $this->id,
                    'subject'       => $this->subject,
                    'authorId'      => $this->authorId,
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update('{{%support_tickets}}', [
                    'subject'   => $this->subject,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }
}
