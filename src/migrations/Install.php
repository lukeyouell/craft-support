<?php
/**
 * Support plugin for Craft CMS 3.x
 *
 * Simple support system for tracking, prioritising and solving customer support tickets.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\support\migrations;

use lukeyouell\support\Support;
use lukeyouell\support\records\Email as EmailRecord;
use lukeyouell\support\records\TicketStatus as TicketStatusRecord;
use lukeyouell\support\records\TicketStatusEmail as TicketStatusEmailRecord;
use lukeyouell\support\records\TicketPriority as TicketPriorityRecord;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use craft\helpers\MigrationHelper;

use LitEmoji\LitEmoji;

/**
 * Support Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Luke Youell
 * @package   Support
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    public $driver;

    // Public Methods
    // =========================================================================

    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->dropForeignKeys();
        $this->dropTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function createTables()
    {
        $tablesCreated = false;

        // support_tickets table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%support_tickets}}');
        if ($tableSchema === null) {
            $tablesCreated = true;

            $this->createTable(
                '{{%support_emails}}',
                [
                    'id'            => $this->primaryKey(),
                    'dateCreated'   => $this->dateTime()->notNull(),
                    'dateUpdated'   => $this->dateTime()->notNull(),
                    'uid'           => $this->uid(),
                    // Custom columns in the table
                    'name'          => $this->string()->notNull(),
                    'subject'       => $this->string()->notNull(),
                    'recipientType' => $this->enum('recipientType', ['author', 'custom'])->defaultValue('custom'),
                    'to'            => $this->string(),
                    'bcc'           => $this->string(),
                    'templatePath'  => $this->string()->notNull(),
                    'sortOrder'   => $this->integer(),
                    'enabled'       => $this->boolean(),
                ]
            );

            $this->createTable(
                '{{%support_messages}}',
                [
                    'id'            => $this->primaryKey(),
                    'dateCreated'   => $this->dateTime()->notNull(),
                    'dateUpdated'   => $this->dateTime()->notNull(),
                    'uid'           => $this->uid(),
                    // Custom columns in the table
                    'ticketId'      => $this->integer(),
                    'authorId'      => $this->integer(),
                    'attachmentIds' => $this->text(),
                    'content'       => $this->text()->notNull(),
                ]
            );

            $this->createTable(
                '{{%support_tickets}}',
                [
                    'id'             => $this->primaryKey(),
                    'dateCreated'    => $this->dateTime()->notNull(),
                    'dateUpdated'    => $this->dateTime()->notNull(),
                    'uid'            => $this->uid(),
                    // Custom columns in the table
                    'ticketStatusId' => $this->integer(),
                    'ticketPriorityId' => $this->integer(),
                    'authorId'       => $this->integer(),
                ]
            );

            $this->createTable(
                '{{%support_ticketstatuses}}',
                [
                    'id'          => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid'         => $this->uid(),
                    // Custom columns in the table
                    'name'        => $this->string()->notNull(),
                    'handle'      => $this->string()->notNull(),
                    'colour'      => $this->enum('colour', ['green', 'orange', 'red', 'blue', 'yellow', 'pink', 'purple', 'turquoise', 'light', 'grey', 'black'])->notNull()->defaultValue('green'),
                    'sortOrder'   => $this->integer(),
                    'default'     => $this->boolean(),
                    'newMessage'  => $this->boolean(),
                ]
			);
			
			$this->createTable(
                '{{%support_ticketpriorities}}',
                [
                    'id'          => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid'         => $this->uid(),
                    // Custom columns in the table
                    'name'        => $this->string()->notNull(),
                    'handle'      => $this->string()->notNull(),
                    'colour'      => $this->enum('colour', ['green', 'orange', 'red', 'blue', 'yellow', 'pink', 'purple', 'turquoise', 'light', 'grey', 'black'])->notNull()->defaultValue('green'),
                    'sortOrder'   => $this->integer(),
                    'default'     => $this->boolean()
                ]
            );

            $this->createTable(
                '{{%support_ticketstatus_emails}}',
                [
                    'id'             => $this->primaryKey(),
                    'dateCreated'    => $this->dateTime()->notNull(),
                    'dateUpdated'    => $this->dateTime()->notNull(),
                    'uid'            => $this->uid(),
                    // Custom columns in the table
                    'ticketStatusId' => $this->integer()->notNull(),
                    'emailId'        => $this->integer()->notNull(),
                ]
            );
        }

        return $tablesCreated;
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%support_messages}}', ['id'], '{{%elements}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%support_messages}}', ['authorId'], '{{%users}}', ['id'], null, 'CASCADE');
        $this->addForeignKey(null, '{{%support_messages}}', ['ticketId'], '{{%support_tickets}}', ['id'], 'CASCADE');

        $this->addForeignKey(null, '{{%support_tickets}}', ['id'], '{{%elements}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%support_tickets}}', ['authorId'], '{{%users}}', ['id'], null, 'CASCADE');
        $this->addForeignKey(null, '{{%support_tickets}}', ['ticketStatusId'], '{{%support_ticketstatuses}}', ['id'], null, 'CASCADE');
        $this->addForeignKey(null, '{{%support_tickets}}', ['ticketpriorityId'], '{{%support_ticketpriorities}}', ['id'], null, 'CASCADE');

        $this->addForeignKey(null, '{{%support_ticketstatus_emails}}', ['emailId'], '{{%support_emails}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, '{{%support_ticketstatus_emails}}', ['ticketStatusId'], '{{%support_ticketstatuses}}', ['id'], 'CASCADE', 'CASCADE');
    }

    protected function dropForeignKeys()
    {
        MigrationHelper::dropAllForeignKeysOnTable('{{%support_messages}}', $this);
        MigrationHelper::dropAllForeignKeysOnTable('{{%support_tickets}}', $this);
        MigrationHelper::dropAllForeignKeysOnTable('{{%support_ticketstatuses}}', $this);
        MigrationHelper::dropAllForeignKeysOnTable('{{%support_ticketpriorities}}', $this);
        MigrationHelper::dropAllForeignKeysOnTable('{{%support_ticketstatus_emails}}', $this);
    }

    protected function dropTables()
    {
        $this->dropTable('{{%support_emails}}');
        $this->dropTable('{{%support_messages}}');
        $this->dropTable('{{%support_tickets}}');
        $this->dropTable('{{%support_ticketstatuses}}');
        $this->dropTable('{{%support_ticketpriorities}}');
        $this->dropTable('{{%support_ticketstatus_emails}}');
    }

    protected function insertDefaultData()
    {
		$this->_defaultTicketStatuses();
		$this->_defaultTicketPriority();
    }

    // Private Methods
    // =========================================================================

    private function _defaultTicketStatuses()
    {
        // Default ticket statuses
        $data = [
            'name'      => 'New',
            'handle'    => 'new',
            'colour'    => 'blue',
            'sortOrder' => 1,
            'default'   => true
        ];
        $this->insert(TicketStatusRecord::tableName(), $data);

        $data = [
            'name'       => 'In Progress',
            'handle'     => 'inProgress',
            'colour'     => 'orange',
            'sortOrder'  => 2,
            'newMessage' => true,
        ];
        $this->insert(TicketStatusRecord::tableName(), $data);

        $data = [
            'name'      => 'Solved',
            'handle'    => 'solved',
            'colour'    => 'green',
            'sortOrder' => 3,
        ];
        $this->insert(TicketStatusRecord::tableName(), $data);

        $data = [
            'name'      => 'Closed',
            'handle'    => 'closed',
            'colour'    => 'red',
            'sortOrder' => 4,
        ];
        $this->insert(TicketStatusRecord::tableName(), $data);

        $data = [
            'name'      => 'Archived',
            'handle'    => 'archived',
            'colour'    => 'grey',
            'sortOrder' => 5,
        ];
        $this->insert(TicketStatusRecord::tableName(), $data);

		// Default emails
        $data = [
            'name'          => 'New Ticket',
			// 'subject'       => LitEmoji::unicodeToShortcode('[📥 New Support Ticket] {title} (#{id})'),
			'subject'		=> "{{siteName}}: {title} {% if ticketPriority.handle == 'critical' %}{ticketPriority.name}{% endif %}",
            'recipientType' => 'author',
			// 'to'            => Craft::$app->systemSettings->getSetting('email', 'fromEmail'),
            'templatePath'  => 'support/_emails/newTicket',
            'sortOrder'     => 1,
            'enabled'       => true,
        ];
        $this->insert(EmailRecord::tableName(), $data);

        $data = [
            'name'          => 'New Message',
			// 'subject'       => LitEmoji::unicodeToShortcode('[📥 New Message] {title} (#{id})'),
			'subject'		=> "{{siteName}}: {title}",
            'recipientType' => 'author',
			// 'to'            => Craft::$app->systemSettings->getSetting('email', 'fromEmail'),
            'templatePath'  => 'support/_emails/newMessage',
            'sortOrder'     => 2,
            'enabled'       => true,
        ];
        $this->insert(EmailRecord::tableName(), $data);

        $data = [
            'name'          => 'Ticket Closed',
			// 'subject'       => LitEmoji::unicodeToShortcode('[📕 Ticket Closed] {title} (#{id})'),
			'subject'		=> "{{siteName}}: {title}",
            'recipientType' => 'author',
			// 'to'            => Craft::$app->systemSettings->getSetting('email', 'fromEmail'),
            'templatePath'  => 'support/_emails/ticketClosed',
            'sortOrder'     => 3,
            'enabled'       => true,
        ];
        $this->insert(EmailRecord::tableName(), $data);

        // Default ticket status / email links
        $data = [
            'ticketStatusId' => 1,
            'emailId'        => 1,
        ];
        $this->insert(TicketStatusEmailRecord::tableName(), $data);

        $data = [
            'ticketStatusId' => 2,
            'emailId'        => 2,
        ];
        $this->insert(TicketStatusEmailRecord::tableName(), $data);

        $data = [
            'ticketStatusId' => 4,
            'emailId'        => 3,
        ];
        $this->insert(TicketStatusEmailRecord::tableName(), $data);
	}
	
	private function _defaultTicketPriority()
	{
		 // Default ticket priorities
		 $data = [
            'name'      => 'Critical',
            'handle'    => 'critical',
            'colour'    => 'red',
            'sortOrder' => 1
        ];
        $this->insert(TicketPriorityRecord::tableName(), $data);

        $data = [
            'name'       => 'Major',
            'handle'     => 'major',
            'colour'     => 'orange',
            'sortOrder'  => 2,
        ];
        $this->insert(TicketPriorityRecord::tableName(), $data);

        $data = [
            'name'      => 'Minor',
            'handle'    => 'minor',
            'colour'    => 'green',
            'sortOrder' => 3,
        ];
        $this->insert(TicketPriorityRecord::tableName(), $data);

        $data = [
            'name'      => 'Enhancement',
            'handle'    => 'enhancement',
            'colour'    => 'blue',
            'sortOrder' => 4,
        ];
        $this->insert(TicketPriorityRecord::tableName(), $data);
	}
}
