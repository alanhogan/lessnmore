<?php include('stubs/header.php'); ?>

<?php if(isset($error) && strlen($error)) : ?>
<p class="error"><?php echo htmlentities($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<p>
	<!-- Using shortlink class. See http://ajh.us/k -->
	<input type="text" id="url" class="shortlink" value="<?php echo htmlspecialchars($new_url); ?>" onclick="this.focus();this.select();" />
	→ <strong><?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?></strong>
</p>
<p>
	<a href="http://twitter.com/?status=<?php echo urlencode($new_url); ?>">Tweet</a>
</p>

<?php if(isset($bc_log) && strlen($bc_log)) : ?>
<p class="logs"><?php echo nl2br(htmlentities($bc_log, ENT_QUOTES, 'UTF-8')) ?></p>
<?php endif; ?>
<script>
var input = document.getElementById('url');
input.focus();
input.select();
</script>
<?php include('stubs/footer.php'); ?>