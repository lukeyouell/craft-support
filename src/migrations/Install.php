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
use lukeyouell\support\records\TicketStatus;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use craft\helpers\MigrationHelper;

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
                '{{%support_tickets}}',
                [
                    'id'             => $this->primaryKey(),
                    'dateCreated'    => $this->dateTime()->notNull(),
                    'dateUpdated'    => $this->dateTime()->notNull(),
                    'uid'            => $this->uid(),
                    // Custom columns in the table
                    'ticketStatusId' => $this->integer(),
                    'authorId'       => $this->integer(),
                ]
            );

            $this->createTable(
                '{{%support_ticketstatuses}}',
                [
                    'id'            => $this->primaryKey(),
                    'dateCreated'   => $this->dateTime()->notNull(),
                    'dateUpdated'   => $this->dateTime()->notNull(),
                    'uid'           => $this->uid(),
                    // Custom columns in the table
                    'name'      => $this->string()->notNull(),
                    'handle'      => $this->string()->notNull(),
                    'colour' => $this->enum('colour', ['green', 'orange', 'red', 'blue', 'yellow', 'pink', 'purple', 'turquoise', 'light', 'grey', 'black'])->notNull()->defaultValue('green'),
                    'sortOrder' => $this->integer(),
                    'default' => $this->boolean(),
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
        }

        return $tablesCreated;
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%support_tickets}}', ['id'], '{{%elements}}', ['id'], null, 'CASCADE');
        $this->addForeignKey(null, '{{%support_tickets}}', ['authorId'], '{{%users}}', ['id'], null, 'CASCADE');
        $this->addForeignKey(null, '{{%support_tickets}}', ['ticketStatusId'], '{{%support_ticketstatuses}}', ['id'], null, 'CASCADE');
        $this->addForeignKey(null, '{{%support_messages}}', ['id'], '{{%elements}}', ['id'], null, 'CASCADE');
        $this->addForeignKey(null, '{{%support_messages}}', ['authorId'], '{{%users}}', ['id'], null, 'CASCADE');
        $this->addForeignKey(null, '{{%support_messages}}', ['ticketId'], '{{%support_tickets}}', ['id'], null, 'CASCADE');
    }

    protected function dropForeignKeys()
    {
        MigrationHelper::dropAllForeignKeysOnTable('{{%support_tickets}}', $this);
        MigrationHelper::dropAllForeignKeysOnTable('{{%support_ticketstatuses}}', $this);
        MigrationHelper::dropAllForeignKeysOnTable('{{%support_messages}}', $this);
    }

    protected function dropTables()
    {
        $this->dropTable('{{%support_tickets}}');
        $this->dropTable('{{%support_ticketstatuses}}');
        $this->dropTable('{{%support_messages}}');
    }

    protected function insertDefaultData()
    {
        $this->_defaultTicketStatuses();
    }

    // Private Methods
    // =========================================================================

    private function _defaultTicketStatuses()
    {
        $data = [
            'name' => 'New',
            'handle' => 'new',
            'colour' => 'blue',
            'default' => true
        ];
        $this->insert(TicketStatus::tableName(), $data);

        $data = [
            'name' => 'In Progress',
            'handle' => 'inProgress',
            'colour' => 'orange',
            'default' => false
        ];
        $this->insert(TicketStatus::tableName(), $data);

        $data = [
            'name' => 'Solved',
            'handle' => 'solved',
            'colour' => 'green',
            'default' => false
        ];
        $this->insert(TicketStatus::tableName(), $data);

        $data = [
            'name' => 'Closed',
            'handle' => 'closed',
            'colour' => 'red',
            'default' => false
        ];
        $this->insert(TicketStatus::tableName(), $data);

        $data = [
            'name' => 'Archived',
            'handle' => 'archived',
            'colour' => 'grey',
            'default' => false
        ];
        $this->insert(TicketStatus::tableName(), $data);
    }
}
