<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support\models;

use lukeyouell\support\Support;
use lukeyouell\support\records\TicketStatus as TicketStatusRecord;

use Craft;
use craft\base\Model;
use craft\base\VolumeInterface;
use craft\helpers\UrlHelper;
use craft\validators\UniqueValidator;

class TicketStatus extends Model
{
    // Public Properties
    // =========================================================================

    public $id;

    public $name;

    public $handle;

    public $colour = 'green';

    public $sortOrder;

    public $default;

    public $newMessage;

    // Public Methods
    // =========================================================================

    public function __toString()
    {
        return (string) $this->name;
    }

    public function rules()
    {
        return [
            [['name', 'handle'], 'required'],
            [['handle'], UniqueValidator::class, 'targetClass' => TicketStatusRecord::class],
        ];
    }

    public function getEmails(): array
    {
        return $this->id ? Support::getInstance()->emailService->getAllEmailsByTicketStatusId($this->id) : [];
    }

    public function getEmailIds(): array
    {
        return array_column($this->getEmails(), 'id');
    }

    public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('support/settings/ticket-statuses/'.$this->id);
    }

    public function getLabelHtml(): string
    {
        $html  = '<div class="element small hasstatus">';
        $html .= '<span class="status '.$this->colour.'"></span>';
        $html .= '<div class="label"><span class="title">';
        $html .= '<a href="'.$this->getCpEditUrl().'">'.$this->name.'</a>';
        $html .='</span></div>';
        $html .= '</div>';

        return $html;
    }
}
