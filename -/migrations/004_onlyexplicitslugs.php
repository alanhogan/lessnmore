<?php
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 


class OnlyExplicitSlugs extends Migration
{
	function up()
	{
		Migrator::message('inform',
		 	'This migration will attempt to assign each non-custom/legacy redirection an '
			.'explicit slug (short URL path) equal '
			.'to its id in base 36 (as in the original Lessn).'
		);
		
		//Set up / mini config
		$batch = 25;
		$prefix = DB_PREFIX;
		$mark_custom = TRUE;
		$db =& $this->db;
		// Queries
		$update_sql = "UPDATE ${prefix}urls SET redir_type = 'custom' WHERE custom_url IS NOT NULL";
		$select_sql = "SELECT * FROM ${prefix}urls "
			.'WHERE custom_url IS NULL '
			.'AND id > :min_id '
			.'ORDER BY id '
			.'LIMIT '.$batch;
		$check_sql = 'SELECT * FROM '.DB_PREFIX.'urls WHERE BINARY custom_url = BINARY :custom_url';
		$explicit_sql = 'UPDATE '.DB_PREFIX.'urls SET custom_url = :custom_url WHERE id=:id LIMIT 1';
		
		// --- STEP ONE ---
		// Add column to keep track of what kind of migration it is
		try {
			$this->addColumn(
				DB_PREFIX.'urls', 
				'redir_type', 
				'string', 		// We'd like to use enum, but not supported by SQLite.
				array('default' => 'auto', 'size' => 6, 'null' => false, 'index' => true)
			);
			
			$this->createIndex(DB_PREFIX.'urls', 'redir_type', 'redir_type_index');
			Migrator::message('success', 'Added "redir_type" column');
		}
		catch(Exception $e)
		{
			Migrator::message('failure', '"redir_type" column already exists… continuing…');
			$mark_custom = FALSE;
		}

		// --- STEP TWO ---
		// SET type to 'custom' when it was really custom
		if($mark_custom){
			$updt = $db->prepare($update_sql);
			if( ! $updt->execute()) throw new Exception('Update failed!');
			$affected = $updt->rowCount();
			Migrator::message(($affected ? 'success' : 'inform'), 
				$affected.
				' redirection(s) with custom slugs were explicitly marked as \'custom\'.');
		}
		
		// --- STEP THREE ---
		// Give each id-based redirection an explicit slug
		
		// Avoid doing failed migration rows over and over
		$min_id = -1;
		
		while(TRUE)
		{
			set_time_limit(60);
			$slct = $db->prepare($select_sql);
			$slct->execute(array('min_id'=>$min_id));
			$rows = $slct->fetchAll(PDO::FETCH_ASSOC);
		
			$returned = count($rows);
			$errors = 0; 
			
			// Migrate each of these
			foreach($rows as $row){
				$id = $row['id'];
				// For next batch
				if ($id > $min_id) $min_id = $id;
				
				// Explicit redirection
				$slug = base_convert($id, 10, 36);
				
				$chk  = $db->prepare($check_sql);
				$chk->execute(array('custom_url'=>$slug));
				// Check to make sure one doesn't aready exist
				$conflict = $chk->fetch(PDO::FETCH_ASSOC);
				if ($conflict === FALSE) 
				{
					$expl = $db->prepare($explicit_sql);
					$expl->execute(array('custom_url'=>$slug, 'id'=>$id));
				}
				elseif(is_array($conflict) && isset($conflict['id'])) 
				{
					Migrator::message('failure', 
						'Could not give redirect an explicit slug (already in use!). '
						."\nID: "
						.$id
						.". \nAlready-in-use slug: ".$slug
						.". \nID of (custom) redirection already using slug: ".$conflict['id']);
					$errors++;
				}
				else
				{
					throw new Exception('Unexpected database result when 
						checking for pre-existing rows');
				}

			}
			
			if(($returned-$errors) > 0) 
			{
				Migrator::message('success', 
					'Gave '.($returned-$errors).' redirections explicit slugs.'); 
			}
			if ($returned < $batch) {
				// Complete!
				break;
			} 
		}

	}
	
	function down()
	{
		$this->removeColumn(DB_PREFIX.'urls', 'redir_type');
		// Could remove slugs from all auto-assigned redirections
	}
}
{
//	For the record, ENUMs could work like: 
//
// Postgres:
// CREATE TYPE bu_redir_type AS ENUM ('auto', 'custom', 'alias', 'gone');
// ALTER TABLE ${prefix}urls ADD COLUMN redir_type bu_redir_type DEFAULT 'auto';
// UPDATE ${prefix}urls SET redir_type = 'custom' WHERE custom_url IS NOT NULL;
// 
// MySQL:
// ALTER TABLE ${prefix}urls ADD COLUMN redir_type ENUM  DEFAULT 'auto';
// UPDATE ${prefix}urls SET redir_type = 'custom' WHERE custom_url IS NOT NULL;
}