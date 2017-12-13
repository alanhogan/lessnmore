<?php
$backtrace = debug_backtrace();
$id = basename($backtrace[0]['file'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title><?php echo htmlentities(APP_NAME) ?></title>
<meta name="generator" content="<?php echo htmlentities(APP_NAME .' '. BCURLS_VERSION);?>" />
<style>
<?php include BCURLS_PATH . '/css/lessn.css'; ?>
</style>
<script defer src="//use.fontawesome.com/releases/v5.0.1/js/all.js"></script>
</head>
<body id="<?php echo $id; ?>">
<div id="top-bar">
  <div>
    <a href="?">Lessn More <?php echo BCURLS_VERSION ?></a>
  </div>

  <?php if (user_is_logged_in()) { ?>
    <div>
      <a class="top-logout-link" href="?logout">Log Out</a>
    </div>
  <?php } ?>
</div>
<div id="wrap">
