<?php

class m140929_032601_alter_table_profiles extends CDbMigration
{

	/**
	 * Creates initial version of the table
	 */
	public function up()
	{

		$this->execute("
            ALTER TABLE `profiles` ADD `code_card_expire_date` DATE NULL;
        ");
        
	}

	/**
	 * Drops the table
	 */
	public function down()
	{
        
		$this->execute("
            ALTER TABLE `profiles` DROP `code_card_expire_date`;
        ");
        
	}

	/**
	 * Creates initial version of the table in a transaction-safe way.
	 * Uses $this->up to not duplicate code.
	 */
	public function safeUp()
	{
		$this->up();
	}

	/**
	 * Drops the table in a transaction-safe way.
	 * Uses $this->down to not duplicate code.
	 */
	public function safeDown()
	{
		$this->down();
	}
}
