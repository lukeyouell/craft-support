<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support\services;

use lukeyouell\support\Support;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\elements\db\AssetQuery;
use craft\helpers\Json;

class AttachmentService extends Component
{
    // Static Methods
    // =========================================================================

    public static function getMessageAttachments($attachmentIds = null)
    {
        if ($attachmentIds) {
          // Convert json to array of integers
          $ids = Json::decodeIfJson($attachmentIds);
          //$ids = array_map('intval', $ids);

          return $ids;
        }

        return null;
    }
}
