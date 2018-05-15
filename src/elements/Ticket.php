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
use lukeyouell\support\services\MessageService;
use lukeyouell\support\services\TicketStatusService;

use Craft;
use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;

use yii\base\Exception;
use yii\base\InvalidConfigException;

class Ticket extends Element
{
    // Public Properties
    // =========================================================================

    public $ticketStatusId;

    public $authorId;

    public $_ticketStatus;

    public $_author;

    public $_messages;

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

    public static function hasStatuses(): bool
    {
        return false;
    }

    public static function statuses(): array
    {
        return [];
    }

    public static function find(): ElementQueryInterface
    {
        return new TicketQuery(static::class);
    }

    protected static function defineSources(string $context = null): array
    {
        $userSessionService = Craft::$app->getUser();
        $userId = $userSessionService->getIdentity()->id;
        $canManageTickets = $userSessionService->checkPermission('manageTickets');

        $sources = [
            '*' => [
                'key'         => '*',
                'label'       => 'All Tickets',
                'criteria'    => [
                    'authorId' => $canManageTickets ? '' : $userId,
                ],
                'defaultSort' => ['dateCreated', 'desc'],
            ],
        ];

        $sources[] = [
            'key'         => 'myTickets',
            'label'       => 'My Tickets',
            'criteria'    => [
                'authorId' => $userId,
            ],
            'defaultSort' => ['dateCreated', 'desc'],
        ];

        $sources[] = ['heading' => 'Ticket Status'];

        $statuses = TicketStatusService::getStatuses();

        foreach ($statuses as $status) {
            $sources[] = [
                'key'         => 'status:'.$status['value'],
                'status'      => $status['colour'],
                'label'       => $status['label'],
                'criteria'    => [
                    'authorId' => $canManageTickets ? '' : $userId,
                    'ticketStatusId' => $status['value'],
                ],
                'defaultSort' => ['dateCreated', 'desc'],
            ];
        }

        return $sources;
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['title', 'ticketStatusId'];
    }

    protected static function defineActions(string $source = null): array
    {
        $userSessionService = Craft::$app->getUser();
        $canDeleteTickets = $userSessionService->checkPermission('deleteTickets');

        $actions = [];

        if ($canDeleteTickets) {
            $actions[] = Craft::$app->getElements()->createAction([
                'type'                => Delete::class,
                'confirmationMessage' => Craft::t('support', 'Are you sure you want to delete the selected tickets?'),
                'successMessage'      => Craft::t('support', 'Tickets deleted.'),
            ]);
        }

        return $actions;
    }

    protected static function defineTableAttributes(): array
    {
        $userSessionService = Craft::$app->getUser();
        $canManageTickets = $userSessionService->checkPermission('manageTickets');

        if ($canManageTickets) {
            $attributes = [
                'title'          => Craft::t('support', 'Title'),
                'ticketStatus'   => Craft::t('support', 'Status'),
                'author'         => Craft::t('support', 'Author'),
                'dateCreated'    => Craft::t('support', 'Date Created'),
                'dateUpdated'    => Craft::t('support', 'Date Updated'),
            ];
        } else {
            $attributes = [
                'title'        => Craft::t('support', 'Title'),
                'ticketStatus' => Craft::t('support', 'Status'),
                'dateCreated'  => Craft::t('support', 'Date Created'),
                'dateUpdated'  => Craft::t('support', 'Date Updated'),
            ];
        }

        return $attributes;
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        $userSessionService = Craft::$app->getUser();
        $canManageTickets = $userSessionService->checkPermission('manageTickets');

        if ($canManageTickets) {
            $attributes = ['title', 'ticketStatus', 'dateCreated', 'dateUpdated', 'author'];
        } else {
            $attributes = ['title', 'ticketStatus', 'dateCreated', 'dateUpdated'];
        }

        return $attributes;
    }

    public function getTableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'ticketStatus':
                $status = $this->getTicketStatus();

                return '<span class="status '.$status['colour'].'"></span>'.$status['label'];
            case 'author':
                $author = $this->getAuthor();

                return $author ? Craft::$app->getView()->renderTemplate('_elements/element', ['element' => $author]) : '';
            default:
                {
                    return parent::tableAttributeHtml($attribute);
                }
        }
    }

    // Public Methods
    // =========================================================================

    public function extraFields()
    {
        $names = parent::extraFields();
        $names[] = 'ticketStatus';
        $names[] = 'author';
        $names[] = 'messages';
        return $names;
    }

    public function getIsEditable(): bool
    {
        return false;
    }

    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('support/tickets/'.$this->id);
    }

    public function getTicketStatus()
    {
        if ($this->_ticketStatus !== null) {
            return $this->_ticketStatus;
        }

        if ($this->ticketStatusId === null) {
            return null;
        }

        $this->_ticketStatus = TicketStatusService::getStatusByValue($this->ticketStatusId);

        return $this->_ticketStatus;
    }

    public function getAuthor()
    {
        if ($this->_author !== null) {
            return $this->_author;
        }

        if ($this->authorId === null) {
            return null;
        }

        if (($this->_author = Craft::$app->getUsers()->getUserById($this->authorId)) === null) {
            throw new InvalidConfigException('Invalid author ID: '.$this->authorId);
        }

        return $this->_author;
    }

    public function getMessages()
    {
        if ($this->_messages !== null) {
            return $this->_messages;
        }

        $this->_messages = MessageService::getMessagesByTicketId($this->id);

        return $this->_messages;
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
                    'id'             => $this->id,
                    'ticketStatusId' => $this->ticketStatusId,
                    'authorId'       => $this->authorId,
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update('{{%support_tickets}}', [
                    'ticketStatusId'  => $this->ticketStatusId,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }
}
