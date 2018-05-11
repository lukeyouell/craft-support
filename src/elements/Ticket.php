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
use lukeyouell\support\elements\db\TicketQuery;
use lukeyouell\support\services\TicketStatusService;

use Craft;
use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;

class Ticket extends Element
{
    // Public Properties
    // =========================================================================

    public $status;

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
        return true;
    }

    public static function isLocalized(): bool
    {
        return true;
    }

    public static function find(): ElementQueryInterface
    {
        return new TicketQuery(static::class);
    }

    protected static function defineSources(string $context = null): array
    {
        $sources = [
            '*' => [
                'key'         => '*',
                'label'       => 'All Tickets',
                'criteria'    => [],
                'defaultSort' => ['dateCreated', 'desc'],
            ],
        ];

        $sources[] = [
            'key'         => 'myTickets',
            'label'       => 'My Tickets',
            'criteria'    => ['authorId' => Craft::$app->getUser()->getIdentity()->id],
            'defaultSort' => ['dateCreated', 'desc'],
        ];

        $sources[] = ['heading' => 'Ticket Status'];

        $statuses = TicketStatusService::getStatuses();

        foreach ($statuses as $status) {
            $sources[] = [
                'key'         => 'status:'.$status['handle'],
                'status'      => $status['colour'],
                'label'       => $status['name'],
                'criteria'    => ['status' => $status['handle']],
                'defaultSort' => ['dateCreated', 'desc'],
            ];
        }

        return $sources;
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['title', 'status'];
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
            'title'       => Craft::t('support', 'Title'),
            'status'      => Craft::t('support', 'Status'),
            'dateCreated' => Craft::t('support', 'Date Created'),
            'dateUpdated' => Craft::t('support', 'Date Updated'),
        ];

        return $attributes;
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = ['title', 'status', 'dateCreated', 'dateUpdated'];

        return $attributes;
    }

    public function getTableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'status':
                {
                    $status = TicketStatusService::getStatusByHandle($this->status);
                    return '<span class="status '.$status['colour'].'"></span>'.$status['name'];
                }
            default:
                {
                    return parent::tableAttributeHtml($attribute);
                }
        }
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
                    'id'       => $this->id,
                    'status'   => $this->status,
                    'authorId' => $this->authorId,
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update('{{%support_tickets}}', [
                    'status'  => $this->status,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }
}
