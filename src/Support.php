<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support;

use lukeyouell\support\elements\Ticket as TicketElement;
use lukeyouell\support\models\Settings;
use lukeyouell\support\services\SupportService as SupportServiceService;
use lukeyouell\support\variables\SupportVariable;

use Craft;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\Elements;
use craft\services\Plugins;
use craft\services\UserPermissions;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Luke Youell
 * @package   Support
 * @since     1.0.0
 *
 * @property  SupportServiceService $supportService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Support extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Support::$plugin
     *
     * @var Support
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Support::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['support/tickets'] = 'support/tickets/index';
                $event->rules['support/tickets/new'] = 'support/tickets/new';
                $event->rules['support/tickets/<ticketId:\d+>'] = 'support/tickets/view';

                $event->rules['support/settings/general'] = 'support/settings/index';

                $event->rules['support/settings/attachments'] = 'support/attachments/index';

                $event->rules['support/settings/ticket-statuses'] = 'support/ticket-statuses/index';
                $event->rules['support/settings/ticket-statuses/new'] = 'support/ticket-statuses/edit';
                $event->rules['support/settings/ticket-statuses/<id:\d+>'] = 'support/ticket-statuses/edit';
            }
        );

        // Register our elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = MessageElement::class;
                $event->types[] = TicketElement::class;
            }
        );

        // Register user permissions
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[$this->name] = [
                    'support-manageTickets' => ['label' => \Craft::t('support', 'Manage Tickets')],
                    'support-deleteTickets' => ['label' => \Craft::t('support', 'Delete Tickets')],
                ];
            }
        );

        // Register variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $variable = $event->sender;
                $variable->set($this->handle, SupportVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('settings/plugins/support'))->send();
                }
            }
        );

        Craft::info(
            Craft::t(
                'support',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function getCpNavItem()
    {
        $ret = parent::getCpNavItem();

        $ret['label'] = $this->getSettings()->pluginNameOverride ?: $this->name;

        $ret['subnav']['tickets'] = [
            'label' => 'Tickets',
            'url'   => 'support/tickets',
        ];

        if (Craft::$app->getUser()->getIsAdmin()) {
            $ret['subnav']['settings'] = [
                'label' => 'Settings',
                'url'   => 'support/settings/general',
            ];
        }

        return $ret;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        // Get and pre-validate the settings
        $settings = $this->getSettings();
        $settings->validate();
        // Get the settings that are being defined by the config file
        $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($this->handle));

        return Craft::$app->view->renderTemplate(
            'support/settings',
            [
                'settings' => $settings,
                'overrides' => array_keys($overrides)
            ]
        );
    }
}
