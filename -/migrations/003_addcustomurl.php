<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 


class AddCustomURL extends Migration
{
	function up()
	{
		Migrator::message('inform',
		 	'This migration allows you to set a custom short URL. '
			.'Make sure to get the <strong>NEW</strong> bookmarklets that support this feature!',
			false
		);
		$this->addColumn(DB_PREFIX.'urls', 'custom_url', 'string', array('default' => null, 'size' => 255));
	}
	
	function down()
	{
		
	}
}
