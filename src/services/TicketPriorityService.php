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
use lukeyouell\support\elements\Ticket;
// use lukeyouell\support\models\Email as EmailModel;
use lukeyouell\support\models\TicketPriority as TicketPriorityModel;
// use lukeyouell\support\records\Email as EmailRecord;
use lukeyouell\support\records\TicketPriority as TicketPriorityRecord;
// use lukeyouell\support\records\TicketPriorityEmail as TicketPriorityEmailRecord;

use Craft;
use craft\base\Component;
use craft\db\Query;

use yii\base\Exception;

class TicketPriorityService extends Component
{

    // Properties
    // =========================================================================

    private $_fetchedAllPriorities = false;

    private $_ticketPrioritiesById = [];

    private $_ticketPrioritiesByHandle = [];

    // Public Methods
    // =========================================================================

    public function getAllTicketPriorities()
    {
        if (!$this->_fetchedAllPriorities) {
            $results = $this->_createTicketPriorityQuery()->all();

            foreach ($results as $row) {
                $this->_memoizeTicketPriority(new TicketPriorityModel($row));
            }

            $this->_fetchedAllPriorities = true;
        }

        return $this->_ticketPrioritiesById;
    }

    public function getTicketPriorityById($id)
    {
        $result = $this->_createTicketPriorityQuery()
            ->where(['id' => $id])
            ->one();

        return new TicketPriorityModel($result);
    }

    public function getDefaultTicketPriority()
    {
        $result = $this->_createTicketPriorityQuery()
            ->where(['default' => 1])
            ->one();

        return new TicketPriorityModel($result);
    }

    public function checkIfTicketPriorityInUse($id)
    {
        $result = Ticket::find()
            ->TicketPriorityId($id)
            ->one();

        return $result;
    }

    public function reorderTicketPriorities(array $ids)
    {
        foreach ($ids as $sortOrder => $id) {
            Craft::$app->getDb()->createCommand()
                ->update('{{%support_ticketpriorities}}', ['sortOrder' => $sortOrder + 1], ['id' => $id])
                ->execute();
        }

        return true;
    }

    public function saveTicketPriority(TicketPriorityModel $model, bool $runValidation = true)
    {
        if ($model->id) {
            $record = TicketPriorityRecord::findOne($model->id);

            if (!$record->id) {
                throw new Exception(Craft::t('support', 'No ticket priority exists with the ID "{id}"',
                    ['id' => $model->id]));
            }
        } else {
            $record = new TicketPriorityRecord();
        }

        if ($runValidation && !$model->validate()) {
            Craft::info('Ticket priority not saved due to a validation error.', __METHOD__);

            return false;
        }

        $record->name = $model->name;
        $record->handle = $model->handle;
        $record->colour = $model->colour;
        $record->sortOrder = $model->sortOrder ?: 999;
        $record->default = $model->default;

        // Validate email ids
        // $exist = EmailRecord::find()->where(['in', 'id', $emailIds])->exists();
        // $hasEmails = (boolean) count($emailIds);

        // if (!$exist && $hasEmails) {
        //     $model->addError('emails', 'One or more emails do not exist in the system.');
        // }

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            // Only one default priority can be among priorities
            if ($record->default) {
                TicketPriorityRecord::updateAll(['default' => 0]);
            }

            // Save it
            $record->save(false);

			/*
            // Delete old email links
            if ($model->id) {
                $rows = TicketPriorityEmailRecord::find()->where(['TicketPriorityId' => $model->id])->all();

                foreach ($rows as $row) {
                    $row->delete();
                }
            }

            // Save new email links
            $rows = array_map(
                function ($id) use ($record) {
                    return [$id, $record->id];
                }, $emailIds);

            $cols = ['emailId', 'TicketPriorityId'];
            $table = TicketPriorityEmailRecord::tableName();
			Craft::$app->getDb()->createCommand()->batchInsert($table, $cols, $rows)->execute();
			*/

            // Now that we have a record ID, save it on the model
            $model->id = $record->id;

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    public function deleteTicketPrioritybyId($id)
    {
        $priorities = $this->getAllTicketPriorities();

        $existingTicket = $this->checkIfTicketPriorityInUse($id);

        // Don't delete if it's still in use
        if ($existingTicket) {
            return false;
        }

        // Don't delete if it's the only priority left
        if (count($priorities) > 1) {
            $record = TicketPriorityRecord::findOne($id);

            return $record->delete();
        }

        return false;
    }

    // Private Methods
    // =========================================================================

    private function _memoizeTicketPriority(TicketPriorityModel $TicketPriority)
    {
        $this->_ticketPrioritiesById[$TicketPriority->id] = $TicketPriority;
        $this->_ticketPrioritiesByHandle[$TicketPriority->handle] = $TicketPriority;
    }

    private function _createTicketPriorityQuery()
    {
        return (new Query())
            ->select([
                'id',
                'name',
                'handle',
                'colour',
                'sortOrder',
                'default',
            ])
            ->orderBy('sortOrder')
            ->from(['{{%support_ticketpriorities}}']);
    }
}
