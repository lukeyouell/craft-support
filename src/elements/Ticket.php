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
	
    public $ticketPriorityId;

    public $authorId;

	public $_ticketStatus;
	
    public $_ticketPriority;

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
	
	public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['ticketPriorityId'], 'required'];

        return $rules;
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
        $canManageTickets = $userSessionService->checkPermission('support-manageTickets');

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

        $statuses = Support::getInstance()->ticketStatusService->getAllTicketStatuses();

        foreach ($statuses as $status) {
            $sources[] = [
                'key'         => 'status:'.$status['handle'],
                'status'      => $status['colour'],
                'label'       => $status['name'],
                'criteria'    => [
                    'authorId' => $canManageTickets ? '' : $userId,
                    'ticketStatusId' => $status['id'],
                ],
                'defaultSort' => ['dateCreated', 'desc'],
            ];
		}
		
		$sources[] = ['heading' => 'Ticket Priority'];

        $priorities = Support::getInstance()->ticketPriorityService->getAllTicketPriorities();

        foreach ($priorities as $priority) {
            $sources[] = [
                'key'         => 'priority:'.$priority['handle'],
                'status'      => $priority['colour'],
                'label'       => $priority['name'],
                'criteria'    => [
                    'authorId' => $canManageTickets ? '' : $userId,
                    'ticketPriorityId' => $priority['id'],
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
        $canDeleteTickets = $userSessionService->checkPermission('support-deleteTickets');

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
        $canManageTickets = $userSessionService->checkPermission('support-manageTickets');

        if ($canManageTickets) {
            $attributes = [
                'title'          => Craft::t('support', 'Title'),
                'ticketStatus'   => Craft::t('support', 'Status'),
                'ticketPriority' => Craft::t('support', 'Priority'),
                'author'         => Craft::t('support', 'Author'),
                'dateCreated'    => Craft::t('support', 'Date Created'),
                'dateUpdated'    => Craft::t('support', 'Date Updated'),
            ];
        } else {
            $attributes = [
                'title'          => Craft::t('support', 'Title'),
				'ticketStatus'   => Craft::t('support', 'Status'),
				'ticketPriority' => Craft::t('support', 'Priority'),
                'dateCreated'    => Craft::t('support', 'Date Created'),
                'dateUpdated'    => Craft::t('support', 'Date Updated'),
            ];
        }

        return $attributes;
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        $userSessionService = Craft::$app->getUser();
        $canManageTickets = $userSessionService->checkPermission('support-manageTickets');

        if ($canManageTickets) {
            $attributes = ['title', 'ticketStatus', 'ticketPriority', 'dateCreated', 'dateUpdated', 'author'];
        } else {
            $attributes = ['title', 'ticketStatus', 'ticketPriority', 'dateCreated', 'dateUpdated'];
        }

        return $attributes;
    }

    public function getTableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'ticketStatus':
                $status = $this->getTicketStatus();

				return '<span class="status '.$status['colour'].'"></span>'.$status['name'];
			case 'ticketPriority':
                $priority = $this->getTicketPriority();

                return '<span class="status '.$priority['colour'].'"></span>'.$priority['name'];
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
        $names[] = 'ticketPriority';
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

        $this->_ticketStatus = Support::getInstance()->ticketStatusService->getTicketStatusById($this->ticketStatusId);

        return $this->_ticketStatus;
	}
	
	public function getTicketPriority()
    {
        if ($this->_ticketPriority !== null) {
            return $this->_ticketPriority;
        }

        if ($this->ticketPriorityId === null) {
            return null;
        }

        $this->_ticketPriority = Support::getInstance()->ticketPriorityService->getTicketPriorityById($this->ticketPriorityId);

        return $this->_ticketPriority;
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

        $this->_messages = Support::getInstance()->messageService->getMessagesByTicketId($this->id);

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
                    'ticketPriorityId' => $this->ticketPriorityId,
                    'authorId'       => $this->authorId,
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update('{{%support_tickets}}', [
                    'ticketStatusId'  => $this->ticketStatusId,
                    'ticketPriorityId'  => $this->ticketPriorityId,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }
}
