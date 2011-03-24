<?php include('stubs/header.php'); ?>
<form method="post">
	<input type="text" 		name="username" placeholder="username" id="username" />
	<input type="password" 	name="password" placeholder="password" />
	<button>Login</button>
</form>
<script>
document.getElementById('username').focus();
</script>
<?php include('stubs/footer.php'); ?>