<?php

class QueryTools_Table
{
	protected $name;
	protected $fields = array();
	protected $constraints = array();
	
	function __construct(&$qt, $name)
	{
		$this->qt = $qt;
		$this->name = $name;
	}
	
	function addPrimaryKey($fields, $name=null)
	{
		$out = null;
		if($name === null)
			$name = $this->name.'_pkey';
		
		if(is_array($fields))
		{
			$out = '';
			foreach($fields as $field)
			{
				if($out != '')
					$out .= ', ';
				$out .= $this->qt->objectReference($field);
			}
			$fields = $out;
		}
		else
			$fields = $this->qt->objectReference($fields);
			
		if($this->qt->isType('mysql'))
		{
			$out = 'PRIMARY KEY('.$fields.')';
		}
		else if($this->qt->isType('pgsql'))
		{
			$out = 'CONSTRAINT '.$name.' PRIMARY KEY ('.$fields.')';
		}
		else if($this->qt->isType('sqlite'))
		{
			$out = 'CONSTRAINT '.$name.' PRIMARY KEY ('.$fields.')';
		}
		$this->constraints[] = $out;
	}	
	
	
	function column($name, $type, $options=null)
	{
		if(isset($options['primary_key']) && $options['primary_key'])
			$this->addPrimaryKey($name);
		unset($options['primary_key']);
		$dec = $this->qt->columnDefinition($name, $type, $options);
		$this->fields[] = $dec;
	}
	
	function query()
	{
		$fieldString = '';
		$query = 'CREATE TABLE '.$this->qt->objectReference($this->name)." (\n%s\n)";
		foreach($this->fields as $fieldValue)
		{
			if($fieldString != "")
				$fieldString .= ",\n";
			$fieldString .= $fieldValue;
		}
		foreach($this->constraints as $c)
		{
			$fieldString .= ",\n".$c;
		}
		
		return sprintf($query, $fieldString);
	}
	
	function save()
	{
		$this->qt->saveTable($this);
	}
}

class QueryTools
{
	static $types = array('mysql', 'pgsql', 'sqlite');
	protected $type = null;
	protected $db = null;

	function __construct(&$db, $dbType=null)
	{
		$this->db = $db;
		if($dbType === null)
			$dbType = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
		$this->setType($dbType);
	}
	
	function saveTable($t)
	{
		try {
			$this->db->exec($t->query());
		}
		catch(PDOException $e)
		{
			print 'Failed to save table: '.$t->query().'<br />';
			throw $e;
		}
	}
	
	function setType($type)
	{
		$type = strtolower($type);
		if(!in_array($type, self::$types))
			throw new Exception('Unsupported database type: '.$type);
		$this->type = $type;
	}
	
	function type()
	{
		return $this->type;
	}
	
	function isType($type)
	{
		if(strtolower($type) == $this->type())
			return true;
		return false;
	}
	
	function test() 
	{
		$t = $this->createTable('users');
		$t->column('id', 'serial', array('primary_key' => true));
		$t->column('firstname', 'string', array('size' => 255, 'null' => false));
		$t->column('lastname', 'string', array('size' => 255));
		$t->column('username', 'string', array('size' => 128));
		$t->column('enabled', 'bool', array('default' => true));
		$t->column('awesome', 'bool', array('default' => false));
		$t->save();
		print "\n";
		$this->createIndex('users', 'enabled', 'index_enabled');
		print "\n";
		$this->createIndex('users', 'username', 'index_username', true);
		print "\n\n";
		$t = $this->createTable('url_stats');
		$t->column('id', 'serial', array('primary_key' => true, 'null' => false));
		$t->column('url_id', 'integer', array('null' => false));
		$t->column('ip_address', 'string', array('size' => 255));
		$t->column('referer', 'text');
		$t->column('created_on', 'datetime', array('null' => false));
		$t->save();
	}
	
	function nativeValueForType($value, $type)
	{
		if($this->isType('mysql'))
		{
			switch($type)
			{
				case 'string':
				case 'text':
					return $this->db->quote($value);
					break;
				case 'bool':
					return $value ? 1 : 0;
					break;
				default:
					return $value;
			}
		}
		else if($this->isType('pgsql'))
		{
			switch($type)
			{
				case 'string':
				case 'text':
					return $this->db->quote($value);
					break;
				case 'bool':
					$value = ($value ? 'true' : 'false');
					return $value;
					break;
				default:
					return $value;
			}
		}
		else if($this->isType('sqlite'))
		{
			switch($type)
			{
				case 'string':
				case 'text':
					return $this->db->quote($value);
					break;
				case 'bool':
					return $value ? 1 : 0;
					break;
				default:
					return $value;
			}
		}
	}
	
	function createTable($table)
	{
		return new QueryTools_Table($this, $table);
//		$fieldString = '';
//		$query = 'CREATE TABLE '.$this->objectReference($table).' (%s)';
//		foreach($fields as $fieldValue)
//		{
//			if($fieldString != "")
//				$fieldString .= ",\n";
//			$fieldString .= $fieldValue;
//		}
//		return sprintf($query, $fieldString);
	}
	
	function createIndex($table, $fields, $name, $unique=false)
	{
		$out = '';
		if(is_array($fields))
		{
			$out = '';
			foreach($fields as $field)
			{
				if($out != '')
					$out .= ', ';
				$out .= $this->objectReference($field);
			}
			$fields = $out;
		}
		else
			$fields = $this->objectReference($fields);
		
		$out = 'CREATE'.($unique ? ' UNIQUE' : '').' INDEX '.$name.' ON '.$this->objectReference($table).' ('.$fields.');';
		
		/*if($this->isType('mysql'))
		{
		}
		else if($this->isType('pgsql'))
		{
		}
		else if($this->isType('sqlite'))
		{
		}*/
		
		$this->db->exec($out);
	}
	
	function addColumn($table, $column, $type, $options=null)
	{
		$query = 'ALTER TABLE '.$table.' ADD COLUMN '.$this->columnDefinition($column, $type, $options);
		$this->db->exec($query);
	}
	
	// field/table helpers
	function objectReference($table)
	{
		switch($this->type)
		{
			case 'mysql':
				$table = '`'.$table.'`';
				break;
			case 'pgsql':
			case 'sqlite':
				$table = '"'.$table.'"';
				break;
		}
		return $table;
	}
	
	function columnDefinition($name, $type, $options=null)
	{
		$size = isset($options['size']) ? $options['size'] : null;
		$out = '';
		$originalType = $type;
		if($this->isType('mysql'))
		{
			switch($type)
			{
				case 'bigint':
					$out = 'bigint';
					break;
				case 'datetime':
					$out = 'DATETIME';
					break;
				case 'serial':
					$out = 'INTEGER AUTO_INCREMENT';
					break;
				case 'bool':
					$out = 'tinyint(1)';
					break;
				case 'inet':
				case 'string':
					if($size === null)
						$size = 128;
					$out = 'VARCHAR('.$size.')';
					break;
				case 'text':
					$out = 'TEXT';
					break;
				case 'integer':
					$out = 'INT';
					break;
				case 'decimal':
					if($size === null)
						$size = '5,2';
					$out = 'DECIMAL('.$size.')';
					break;
				default:
					throw new Exception('Unknown type: '.$type);
			}
			if(isset($options['unsigned']) && $options['unsigned'])
				$out .= ' UNSIGNED';
			if(isset($options['null']))
				$out .= !$options['null'] ? ' NOT NULL' : '';
			if(isset($options['default']))
				$out .= ' DEFAULT '.$this->nativeValueForType($options['default'], $type);
		}
		else if($this->isType('pgsql'))
		{
			switch($type)
			{
				case 'bigint':
					$type = 'bigint';
					break;
				case 'inet':
					$type = 'inet';
					break;
				case 'datetime':
					$type = 'timestamp without time zone';
					break;
				case 'serial':
					$type = 'serial';
					break;
				case 'bool':
					$type = 'boolean';
					break;
				case 'string':
					if($size === null)
						$size = 128;
					$type = 'character varying';
					break;
				case 'text':
					$type = 'text';
					break;
				case 'integer':
					$type = 'integer';
					break;
				case 'decimal':
					if($size === null)
						$size = '5,2';
					$type = 'numeric';
					break;
				default:
					throw new Exception('Unknown type: '.$type);
			}
			$out = $type.($size !== null ? '('.$size.')' : '');
			if(isset($options['null']))
				$out .= !$options['null'] ? ' NOT NULL' : '';
			if(isset($options['default']))
			{
				$out .= ' DEFAULT '.$this->nativeValueForType($options['default'], $originalType); //.'::'.$type;
			}
		}
		else if($this->isType('sqlite'))
		{
			switch($type)
			{
				case 'serial':
					$out = 'INTEGER';
					break;
				case 'inet':
				case 'string':
				case 'text':
				case 'datetime':
					$out = 'TEXT';
					break;
				case 'bigint':
				case 'integer':
				case 'bool':
					$out = 'INTEGER';
					break;
				case 'decimal':
					$out = 'REAL';
					break;
				default:
					throw new Exception('Unknown type: '.$type);
			}
			if(isset($options['null']))
				$out .= !$options['null'] ? ' NOT NULL' : '';
			if(isset($options['default']))
			{
				$out .= ' DEFAULT '.$this->nativeValueForType($options['default'], $originalType); //.'::'.$type;
			}
		}
		$dec = $this->objectReference($name).' '.$out;
		return $dec;
	}
	
	function field($name, $type, $options=null)
	{
		// option keys: null, default, auto_increment, primary_key
		return $this->objectReference($name).' '.$this->typeDefinition($type, $options);
	}
}
