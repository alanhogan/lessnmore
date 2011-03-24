<?php
/* (c) 2009 By Travell Perkins http://snook.ca/archives/php/url-shortener#c63597
* Modified by Alan Hogan 2010
*/
class BaseIntEncoder {
	static function encode($n, $codeset, $base){
	    // $base = strlen($codeset);
	    $converted = '';

	    while ($n > 0) {
	        $converted = substr($codeset, bcmod($n,$base), 1) . $converted;
	        $n = self::bcFloor(bcdiv($n, $base));
	    }

	    return $converted ;
	}

	static function decode($code, $codeset, $base){
	    // $base = strlen($codeset);
	    $c = '0';
	    for ($i = strlen($code); $i; $i--) {
	        $c = bcadd($c,bcmul(strpos($codeset, substr($code, (-1 * ( $i - strlen($code) )),1))
	                ,bcpow($base,$i-1)));
	    }

	    return bcmul($c, 1, 0);
	}

	static function bcFloor($x)
	{
	    return bcmul($x, '1', 0);
	}

	static function bcCeil($x)
	{
	    $floor = self::bcFloor($x);
	    return bcadd($floor, ceil(bcsub($x, $floor)));
	}

	static function bcRound($x)
	{
	    $floor = self::bcFloor($x);
	    return bcadd($floor, round(bcsub($x, $floor)));
	}
}