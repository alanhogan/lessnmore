Lessn More 2.1.1
================

Homepage: <http://lessnmore.net>  
Source/Fork: <http://github.com/alanhogan/lessnmore>

Lessn More is a free, open-source personal URL shortener.

## Features

*	The ability to use custom short URLs (slugs), unlike Lessn
*	A bookmarklet that even supports custom short URLs
*	An [API][] that supports the same commands as the web interface
*	Different auto-shorten modes (optional mixed case),
*	The ability to avoid lookalike characters, and 
*	An optional "banned word list" to prevent auto-generating offensive URLs.
*	Support for more shortened URLs than Lessn
*	The ability to add multiple slugs that point to the same long URL, unlike Lessn
*	Trims punctuation from the right of the slug, per [best practices][bestp]

### Attention to detail

*	Adding a new slug for a URL already in the database will become the "canonical"
	short URL, and will be returned if you ask Lessn More (either by API or not)
	for a short URL to the original resource
*	Lessn More lets you change the character set you want to use
	to generate short URLs on-the-fly with minimal or no "wasted" or
	skipped possible slugs, and yet the insertion algorithm
	is fast. (Not as fast as Lessn 1.0, by necessity, since Lessn 1.0 did not allow
	custom short URLs; but the worst-case insertion time after
	upgrade or a switch of insertion algorithms is on the order of O(log(n)) where
	<var>n</var> is the number of redirections in your database, and the common case is
	on the order of O(1) (constant time).)
*	Compliant with [URL shortener best practices and standards][bestp]
	whenever possible
*	An easy migration script will upgrade your database 
	from an existing Lessn migration.

### Caveats

*	This shortener is not appropriate when there is a good chance that two or more URLs
	will be shrunk at the same time. (Simultaneous reads are, of course, fine.)
*	Lessn More 2.0 is a new release and has not been fully tested on databases 
	other than MySQL. YMMV. Please [report any issues][issues].
*	Changing settings such as the allowed character set, while a supported use case,
	should be done seldom, and with deliberation.
*    There is currently a [bug in SQLite support](https://github.com/alanhogan/lessnmore/issues/#issue/7).

[markdn]:  http://bit.ly/mkdnsyntax   "This document is written in Markdown."
[convert]: http://tinyurl.com/mkdnwmd "Markdown editor with instant HTML preview"

[bestp]:   http://alanhogan.com/tips/rel-shortlink-for-short-urls "Everything you need to know about rel-shortlink and short URLs"

[issues]:  http://github.com/alanhogan/lessnmore/issues "Bugs & Issues on GitHub"
[API]:     http://lessnmore.net/api "Lessn More API documentation"

Requirements
-------------

* PHP 5.1+
* PHP's PDO
* MySQL, PostgreSQL, or SQLite <small>(though [see this](https://github.com/alanhogan/lessnmore/issues/#issue/7) for SQLite support)</small>
* mod_rewrite or similar rewrite system (see .htaccess)


History
-------

### v1.0

Lessn was the original personal URL shortening service,
written by [Shaun Inman](http://shauninman.com/). It required PHP, MySQL, and mod_rewrite.

### v1.1

Buttered URLs is a Lessn [fork](http://github.com/jfro/butteredurls) by [Jeremy Knope](http://buttered-cat.com/).
Buttered URLs added logging, custom URLs, a migration mechanism, and support for more database types.

### v2.0

Lessn More is a Buttered URLs [fork](http://github.com/alanhogan/lessnmore) by [Alan Hogan](http://alanhogan.com/).
Lessn More increased the robustness of the insertion algorithm,
prevented slug conflicts, updated the bookmarklets, added multiple auto-shorten modes,
banned word lists, and enhanced security.

### v2.1

Trims punctuation from the right of the slug, per [best practices](http://alanhogan.com/tips/rel-shortlink-for-short-urls)


Legal
-----

Lessn is offered as-is, sans support and without warranty.
Copyright © 2009-10 Shaun Inman and contributors.
Offered under a BSD-like license; see license.txt.

Installation
------------

Installation instructions are different depending on if you are upgrading or doing a fresh install.

### Fresh Install ###

**ONLY** follow these instructions if you are not upgrading!

1. Open /-/config.php in a plaintext editor and
	create a Lessn username and password then enter your
	database connection details.
	You may also choose other settings such as
	authentication salts and a default home page.

2. For the shortest URLs possible, upload the contents of this
	directory to your domain's root public folder.

3. Visit http://doma.in/-/ to log in & start using Lessn More!
	Be sure to grab the bookmarklets. (The required database table is created 
	automatically the first time you visit Lessn).

**NOTE:** If your Lessn'd urls aren't working you probably didn't
upload the .htaccess file. Enable "Show invisible files" 
in your FTP application. It's also possible that your host doesn't like
the <IfModule>; try taking it out (this happens on 1and1).

Upgrading
----------

If you are upgrading from a previous version of Lessn or ButteredURLs:

### Upgrading from Lessn 1.0.0 or 1.0.1

1. Using a tool like PhpMyAdmin or the MySQL CLI change the 
   checksum index type to INDEX (from UNIQUE).
2.	Continue below with "ALL VERSIONS"

### ALL VERSIONS: Upgrading to Lessn More 2.0

1.	You are strongly encouraged to back up your database.
1.	Note some old redirections so you can manually check they still work after upgrading (they should, but hey, it's important).
1.	Manually merge your old configs into the new config file.
	There will be new options you will want to make
	decisions about.
1.	Upload all lessn/BU files, excluding config.php, or making sure to use the new one.
1.	Go to http://doma.in/install.php?start=N where 
	N is 2 if upgrading from Lessen 1.0, or    
	N is 4 if upgrading from ButteredURLs 1.1.
1.	Test some old known working redirections
1.	Delete install.php.
1.	Grab the new bookmarklets with custom short URL support!

**Congratulations.** You are running the latest version of Lessn More.


API
---

You can find [API documentation here][API].
It's super simple.

Issues
-------

To report an issue or check known issues, visit [the Lessn More issue tracker on GitHub][issues].
