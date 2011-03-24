<?php 
// If install.php was deleted, don't allow access!
defined('OKAY_TO_MIGRATE') OR die('No direct access allowed.'); 

require_once 'Migration.php';

class Migrator
{
	protected $db;
	protected $current_version; // checked by constructor
	protected $migrations_dir;
	
	function __construct(&$db, $migrations_dir=null)
	{
		$this->db = $db;
		$this->current_version = 0;
		if($migrations_dir === null)
			$migrations_dir = './migrations'; // default to ./migrations
		$this->migrations_dir = $migrations_dir;
		$this->qt = new QueryTools($this->db);
		
		$this->check_schema_table();
	}
	
	function currentVersion()
	{
		return $this->current_version;
	}
	
	function check_schema_table()
	{
		try {
			$stmt = $this->db->prepare('SELECT * FROM '.DB_PREFIX.'schema_info');
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if(count($rows) == 0)
				$this->db->exec('INSERT INTO '.DB_PREFIX.'schema_info VALUES(1, 0)');
			else 
			{
				$this->current_version = $rows[0]['version'];
			}
		}
		catch(PDOException $e) {
			$t = $this->qt->createTable(DB_PREFIX.'schema_info');
			$t->column('id', 'serial', array('primary_key' => true, 'null' => false));
			$t->column('version', 'integer', array('default' => 0, 'null' => false));
			$t->save();
			$this->db->exec('INSERT INTO '.DB_PREFIX.'schema_info VALUES(1, 0)');
		}
	}
	
	function updateInfoToVersion($v)
	{
		$this->db->exec('UPDATE '.DB_PREFIX.'schema_info SET version='.$v.' WHERE id = 1');
	}
	
	function migrate($start=null)
	{
		$this->html_begin();
		
		if($start === null)
			$start = $this->current_version + 1;
		$files = glob($this->migrations_dir."/*.php");
		
		$this->db->beginTransaction();
		$highest_version_seen = 0;
		echo '<dl>';
		foreach($files as $file)
		{
			include $file;
			$file = basename($file, '.php');
			$class = substr($file, strpos($file,'_')+1);
			$v = intval(substr($file,0,strpos($file,'_')));
			if($highest_version_seen < $v) $highest_version_seen = $v;
			if(!class_exists($class))
			{
				echo '</dl>';
				Migrator::message('failure',  'Could not find class: \''.$class
				.'\' in migration \''.$file."'"); //no need to escape for html
				$this->html_end();
				exit;
			}
			if($v < $start || $v <= $this->currentVersion())
			{
				continue;
			}
			$m = new $class($this->db);
			try {
				echo '<dt>Running migration '.htmlspecialchars($class).
					'::up to version '.$v.'</dt><dd>';
				$m->up();
				Migrator::message('success','Migration complete. Current schema version: '.$v);
				$this->updateInfoToVersion($v);
				$this->db->commit();
				$this->db->beginTransaction();
				echo '</dd>';
			}
			catch(Exception $e)
			{
				Migrator::message('failure', 'Failed to migrate, rolling back.'."\n".(string)$e);
				$this->db->rollBack();
				echo '</dd></dl>';
				$this->html_end();
				exit();
			}
			//$last_version = $v;
		}
		echo '</dl>';
		
		if($this->currentVersion() == $highest_version_seen){
			Migrator::message('inform', 'Your schema is up-to-date! Schema version: '
				.$this->currentVersion());
		}
		
		$this->html_end();
	}
	
	
	function html_begin()
	{
		echo "<!DOCTYPE HTML>
		<html>
		<head>
		<title>Installing or Upgrading ".APP_NAME."</title>
		<link type=\"text/css\" rel=\"stylesheet\" href=\"/-/css/admin.css\"/>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
		</head>
		<body>
			<div class=\"bigWrap\">
				<div class=\"huge\">&#x2197;</div>
				<h2 class=\"bigTitle\">Installing/Upgrading ".APP_NAME."</h2>
			</div>
			<div class=\"everythingElse\">
		";	
		
	}
	
	function html_end()
	{
		
		print '<p><strong>You will probably want to delete install.php when finished installing/migrating.</strong></p></div></body></html>';
		
	}
		
	/**
	*	Reports a message to the user. Your message should not include
	* 	the word(s) success/failure, we'll make sure that is communicated.
	*	
	*	We also escape the string, so no need to worry about that (can be disabled.)
	*	
	*	$type can be 'inform', 'success', or 'failure'
	*/
	static function message($type, $content, $escape = true, $nl2br = true)
	{
		static $templates = array(
			'inform'  => '<p class="message message_inform "><span class="message_label"></span>%s</p>',
			'success' => '<p class="message message_success"><span class="message_label">Success: </span>%s</p>',
			'failure' => '<p class="message message_failure"><span class="message_label">Failed: </span>%s</p>',
		);
		if ( ! in_array($type, array_keys($templates), TRUE)) {
			try{
				self::message('inform', 
					'Unsupported message type '.$type.' called in Migrator::message', 
					TRUE);			
			}
			catch(Exception $e){}
		}
		
		if ($escape) $content = htmlspecialchars($content);
		if ($nl2br) $content = nl2br($content);
		
		printf($templates[$type], $content);
		flush();
	}
}