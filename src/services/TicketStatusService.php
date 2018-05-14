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

class TicketStatusService extends Component
{
    // Static Methods
    // =========================================================================

    public static function getStatuses()
    {
        return [
            [
                'value' => 'new',
                'label'   => 'New',
                'colour' => 'blue',
            ],
            [
                'value' => 'in-progress',
                'label'   => 'In Progress',
                'colour' => 'orange',
            ],
            [
                'value' => 'solved',
                'label'   => 'Solved',
                'colour' => 'green',
            ],
            [
                'value' => 'closed',
                'label'   => 'Closed',
                'colour' => 'red',
            ],
            [
                'value' => 'archived',
                'label'   => 'Archived',
                'colour' => 'grey',
            ],
        ];
    }

    public static function getStatusByValue($value = null)
    {
        if ($value) {
            $statuses = self::getStatuses();

            foreach ($statuses as $status) {
                if ($status['value'] === $value) {
                    return $status;
                }
            }
        }

        return null;
    }
}
