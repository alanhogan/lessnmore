<?php

/**
*	
*	This file allows inserting redirections and displaying stats.
*	
*	For redirections added, there are several possibilities:
*	
*	1. Adding a new URL, no custom slug: Inserted, and new slug auto-generated.
*	
*	2. Adding an existing URL, no custom slug: The old slug is returned.
*	
*	3. Adding a new URL, with a custom slug:
*	
*	4. Adding an existing URL, with a custom slug: The new slug is inserted anyway.
*		The old redirection(s) become a "aliases" of the new one,
*		so in case 2, the newest slug is returned (it’s "preferred".)
*	
*	DEVELOPERS:
*	Note the following possible redir_type values:
*	-	'auto' - Automatically assigned slug. 301 redirect on access.
*	-	'custom' - Manually set slug. 301 redirect on access.
*	-	'alias' - Its 'url' is really just another slug. Do a recursive lookup to redirect on access.
*	-	'gone' - Access results in a 410; should never change
*/
REQUIRE 'config.php';
REQUIRE 'db.php';
REQUIRE 'stats.php';

define('BCURLS_VERSION',	'2.0.1');

define('BCURLS_DOMAIN', 	preg_replace('#^www\.#', '', $_SERVER['SERVER_NAME']));
define('BCURLS_URL', 	str_replace('-/index.php', '', 'http://'.BCURLS_DOMAIN.$_SERVER['PHP_SELF']));
define('BCURLS_PATH', 	realpath('.') );

//don't reveal db prefix over HTTP. 16 chars is more than enough to avoid collisions
define('COOKIE_NAME', 	substr(md5(DB_PREFIX.COOKIE_SALT), 4, 16).'auth'); 
define('COOKIE_VALUE',	md5(USERNAME.PASSWORD.COOKIE_SALT));
define('COOKIE_DOMAIN', '.'.BCURLS_DOMAIN);

if (!defined('API_SALT')) define('API_SALT', 'B75jk4K25M5U7hTAP1'); // added in lessn 1.0.5
define('API_KEY', md5(USERNAME.PASSWORD.API_SALT));

define('NOW', 		time());
define('YEAR',		365 * 24 * 60 * 60);

/**
* @Returns int Characters from the right, 0-(n-1) where n = strlen($slug),
* where a banned word ends. e.g.:
* slug = 'dLpooz'
* 'poo' is a banned word
* returns 1
*/
function bcurls_find_banned_word($slug) {
	global $bcurls_banned_words;
	REQUIRE_ONCE 'banned_words.php';
	foreach ($bcurls_banned_words as $banned){
		$strpos = stripos($slug, $banned); 
		if ($strpos !== false) {
			bc_log('Found banned word '.$banned.' in '.$slug);
			// $slug = xBANNEDxx (length 9)
			// $banned = BANNED (length 6)
			// strpos = 1
			// we want to return 2 (BANNED is two characters from the right)
			return strlen($slug) - strlen($banned) - $strpos;
		}
	}
	return FALSE; 
}

/**
* @Returns int Characters from the right, 0-(n-1) where n = strlen($slug),
* where a banned glyph ends. e.g.:
*/
function bcurls_find_banned_glyph($slug) {
	if (ADDITIONAL_HOMOGLYPHS_TO_AVOID === false)
		return false;
	$glyphs = str_split(ADDITIONAL_HOMOGLYPHS_TO_AVOID);	
	foreach ($glyphs as $banned){
		$strpos = strpos($slug, $banned); 
		if ($strpos !== FALSE) {
			bc_log('Found banned glyph '.$banned.' in '.$slug);
			// $slug = xIxx (length 4)
			// $banned = I (length I)
			// strpos = 1
			// we want to return 2 (I is two characters from the right)
			return (strlen($slug) - 1 - $strpos);
		}
	}
	return FALSE; 
}

// handle login
if (isset($_POST['username']))
{
	if (md5($_POST['username'].$_POST['password'].COOKIE_SALT) == COOKIE_VALUE)
	{
		setcookie(COOKIE_NAME, COOKIE_VALUE, NOW + YEAR, '/', COOKIE_DOMAIN);
		$_COOKIE[COOKIE_NAME] = COOKIE_VALUE;
	}
}
// API login
else if (isset($_GET['api']) && $_GET['api'] == API_KEY)
{
	$_COOKIE[COOKIE_NAME] = COOKIE_VALUE;
}
else if (isset($_GET['api'])) // spit out a nicer failure for API attempts
{
	exit('Invalid API key');
}

// handle logout
if (isset($_GET['logout']))
{
	setcookie(COOKIE_NAME, '', NOW - YEAR, '/', COOKIE_DOMAIN);
	unset($_COOKIE[COOKIE_NAME]);
	header('Location:./');
}

// require login
if (!isset($_COOKIE[COOKIE_NAME]) || $_COOKIE[COOKIE_NAME] != COOKIE_VALUE)
{
	include('pages/login.php');
	exit();
}
// prolong login for another year, unless this is an API request
else if (!isset($_GET['api']))
{
	setcookie(COOKIE_NAME, COOKIE_VALUE, NOW + YEAR, '/', COOKIE_DOMAIN);
}

// Successfully logged in, so
define('OKAY_TO_SHOW_PAGES', true); //value doesn't matter

/**
*	WARNING! Provides NO checking and should ONLY be used internally!
*	Returns TRUE *or* a string "error code: error info".
*/
function bcurls_insert_url ($url, $checksum, $slug, $redir_type)
{
	global $db;
	$stmt = $db->prepare('INSERT INTO '.DB_PREFIX.'urls (url, checksum, custom_url, redir_type) VALUES(?, ?, ?, ?)');
	$stmt->bindValue(1, $url);
	$stmt->bindValue(2, $checksum);
	$stmt->bindValue(3, $slug);
	$stmt->bindValue(4, $redir_type);
	if($stmt->execute())
		return true;
	else
		return $stmt->errorCode().': '.$stmt->errorInfo();
}
/**
*	WARNING! Provides NO checking and should ONLY be used internally!
*	Returns TRUE *or* a string "error code: error info".
*/
function bcurls_update_slug ($url, $checksum, $slug, $redir_type)
{
	global $db;
	$stmt = $db->prepare('UPDATE '.DB_PREFIX.'urls SET url = ?, checksum = ?, redir_type = ? WHERE custom_url = ? ');
	$stmt->bindValue(1, $url);
	$stmt->bindValue(2, $checksum);
	$stmt->bindValue(3, $redir_type);
	$stmt->bindValue(4, $slug);
	if($stmt->execute())
		return true;
	else
		return $stmt->errorCode().': '.$stmt->errorInfo();
}

/**
* For debuggers, developers, and the curious
*/
function bc_log($message){
	global $bc_log;
	if(!is_string($bc_log)) $bc_log = '';
	if(LOG_MODE) $bc_log .= date('r - ')."$message \n";
}

// new shortcut
if (isset($_GET['url']) && !empty($_GET['url']))
{
	$url = $_GET['url'];
	$slug = NULL;
	$prefix = DB_PREFIX;
	if (!preg_match('#^[^:]+://#', $url))
	{
		$url = 'http://'.$url;
	}
	if(strpos($url, BCURLS_URL) === 0)
	{
		$error = 'You tried to shorten a URL on this domain which should already be short!';
		include('pages/error.php');
		exit;
	}

	// Is there already a row in the DB going to this same URL?
	$checksum 		= (int) sprintf('%u', crc32($url));
	$result = $db->prepare("SELECT id, custom_url, redir_type FROM {$prefix}urls WHERE checksum=? AND BINARY url = BINARY ? AND url = ? AND redir_type <> 'gone' ORDER BY redir_type DESC LIMIT 1"); //sort so custom is before auto.
	$result->bindValue(1, (int)$checksum);
	$result->bindValue(2, $url);
	$result->bindValue(3, $url);
	if ( ! $result->execute())
	{
		$error = 'Problem executing query to check if the URL is already in the DB! '
			.$result->errorCode().': '.$result->errorInfo();
		include('pages/error.php');
		exit;
	}
	
	if ($row = $result->fetch(PDO::FETCH_ASSOC))
	{
		$existing_slug = $row['custom_url'];
	} else {
		$existing_slug = false;
		if(LOG_MODE) bc_log('No existing slug'); //debug
	}
	
	if(isset($_GET['custom_url']) && strlen(trim($_GET['custom_url'])))
	{	// user wants to assign a custom short URL
		$custom_url = trim($_GET['custom_url']);
		// check if the slug is already in use
		$stmt = $db->prepare("SELECT * FROM {$prefix}urls WHERE BINARY custom_url = BINARY ? AND  custom_url =  ?");
		$stmt->bindValue(1, $custom_url);
		$stmt->bindValue(2, $custom_url);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if($row && ! isset($_GET['overwrite']))
		{
			$error = 'The custom short URL you attempted to use (/'.$row['custom_url'].') is already in use, and is '.($row['redir_type'] == 'alias' ? 'an alias for /': 'pointing to ').$row['url'];
			if(isset($_GET['api'])) exit('Error: Token in use');
			else 
			{
				include('pages/add.php');
				exit;
			}
		}
		elseif ($row) //implicit: they allowed overwrite
		{
			
			$redir_type = 'custom';
			$slug = $custom_url;
			// Update
			$insert_result = bcurls_update_slug ($url, $checksum, $slug, $redir_type);
			if($insert_result !== true) {
				$error = $insert_result;
				include('pages/error.php');
				exit;
			}
		}
		else 
		{
			$redir_type = 'custom';
			$slug = $custom_url;
			// Insert!
			$insert_result = bcurls_insert_url ($url, $checksum, $slug, $redir_type);
			if($insert_result !== true) {
				$error = $insert_result;
				include('pages/error.php');
				exit;
			}
		
		}
		
		// Old records become aliases to this one
		if($existing_slug !== false)
		{
			// User added a new custom short URL even though that URL is already in the DB
			// Update old redirections so they are no more than aliases of the new one ;)
			$update_to_alias_sql = "UPDATE {$prefix}urls SET redir_type = 'alias', url = :slug, checksum = :newchecksum WHERE checksum = :checksum AND url = :url AND BINARY url = BINARY :url AND (redir_type = 'custom' OR redir_type = 'auto') AND BINARY custom_url <> BINARY :slug";
			$updt_a = $db->prepare($update_to_alias_sql);
			$updt_a->execute(array(
				'checksum'		=> $checksum,
			 	'url'			=> $url,
				'slug'			=> $slug, 
				'newchecksum' 	=> (int) sprintf('%u', crc32($slug)),
			));
			unset($updt_a);
		}
	}
	elseif($existing_slug == false) 
	{	// auto-assign a slug for this new URL.
		$redir_type = 'auto';
		require_once 'library/BaseIntEncoder.php';
		
		// PREPARE
		switch(AUTO_SLUG_METHOD) {
			case 'base62':
				$glyphs = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$base = 62;
				break;
			case 'mixed-smart':
				$glyphs = '23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
				// we excluded 6 characters: 0oO1Il
				$base = 56;
				break;
			case 'base36':
				$glyphs = '0123456789abcdefghijklmnopqrstuvwxyz';
				$base = 36;
				break;
			case 'smart':
				$glyphs = '23456789abcdefghijkmnpqrstuvwxyz';
				$base = 32; //exclude 0o1l
				break;
			default:
				throw new Exception ('Unsupported method to generate unique slugs!');
				break;
		}
		$seek_count = 0; // When we need to skip tweets
		$auto_seek_pow = 1.1513;
		$custom_seek_pow = 1.031; 
		$auto_assign_sql = "SELECT base10 FROM {$prefix}autoslug "
			.'WHERE method = :method LIMIT 1';
		$auto = $db->prepare($auto_assign_sql);
		$auto->execute(array('method'=>AUTO_SLUG_METHOD)); 
		$counter = $auto->fetch(PDO::FETCH_ASSOC);
		$counter = $counter['base10'];
		
		// For binary search //TODO
		$high = $low = $counter;
		
		$total_attempts_remaining = 250;
		
		// Check to insert (seek)
		while ($slug === NULL) {
			// Never just try forever
			if($total_attempts_remaining-- < 1) {
				bc_log('Total attempts maxed out in insert loop');
				$error = 'Tried too many times seeking an available slug';
				include('pages/error.php');
				exit;
			}
			
			$slug = BaseIntEncoder::encode($counter, $glyphs, $base);
			
			// Check if slug is free
			$stmt = $db->prepare("SELECT custom_url, redir_type FROM {$prefix}urls WHERE BINARY custom_url = BINARY :slug AND custom_url = :slug");
			$stmt->execute(array('slug'=>$slug));
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if( ! $row ) //okay to insert 
			{
				$high = $counter;
				break; // found a suitable URL
			}
			else
			{
				$low = $counter;
				$counter += ceil(pow(++$seek_count, 
					($row['redir_type'] == 'custom'
						? $custom_seek_pow
						: $auto_seek_pow
					)
				));
				if(LOG_MODE) bc_log('Slug '.$slug.' already in use; its redir_type is '.$row['redir_type']
					." - ".'Incremented counter from '.$low.' to '.$counter);
				$slug = NULL;
				continue;
			}
			
		}
		
		
		// Binary Search
		if(LOG_MODE) bc_log("Before binary search: low: $low; high: $high; slug: $slug");
		// Note: Low is always "known bad" and high is always "known good"
		$high = (string)$high;
		$low = (string)$low;
		while($low != $high)
		{
			if($high == bcadd((string)$low,'1'))
			{
				$counter = $high;
				$slug = BaseIntEncoder::encode($counter, $glyphs, $base);
				if(LOG_MODE) bc_log('Binary search decided to use '.$slug.' (counter '.$counter.") because high == low+1");
				break;
			}
			
			$counter = bcadd($low, bcmul(bcsub($high, $low), '0.5', 0)); // at least +1
			$slug = BaseIntEncoder::encode($counter, $glyphs, $base);
			$stmt = $db->prepare("SELECT custom_url, redir_type FROM {$prefix}urls WHERE  custom_url = :slug AND BINARY custom_url = BINARY :slug");
			$stmt->execute(array('slug'=>$slug));
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if( ! $row ) // empty spot in the DB!
			{
				$high = $counter;
				if(LOG_MODE) bc_log($slug.' was available (counter: '.$counter.')');

			}
			else
			{
				$low = $counter;
				if(LOG_MODE) bc_log($slug.' was occupied (counter: '.$counter.')');
			}
			
		}
		
		
		$total_attempts_remaining += 50;
		$validated = true;
		// (Carefully, loopingly) Insert!
		while ($slug !== false){
			// Never just try forever
			if($total_attempts_remaining-- < 1) {
				bc_log('Total attempts maxed out in insert loop');
				$error = 'Tried too many times';
				include('pages/error.php');
				exit;
			}
			
			if( ! $validated){
				$stmt = $db->prepare("SELECT custom_url, redir_type FROM {$prefix}urls WHERE  custom_url = :slug AND BINARY custom_url = BINARY :slug");
				$stmt->execute(array('slug'=>$slug));
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if($row) 
				{
					$counter = bcadd($counter, '1');
					$slug = BaseIntEncoder::encode($counter, $glyphs, $base);
					continue;
				}
			}
			
			if(USE_BANNED_WORD_LIST){
				$banned_pos = bcurls_find_banned_word($slug);
				if($banned_pos !== FALSE) {
					if($banned_pos > 0) {
						// If slug is e.g. BANNEDa
						// we want to increment counter by (10 - a) in base $base
						// to make the slug BANNEE0
						$rollover = bcpow((string)$base, (string)$banned_pos);
						bc_log('Rollover calculated: '.$rollover);
						// and computure that a(base $base) = 11(base 10)
						$already_in = BaseIntEncoder::decode(substr($slug, 0-$banned_pos), 
							$glyphs, $base);
						bc_log('Already_in calculated: '.$already_in.' based on "'.substr($slug, 0-$banned_pos).'"');
						// so (10 in base $base) in base 10 - 11 in base 10
						$diff = bcsub($rollover,$already_in);
						$counter = bcadd($counter, $diff);
						if(LOG_MODE) bc_log('Counter += '.$diff.
							' for banned word,  is now '.$counter.' - slug '.$slug);
					} else {
						$counter = bcadd($counter, '1');
						if(LOG_MODE) bc_log('Counter++ for banned word in slug '.$slug
							.', counter is now '.$counter);
					}
					$slug = BaseIntEncoder::encode($counter, $glyphs, $base);
					$validated = false;
					continue;
				}	
			}			
			if(ADDITIONAL_HOMOGLYPHS_TO_AVOID){
				$banned_pos = bcurls_find_banned_glyph($slug);
				if($banned_pos !== FALSE) {
					if($banned_pos > 0) {
						$rollover = bcpow((string)$base, (string)$banned_pos);
						bc_log('Rollover calculated: '.$rollover);
						$already_in = BaseIntEncoder::decode(substr($slug, 0-$banned_pos), 
							$glyphs, $base);
						bc_log('Already_in calculated: '.$already_in.' based on "'.substr($slug, 0-$banned_pos).'"');
						$diff = bcsub($rollover,$already_in);
						$counter = bcadd($counter, $diff);
						if(LOG_MODE) bc_log('Counter += '.$diff.
							' for homoglyphs,  is now '.$counter.' - slug '.$slug);
					} else {
						$counter = bcadd($counter, '1');
						if(LOG_MODE) bc_log('Counter++ for homoglyph in slug '.$slug
							.', counter is now '.$counter);
					}
					$slug = BaseIntEncoder::encode($counter, $glyphs, $base);
					$validated = false;
					continue;
				}	
			}
			
			// Actually insert 
			try{
				$insert_result = bcurls_insert_url ($url, $checksum, $slug, $redir_type);
				if($insert_result !== true) {
					bc_log('Insertion result (not true)'.(string)$insert_result);
					$counter = bcadd($counter, '1');
					$slug = BaseIntEncoder::encode($counter, $glyphs, $base);	
					$validated = false;
					continue;			
				} 
			} catch(Exception $e){
				bc_log('Exception inserting or incrementing counter.'.$e);
				$error = $e;
				include('pages/error.php');
				exit;
			}
			bc_log('About to increment counter');
			// Update counter
			try{
				// Update counter 
				// Note it would be great if this was a strictly incremental
				// and atomic operation: "If your current base10 is smaller
				// than mine, change it to mine, database!"
				// This would deal with simultaneous inserts.
				$counter_update_sql = "UPDATE {$prefix}autoslug 
					SET base10 = :base10
					WHERE method = :method LIMIT 1";
				$ctr_up = $db->prepare($counter_update_sql);
				$ctr_up_res = $ctr_up->execute(array(
					'method' => AUTO_SLUG_METHOD, 
					'base10' => bcadd((string)$counter,'1'
				))); 
				if(! $ctr_up_res) {
					$error = 'Could not update the autoslug index (this will hurt insertion performance.) '
						.$ctr_up->errorCode().': '.$ctr_up->errorInfo();
					bc_log('Counter increment error. '.$error);
				}
			}
			catch (Exception $e2) {
				$error = (string) $e2;
				bc_log('Could not update counter. '.$e2);
			}
			
			break;
		}
		
		if(LOG_MODE) bc_log('$total_attempts_remaining after finish: '.$total_attempts_remaining);
	}
	else 
	{	// This is already in the DB, don't provide a new one
		$slug = $existing_slug;
	}
	

	$new_url = BCURLS_URL.$slug;
	
	if (isset($_GET['tweet']))
	{
		$_GET['redirect'] = 'http://twitter.com/?status=%l';
	}
	if (isset($_GET['redirect']))
	{
		header('Location:'.str_replace('%l', urlencode(' '.$new_url), $_GET['redirect']));
		exit();
	}
	if (isset($_GET['api']))
	{
		echo $new_url;
		exit();
	}
	
	include('pages/done.php');
}
else if(isset($_GET['stats']))  // Display stats
{
	$top_urls = stats_top_urls($db);
	$top_referers = stats_top_referers($db);
	$todays_urls = stats_todays_stats($db);
	$weeks_urls = stats_thisweeks_stats($db);
	$number_lessnd = stats_total_lessnd($db);
	$number_redirected = stats_total_redirects($db);
	include('pages/stats.php');
}
elseif(isset($_GET['mark_gone']) && isset($_GET['slug']) && strlen(trim($_GET['slug']))) 
{ // Mark a redirection as GONE
	// TODO
}
else
{
	include('pages/add.php');
}