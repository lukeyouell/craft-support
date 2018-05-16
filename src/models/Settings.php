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

use Craft;
use craft\base\Model;
use craft\base\VolumeInterface;

/**
 * Support Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Luke Youell
 * @package   Support
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $fromEmail;

    public $fromName;

    public $email = true;

    public $toEmail;

    public $attachments = false;

    public $volumeId;

    public $volumeSubpath = 'attachments/{id}';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['email', 'attachments'], 'boolean'],
            [['fromEmail', 'fromName', 'toEmail', 'volumeSubpath'], 'string'],
            [['volumeId'], 'number', 'integerOnly' => true]
        ];
    }
}
