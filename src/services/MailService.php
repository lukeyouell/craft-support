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
use lukeyouell\support\services\TicketService;

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
    // Static Methods
    // =========================================================================

    public static function ticketCreation($ticketId = null)
    {
        if ($ticketId) {
            // Get ticket
            $ticket = TicketService::getTicketById($ticketId);

            if ($ticket) {
                $settings = Support::$plugin->getSettings();
                $system = Craft::$app->getInfo();

                // Prep
                $fromEmail = Craft::$app->systemSettings->getSetting('email', 'fromEmail');
                $fromName = Craft::$app->systemSettings->getSetting('email', 'fromName');
                $toEmails = is_string($settings->toEmail) ? StringHelper::split($settings->toEmail) : $settings->toEmail;
                $subject = '📥 A new support ticket has been created on '.$system->name;
                $cpUrl = UrlHelper::cpUrl('support/tickets/'.$ticket->id);

                // Set template
                $html = self::_setTemplate(
                    'support/_emails/ticketCreation',
                    [
                      'system' => $system,
                      'cpUrl' => $cpUrl,
                      'ticket' => $ticket,
                    ]
                );

                // Send email
                self::_sendEmail($fromEmail, $fromName, $toEmails, $subject, $html);
            }
        }
    }

    private static function _setTemplate($templateLocation = null, $variables = [])
    {
        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
        $html = Craft::$app->view->renderTemplate($templateLocation, $variables);
        $html = Markdown::process($html, 'gfm');

        return $html;
    }

    private static function _sendEmail($fromEmail = null, $fromName = null, $toEmails = null, $subject = null, $html = null)
    {
        $mailer = Craft::$app->getMailer();

        $message = (new Message())
            ->setFrom([$fromEmail => $fromName])
            ->setSubject($subject)
            ->setHtmlBody($html);

        foreach ($toEmails as $toEmail) {
            $message->setTo($toEmail);
            $mailer->send($message);
        }
    }
}
