<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 


abstract class Migration
{
	protected $db;
	
	function __construct(&$db)
	{
		$this->db = $db;
		$this->qt = new QueryTools($this->db, $this->db->getAttribute(PDO::ATTR_DRIVER_NAME));
	}
	
	abstract function up();
	abstract function down();
	
	function createTable($table)
	{
		return $this->qt->createTable($table);
	}
	
	function createIndex($table, $fields, $name, $unique=false)
	{
		$this->qt->createIndex($table, $fields, $name, $unique);
	}
	
	function __call($method, $args)
	{
		return call_user_func_array(array($this->qt, $method), $args);
	}

}