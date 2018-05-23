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
use lukeyouell\support\models\Email as EmailModel;
use lukeyouell\support\records\Email as EmailRecord;

use Craft;
use craft\base\Component;
use craft\db\Query;

use LitEmoji\LitEmoji;

use yii\base\Exception;

class EmailService extends Component
{
    // Public Methods
    // =========================================================================

    public function getAllEmails()
    {
        $rows = $this->_createEmailQuery()->all();

        $emails = [];

        foreach ($rows as $row) {
            $emails[] = new EmailModel($row);
        }

        return $emails;
    }

    public function getAllEmailsByTicketStatusId(int $id): array
    {
        $results = $this->_createEmailQuery()
            ->innerJoin('{{%support_ticketstatus_emails}} statusEmails', '[[emails.id]] = [[statusEmails.emailId]]')
            ->innerJoin('{{%support_ticketstatuses}} ticketStatuses', '[[statusEmails.ticketStatusId]] = [[ticketStatuses.id]]')
            ->where(['ticketStatuses.id' => $id])
            ->all();

        $emails = [];

        foreach ($results as $row) {
            $emails[] = new EmailModel($row);
        }

        return $emails;
    }

    public function getEmailById($id)
    {
        $result = $this->_createEmailQuery()
            ->where(['id' => $id])
            ->one();

        return new EmailModel($result);
    }

    public function saveEmail(EmailModel $model, bool $runValidation = true)
    {
        if ($model->id) {
            $record = EmailRecord::findOne($model->id);

            if (!$record) {
                throw new Exception(Craft::t('support', 'No email exists with the ID "{id}"', ['id' => $model->id]));
            }
        } else {
            $record = new EmailRecord();
        }

        if ($runValidation && !$model->validate()) {
            Craft::info('Email not saved due to a validation error.', __METHOD__);

            return false;
        }

        $record->name          = $model->name;
        $record->subject       = LitEmoji::unicodeToShortcode($model->subject);
        $record->recipientType = $model->recipientType;
        $record->to            = $model->to;
        $record->bcc           = $model->bcc;
        $record->templatePath  = $model->templatePath;
        $record->sortOrder     = $model->sortOrder ?: 999;
        $record->enabled       = $model->enabled;

        // Save it
        $record->save(false);

        // Now that we have a record ID, save it on the model
        $model->id = $record->id;

        return true;
    }

    public function reorderEmails(array $ids)
    {
        foreach ($ids as $sortOrder => $id) {
            Craft::$app->getDb()->createCommand()
                ->update('{{%support_emails}}', ['sortOrder' => $sortOrder + 1], ['id' => $id])
                ->execute();
        }

        return true;
    }

    public function deleteEmailById($id)
    {
        $email = EmailRecord::findOne($id);

        if ($email) {
            return $email->delete();
        }

        return false;
    }

    // Private Methods
    // =========================================================================

    private function _createEmailQuery()
    {
        return (new Query())
            ->select([
                'emails.id',
                'emails.name',
                'emails.subject',
                'emails.recipientType',
                'emails.to',
                'emails.bcc',
                'emails.templatePath',
                'emails.sortOrder',
                'emails.enabled',
            ])
            ->orderBy('sortOrder')
            ->from(['{{%support_emails}} emails']);
    }
}
