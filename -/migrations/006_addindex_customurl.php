<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.');


class AddIndex_CustomURL extends Migration
{
	function up()
	{
		Migrator::message('inform',
		 	'This migration sets an index on custom short URL. ',
			false
		);

		$this->createIndex(DB_PREFIX . 'urls', 'custom_url', 'custom_url_index');
	}

	function down()
	{

	}
}
