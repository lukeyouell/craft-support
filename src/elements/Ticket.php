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

/**
 * Ticket Element
 *
 * Element is the base class for classes representing elements in terms of objects.
 *
 * @property FieldLayout|null      $fieldLayout           The field layout used by this element
 * @property array                 $htmlAttributes        Any attributes that should be included in the element’s DOM representation in the Control Panel
 * @property int[]                 $supportedSiteIds      The site IDs this element is available in
 * @property string|null           $uriFormat             The URI format used to generate this element’s URL
 * @property string|null           $url                   The element’s full URL
 * @property \Twig_Markup|null     $link                  An anchor pre-filled with this element’s URL and title
 * @property string|null           $ref                   The reference string to this element
 * @property string                $indexHtml             The element index HTML
 * @property bool                  $isEditable            Whether the current user can edit the element
 * @property string|null           $cpEditUrl             The element’s CP edit URL
 * @property string|null           $thumbUrl              The URL to the element’s thumbnail, if there is one
 * @property string|null           $iconUrl               The URL to the element’s icon image, if there is one
 * @property string|null           $status                The element’s status
 * @property Element               $next                  The next element relative to this one, from a given set of criteria
 * @property Element               $prev                  The previous element relative to this one, from a given set of criteria
 * @property Element               $parent                The element’s parent
 * @property mixed                 $route                 The route that should be used when the element’s URI is requested
 * @property int|null              $structureId           The ID of the structure that the element is associated with, if any
 * @property ElementQueryInterface $ancestors             The element’s ancestors
 * @property ElementQueryInterface $descendants           The element’s descendants
 * @property ElementQueryInterface $children              The element’s children
 * @property ElementQueryInterface $siblings              All of the element’s siblings
 * @property Element               $prevSibling           The element’s previous sibling
 * @property Element               $nextSibling           The element’s next sibling
 * @property bool                  $hasDescendants        Whether the element has descendants
 * @property int                   $totalDescendants      The total number of descendants that the element has
 * @property string                $title                 The element’s title
 * @property string|null           $serializedFieldValues Array of the element’s serialized custom field values, indexed by their handles
 * @property array                 $fieldParamNamespace   The namespace used by custom field params on the request
 * @property string                $contentTable          The name of the table this element’s content is stored in
 * @property string                $fieldColumnPrefix     The field column prefix this element’s content uses
 * @property string                $fieldContext          The field context this element’s content uses
 *
 * http://pixelandtonic.com/blog/craft-element-types
 *
 * @author    Luke Youell
 * @package   Support
 * @since     1.0.0
 */
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

    public $attachmentIds;

    public $authorId;

    public $assigneeId;

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
                    'attachmentIds' => $this->attachmentIds,
                    'assigneeId'    => $this->assigneeId,
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update('{{%support_tickets}}', [
                    'subject'   => $this->subject,
                    'attachmentIds' => $this->attachmentIds,
                    'assigneeId'    => $this->assigneeId,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }
}
