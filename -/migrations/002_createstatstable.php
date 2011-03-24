<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 



class CreateStatsTable extends Migration
{
	function up()
	{
		$t = $this->createTable(DB_PREFIX.'url_stats');
		$t->column('id', 'serial', array('primary_key' => true, 'null' => false));
		$t->column('url_id', 'integer', array('null' => false));
		$t->column('ip_address', 'inet');
		$t->column('referer', 'text');
		$t->column('created_on', 'datetime', array('null' => false));
		$t->save();
	}
	
	function down()
	{
		
	}
}