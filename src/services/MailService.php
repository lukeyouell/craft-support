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
use lukeyouell\support\records\Email as EmailRecord;

use Craft;
use craft\base\Component;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\mail\Message;
use craft\web\View;

use yii\base\InvalidConfigException;
use yii\helpers\Markdown;

class MailService extends Component
{
    // Public Properties
    // =========================================================================

    public $settings;

    public $system;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        $this->settings = Support::$plugin->getSettings();
        $this->system = Craft::$app->systemSettings;
    }

    public function handleEmail($ticketId = null)
    {
        if ($ticketId) {
            $ticket = Support::getInstance()->ticketService->getTicketById($ticketId);

            if ($ticket->id) {
                $emails = $ticket->ticketStatus->emails;

                foreach ($emails as $email) {
                    if ($email->enabled) {
                        $this->sendEmail($email, $ticket);
                    }
                }
            }
        }
    }

    public function sendEmail($email, $ticket)
    {
        $mailer = Craft::$app->getMailer();

        $message = (new Message())
            ->setFrom([$this->getFromEmail() => $this->getFromName()])
            ->setSubject($this->getSubject($email, $ticket))
            ->setHtmlBody($this->getTemplateHtml($email, $ticket));

        $toEmails = $this->getToEmails($email, $ticket);

        foreach ($toEmails as $toEmail) {
            $message->setTo($toEmail);
            $mailer->send($message);
        }
    }

    public function getFromEmail()
    {
        return $this->settings->fromEmail ?: $this->system->getSetting('email', 'fromEmail');
    }

    public function getFromName()
    {
        return $this->settings->fromName ?: $this->system->getSetting('email', 'fromName');
    }

    public function getToEmails($email, $ticket)
    {
        $toEmail = '';

        if ($email->recipientType == EmailRecord::TYPE_AUTHOR) {
            $toEmail = $ticket->author->email;
        } elseif ($email->recipientType == EmailRecord::TYPE_CUSTOM) {
            $toEmail = $email->to;
        }

        return is_string($toEmail) ? StringHelper::split($toEmail) : $toEmail;
    }

    public function getSubject($email, $ticket)
    {
        // Replace keys with ticket values
		$subject = Craft::$app->getView()->renderObjectTemplate($email->subject, $ticket);
		
        return $subject;
    }

    public function getTemplateHtml($email, $ticket)
    {
        if ($email->templatePath) {
            $variables = [
                'ticket' => $ticket,
            ];

            // Set Craft to the site template mode
            $view = Craft::$app->getView();
            $oldTemplateMode = $view->getTemplateMode();
			// $view->setTemplateMode($view::TEMPLATE_MODE_SITE);
			$view->setTemplateMode($view::TEMPLATE_MODE_CP);

            // Render template
            $html = Craft::$app->view->renderTemplate($email->templatePath, $variables);

            // Set Craft back to the previous template mode
            $view->setTemplateMode($oldTemplateMode);

            return $html;
        }

        return null;
    }
}
