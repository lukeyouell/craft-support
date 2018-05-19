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
use lukeyouell\support\records\Email as EmailRecord;

use Craft;
use craft\base\Model;
use craft\helpers\UrlHelper;

class Email extends Model
{
    // Public Properties
    // =========================================================================

    public $id;

    public $name;

    public $subject;

    public $recipientType;

    public $to;

    public $bcc;

    public $templatePath;

    public $sortOrder;

    public $enabled;

    // Public Methods
    // =========================================================================

    public function __toString()
    {
        return (string) $this->name;
    }

    public function rules()
    {
        return [
            [['name', 'subject'], 'required'],
            [['recipientType'], 'in', 'range' => [EmailRecord::TYPE_AUTHOR, EmailRecord::TYPE_CUSTOM]],
        ];
    }

    public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('support/settings/emails/'.$this->id);
    }

    public function getLabelHtml(): string
    {
        $html  = '<div class="element small hasstatus">';
        if ($this->enabled) {
            $html .= '<span class="status green"></span>';
        } else {
            $html .= '<span class="status"></span>';
        }
        $html .= '<div class="label"><span class="title">';
        $html .= '<a href="'.$this->getCpEditUrl().'">'.$this->name.'</a>';
        $html .='</span></div>';
        $html .= '</div>';

        return $html;
    }
}
