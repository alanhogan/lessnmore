Lessn More 2.5.1
================

Homepage: <https://lessnmore.net>   
Source/Fork: <https://github.com/alanhogan/lessnmore>

Lessn More is a free, open-source personal URL shortener with a lot of enhancements over the original 2009 Lessn.

## Features

*	The option to specify custom short URLs (`/rickroll`) when shrinking
*	Your choice of bit.ly-style random URLs (`/fN6rBX`) or incremental (`/9`, `/a`, `/b`…)
*	Bookmarklets for shrinking (and/or tweeting) URLs — with support for custom short URLs
*	[Statistics][statsimg] on redirection usage
*	An [API][] that supports the same commands as the web interface
*	Your choice of all-lowercase or mixed-case short URLs, and whether or not to avoid lookalike characters
*	An optional "banned word list" to prevent auto-generating offensive URLs
*	Theoretically works with a huge number of URLs in the database
*	The ability to add multiple redirections that point to the same long URL
*	Trims punctuation from the right of the slug (the unique part of the short URL), per [best practices][bestp]
*	Compatible with MySQL for sure and _should_ work with Postgres and SQLite (these DBs are not officially supported, so use at your own risk and please contribute fixes)
*	Will return `http:` or `https:` short URLs to match the current protocol your server
	is responding to, by default; you can manually specify your preferred protocol

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
*	An [easy migration script](https://lessnmore.net/images/installation-script-screen-shot.png) will upgrade your database
	from an existing Lessn or Lessn More installation.

### Caveats

*	It is not recommended to use this shortener in incremental mode
	when there is a good chance that two or more URLs
	will be shrunk at the same time. (Simultaneous reads are, of course, fine.)
*	Lessn More is developed against MySQL and other databases are theoretically supported
	via PDO, but YMMV. Please [report any issues][issues].
*	Changing the character set used for URL generation, when in conjunction with incremental mode,
	is a supported use case but should be done seldom, and with deliberation.

[markdn]:  http://bit.ly/mkdnsyntax   "This document is written in Markdown."
[convert]: http://tinyurl.com/mkdnwmd "Markdown editor with instant HTML preview"

[bestp]:   https://alanhogan.com/tips/rel-shortlink-for-short-urls "Everything you need to know about rel-shortlink and short URLs"

[issues]:  https://github.com/alanhogan/lessnmore/issues "Bugs & Issues on GitHub"
[API]:     https://lessnmore.net/api "Lessn More API documentation"
[statsimg]: https://lessnmore.net/images/stats-this-week-lessn-more.png

Requirements
-------------

* PHP 5.6 or newer
* PHP's PDO (usually included in PHP builds)
* Database: MySQL (most solidly supported), PostgreSQL, or SQLite <small>(though [see this](https://github.com/alanhogan/lessnmore/issues/7) for SQLite support)</small>
* While your server doesn’t need to be Apache, the project ships with an Apache `.htaccess` file to handle redirecting requests to the Lessn More redirection engine, for your convenience. Michael McKelvaney has written about [using Nginx instead][with-nginx]. For IIS, [bigworm reports success with this](https://github.com/alanhogan/lessnmore/issues/24).

[with-nginx]: http://mckelvaney.co.uk/blog/2013/04/30/deploying-lessn-more-with-nginx-and-try-files/


History
-------

### v1.0

Lessn was the original personal URL shortening service,
written by [Shaun Inman](http://shauninman.com/). It required PHP, MySQL, and mod_rewrite.

### v1.1

Buttered URLs is a Lessn [fork](https://github.com/jfro/butteredurls) by [Jeremy Knope](http://buttered-cat.com/).
Buttered URLs added logging, custom URLs, a migration mechanism, and support for more database types.

### v2.0

Lessn More is a Buttered URLs [fork](https://github.com/alanhogan/lessnmore) by [Alan Hogan](https://alanhogan.com/).
Lessn More increased the robustness of the insertion algorithm,
prevented slug conflicts, updated the bookmarklets, added multiple auto-shorten modes,
banned word lists, and enhanced security.

### v2.1

Trims punctuation from the right of the slug, per [best practices](https://alanhogan.com/tips/rel-shortlink-for-short-urls)

### v2.2

Better stats page.

More compatible with sqlite (see [#7][]).

Other small fixes and improvements.

Supports a custom timezone in `config.php`.

Special thanks to [Matt Wiebe](https://github.com/mattwiebe) for a lot of these changes.

### v2.3

Returns `https://` short URLs when running on a secure server.

Allows shrinking of non-Web URIs such as `ftp:` or `magnet:`.

### 2.4.0

Users logging in over HTTPS will be authenticated with a cookie that will not leak out over HTTPS

Cookies are now set with HTTP_ONLY, which means rogue JavaScript cannot steal your authentication cookie

There is now a LOG OUT button

Lessn More now checks for an updated version of itself whenever you use the web interface. (You still have to manually upgrade!)

### 2.4.1

Stopped checking for new versions of Lessn More whiled logged out, because it will always fail then.

### 2.5.0

**Big news!** Now you can configure Lessn More to generate random slugs of a desired length instead of incremental, guessable slugs. This means you will generate URLs like `/8RTvaN`, `/Nvb3QP` instead of `/a`, `/b`, `/c`, if desired.

The link in LM's header now points to your main admin page,  and the update check info in the footer will always link to the LM project home page.

### 2.5.1

Fixes JavaScript and the Back button on the stats page when on HTTPS.


Legal
-----

Lessn is offered as-is, sans support and without warranty.
Copyright © 2009-10 Shaun Inman and contributors.
Offered under a BSD-like license; see license.txt.

Installation
------------

Installation instructions are different depending on if you are upgrading or doing a fresh install.

All the following refer to files inside the `dist` folder.

### Fresh Install ###

**ONLY** follow these instructions if you are not upgrading!

0. Copy or rename /-/config-example.php to /-/config.php.

1. Open /-/config.php in a plaintext editor and
	create a Lessn username and password then enter your database connection details. You may also choose other settings such as authentication salts, a default home page, and your current time zone.

2. For the shortest URLs possible, upload the contents of this directory (`dist`) to your domain's root public folder.

3. Visit http://doma.in/install.php to create the necessary database tables. (Watch for errors.)

4. Visit http://doma.in/-/ to log in & start using Lessn More! Be sure to grab the bookmarklets.

**NOTE:** If your Lessn'd urls aren't working you probably didn't
upload the .htaccess file. Enable "Show invisible files"
in your FTP application. It's also possible that your host doesn't like
the `<IfModule>` directives; try removed them and just leaving the
`Rewrite*` lines that were wrapped by the `<IfModule>`.
(This seems to happen on 1and1).
Or, maybe you aren’t on Apache. That’s OK, probably — see the Requirements section.

Upgrading
----------

If you are upgrading from a previous version of Lessn or ButteredURLs:

### Upgrading from Lessn 1.0.0 or 1.0.1

1. Using a tool like PhpMyAdmin or the MySQL CLI change the
   checksum index type to INDEX (from UNIQUE).
2.	Continue below with "ALL VERSIONS"

### ALL VERSIONS: Upgrading to Lessn More 2

1.	You are strongly encouraged to back up your database.
1.	Note some old redirections so you can manually check they still work after upgrading (they should, but hey, it's important).
1.	Manually merge your old configs into the new config file.
	There will be new options you will want to make
	decisions about.
1.	Upload all lessn/BU files, excluding config.php, or making sure to use the new one.
1.	Go to http://doma.in/install.php?start=N where
	N is 2 if upgrading from Lessn 1.0, or    
	N is 4 if upgrading from ButteredURLs 1.1.
1.	Test some old known working redirections
1.	Delete install.php.
1.	Grab the new bookmarklets with custom short URL support!

**Congratulations.** You are running the latest version of Lessn More.

### Upgrading from Lessn More 2.0 or 2.1 to LM 2.2

Just upload the latest copy of Lessn More to your server, but skip or delete install.php.

Note that since v2.2, there is only a config-example.php file in the distribution, and no config.php file. You will _not_ need to take care to avoid overwriting your existing config.php file, but do take care not to replace the whole `/-/` folder and your config.php file; please make a backup copy of it before upgrading.

### Upgrading from Lessn More 2.2 to 2.3

You only need the new `/-/index.php` for this upgrade. Optionally, add the new
`PROTOCOL_OVERRIDE` constant from `config-example.php` into your `config.php`.

### Upgrading from 2.3.0 to 2.4.0

Just copy over all the files in `dist/` to your web server’s root (or wherever you previously installed Lessn More). Skip or delete install.php.


### Upgrading from 2.4 to 2.5.0

Please replace the files on your site with those in the `dist/` folder (keeping your config file). Add the new constant `RANDOM_SLUG_LENGTH` to your `config.php`. Be sure you get the new `random_compat` folder in `/-/library` if you are not on on PHP 7 or newer.

### Upgrading from 2.5.0 to 2.5.1

Only `/-/pages/stats.php` changed in this release.


API
---

You can find [API documentation here][API].
It's super simple.

Issues
-------

To report an issue or check known issues, visit [the Lessn More issue tracker on GitHub][issues].

[#7]: https://github.com/alanhogan/lessnmore/issues/7

Installation tips
-----------------

1. You may need to install the following packages for PHP:

	* php-bcmath
	* php-pdo

2. If you are getting 404 Not Found errors when you try to use the shortlinks, and you have got the `.htaccess` file in place correctly, check if your Apache httpd.conf has `AllowOverride` set to `All`. If it is `None` then the .htaccess will get ignored.

3. To help debug installation problems, add this line to the end of ./-/config.php:

		define('DISPLAY_ERRORS', true);
