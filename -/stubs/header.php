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
</head>
<body id="<?php echo $id; ?>">
<div id="wrap">