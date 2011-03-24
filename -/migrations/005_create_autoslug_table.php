<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 



class Create_Autoslug_Table extends Migration
{
	function up()
	{
		Migrator::message('inform',
		 	'This migration enables an efficient method of generating automatic slugs.'
		);
		$prefix = DB_PREFIX;
		
		$t = $this->createTable(DB_PREFIX.'autoslug');
		$t->column('id', 'serial', array('primary_key' => true, 'null' => false));
		$t->column('method', 'string', array('null' => false, 'size' =>'31'));
		$t->column('base10', 'string', array('null' => false, 'size'=>'100'));
		$t->save();

		$this->createIndex(DB_PREFIX.'autoslug', 'method', 'method_index', 'unique');
		
		// Populate
		// SQLite/PostgreSQL don't support compound(?) inserts.
		// migration could possibly provide an insert function
		$add_methods_sql = array();
		$add_methods_sql[] = <<<EOSQL
INSERT INTO  ${prefix}autoslug (method ,base10)
VALUES ('base36',  '1');
EOSQL;
		$add_methods_sql[] = <<<EOSQL
INSERT INTO  ${prefix}autoslug (method ,base10)
VALUES ('base62',  '1');
EOSQL;
		$add_methods_sql[] = <<<EOSQL
INSERT INTO  ${prefix}autoslug (method ,base10)
VALUES ('mixed-smart',  '1');
EOSQL;
		$add_methods_sql[] = <<<EOSQL
INSERT INTO  ${prefix}autoslug (method ,base10)
VALUES ('smart',  '1');
EOSQL;
		foreach($add_methods_sql as $sql)
		{
			$ins = $this->db->prepare($sql);
			if( ! $ins->execute()){
				throw new Exception('Failed to add rows for autoslug methods.');
			}
		}
	}
	
	function down()
	{
		// drop autoslug table
	}
}