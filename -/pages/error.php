<?php defined('OKAY_TO_SHOW_PAGES') OR DIE('Unauthenticated!');

if(isset($_GET['api']))
	exit($error);

include('stubs/header.php'); ?>
<h1>Error</h1>
<p class="error">
	<?php echo htmlentities($error) ?>
</p>
<p>
	<a href="/-/">Back</a>
</p>

<?php if(isset($bc_log) && strlen($bc_log)) : ?>
<p class="logs"><?php echo nl2br(htmlentities($bc_log, ENT_QUOTES, 'UTF-8')) ?></p>
<?php endif; ?>
<?php include('stubs/footer.php'); ?>
