<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support\controllers;

use lukeyouell\support\Support;
use lukeyouell\support\models\Email as EmailModel;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;

use yii\base\InvalidConfigException;
use yii\web\Response;

class EmailsController extends Controller
{
    // Public Properties
    // =========================================================================

    public $settings;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        $this->settings = Support::$plugin->getSettings();
        if (!$this->settings->validate()) {
            throw new InvalidConfigException('Support settings don’t validate.');
        }
    }

    public function actionIndex()
    {
        $settings = $this->settings;
        $emails = Support::getInstance()->emailService->getAllEmails();

        $variables = [
            'settings' => $settings,
            'emails'   => $emails,
        ];

        return $this->renderTemplate('support/_settings/emails/index', $variables);
    }

    public function actionEdit(int $id = null, EmailModel $email = null)
    {
        $variables = [
            'id'    => $id,
            'email' => $email,
        ];

        if (!$variables['email']) {
            if ($variables['id']) {
                $variables['email'] = Support::getInstance()->emailService->getEmailById($variables['id']);

                if (!$variables['email']) {
                    throw new NotFoundHttpException('Email not found');
                }
            } else {
                $variables['email'] = new EmailModel();
            }
        }

        if ($variables['email']->id) {
            $variables['title'] = $variables['email']->name;
        } else {
            $variables['title'] = 'Create a new email';
        }

        return $this->renderTemplate('support/_settings/emails/edit', $variables);
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $id = $request->post('id');
        $email = Support::getInstance()->emailService->getEmailById($id);

        if (!$email) {
            $email = new EmailModel();
        }

        $email->name = $request->post('name');
        $email->subject = $request->post('subject');
        $email->recipientType = $request->post('recipientType');
        $email->to = $request->post('to');
        $email->bcc = $request->post('bcc');
        $email->templatePath = $request->post('templatePath');
        $email->enabled = $request->post('enabled');

        // Save it
        $save = Support::getInstance()->emailService->saveEmail($email);

        if ($save) {
            Craft::$app->getSession()->setNotice('Email saved.');

            $this->redirectToPostedUrl();
        } else {
            Craft::$app->getSession()->setError('Couldn’t save email.');
        }

        Craft::$app->getUrlManager()->setRouteParams(compact('email'));
    }

    public function actionReorder(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $ids = Json::decode(Craft::$app->getRequest()->getRequiredBodyParam('ids'));

        if ($success = Support::getInstance()->emailService->reorderEmails($ids)) {
            return $this->asJson(['success' => $success]);
        }

        return $this->asJson(['error' => 'Couldn’t reorder emails.']);
    }

    public function actionDelete()
    {
        $this->requireAcceptsJson();

        $emailId = Craft::$app->getRequest()->getRequiredParam('id');

        if ($success = Support::getInstance()->emailService->deleteEmailById($emailId)) {
            return $this->asJson(['success' => true]);
        }

        return $this->asJson(['error' => 'Couldn’t delete email.']);
    }
}
