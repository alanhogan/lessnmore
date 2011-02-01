<?php include('stubs/header.php'); ?>
<p>
	<a href="<?php echo BCURLS_URL ?>-/">Back</a>
</p>
<p id="lessnd"><?php echo APP_NAME ?> has shortened <strong><?php echo $number_lessnd; ?></strong> URLs to date.</p>
<h2>Today</h2>
<table cellspacing="0" id="today">
	<tr>
		<th class="longurl">URL</th>
		<th class="shorturl">Lessn'd</th>
		<th class="hits">Hits</th>
	</tr>
<?php foreach($todays_urls as $url) { 
	$short = htmlspecialchars(BCURLS_URL.$url['custom_url'], ENT_QUOTES, 'UTF-8');
	?>
	<tr>
		<td><?php echo htmlspecialchars($url['url'], ENT_QUOTES, 'UTF-8')?></td>
		<td><!-- <a href="<?php /*=$short*/ ?>"> --><?php echo $short?><!-- </a> --></td>
		<td><?php echo $url['hits']?></td>
	</tr>
<?php }
	if ( empty($todays_urls) ) {
		echo '<td>Nothing</td><td>to</td><td>report</td>';
	}
?>
</table>

<h2>This Week</h2>
<table cellspacing="0" id="this-week">
	<tr>
		<th class="longurl">URL</th>
		<th class="shorturl">Lessn'd</th>
		<th class="hits">Hits</th>
	</tr>
<?php foreach($weeks_urls as $url) { 
	$short = htmlspecialchars(BCURLS_URL.$url['custom_url'], ENT_QUOTES, 'UTF-8');
	?>
	<tr>
		<td><?php echo htmlspecialchars($url['url'], ENT_QUOTES, 'UTF-8')?></td>
		<td><!-- <a href="<?php /*=$short*/ ?>"> --><?php echo $short?><!-- </a> --></td>
		<td><?php echo $url['hits']?></td>
	</tr>
<?php }
	if ( empty($todays_urls) ) {
		echo '<td>Nothing</td><td>to</td><td>report</td>';
	}
?>
</table>

<h2>All Time</h2>
<table cellspacing="0" id="all-time">
	<tr>
		<th class="longurl">URL</th>
		<th class="shorturl">Lessn'd</th>
		<th class="hits">Hits</th>
	</tr>
	
<?php foreach($top_urls as $url) { 
	$short = htmlspecialchars(BCURLS_URL.$url['custom_url'], ENT_QUOTES, 'UTF-8');
	?>
	<tr>
		<td><?php echo htmlspecialchars($url['url'], ENT_QUOTES, 'UTF-8')?></td>
		<td><!-- <a href="<?php /*=$short*/ ?>"> --><?php echo $short?><!-- </a> --></td>
		<td><?php echo $url['hits']?></td>
	</tr>
<?php } ?>

</table>

<h2>Referrers</h2>
<table cellspacing="0" id="referrers">
	<tr>
		<th class="referers">Referrer</th>
		<th class="hits">Hits</th>
	</tr>
	
<?php foreach($top_referers as $url) { ?>
<tr>
	<td><?php echo htmlspecialchars($url['referer'], ENT_QUOTES, 'UTF-8')?></td>
	<td><?php echo $url['hits']?></td>
</tr>
<?php } ?>

</table>
<script src="<?php echo BCURLS_URL ?>-/js/loader.php"></script>
<script>
<?php
	include BCURLS_PATH . '/js/lessn.js';
?>
</script>
<?php include('stubs/footer.php'); ?>
