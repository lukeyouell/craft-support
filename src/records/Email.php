<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support\records;

use craft\db\ActiveRecord;

class Email extends ActiveRecord
{
    // Constants
    // =========================================================================

    const TYPE_AUTHOR = 'author';

    const TYPE_CUSTOM = 'custom';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%support_emails}}';
    }
}
