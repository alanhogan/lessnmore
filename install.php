<?php
// Prevent anyone for screwing with the database if this file is not here
define('OKAY_TO_MIGRATE', TRUE);

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/-/library');
require_once '-/config.php';
require_once '-/db.php';
require_once 'Migrator.php';
require_once 'QueryTools.php';


if(isset($_GET['start']) AND ctype_digit($_GET['start']))
{
	$start = (int) $_GET['start'];
}
else
	$start = NULL;
	
$migrator = new Migrator($db, dirname(__FILE__).'/-/migrations');
$migrator->migrate($start);

