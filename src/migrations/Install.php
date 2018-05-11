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

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

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
        $this->removeForeignKeys();
        $this->removeTables();

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
                    'id'          => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid'         => $this->uid(),
                    // Custom columns in the table
                    'status'      => $this->string()->notNull(),
                    'authorId'    => $this->integer(),
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
        // support_tickets table
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%support_tickets}}', 'id'),
            '{{%support_tickets}}',
            'id',
            '{{%elements}}',
            'id',
            'CASCADE',
            null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%support_tickets}}', 'authorId'),
            '{{%support_tickets}}',
            'authorId',
            '{{%users}}',
            'id',
            'CASCADE',
            null
        );

        // support_messages table
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%support_messages}}', 'id'),
            '{{%support_messages}}',
            'id',
            '{{%elements}}',
            'id',
            'CASCADE',
            null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%support_messages}}', 'ticketId'),
            '{{%support_messages}}',
            'ticketId',
            '{{%support_tickets}}',
            'id',
            'CASCADE',
            null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%support_messages}}', 'authorId'),
            '{{%support_messages}}',
            'authorId',
            '{{%users}}',
            'id',
            'CASCADE',
            null
        );
    }

    protected function insertDefaultData()
    {
    }

    protected function removeForeignKeys()
    {
        $this->dropForeignKey(
            $this->db->getForeignKeyName('{{%support_messages}}', 'ticketId'),
            '{{%support_messages}}',
            'ticketId',
            '{{%support_tickets}}',
            'id',
            'CASCADE',
            null
        );
    }

    protected function removeTables()
    {
        $this->dropTableIfExists('{{%support_tickets}}');
        $this->dropTableIfExists('{{%support_messages}}');
    }
}
