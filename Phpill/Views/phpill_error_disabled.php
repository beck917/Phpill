<?php  
if (Router::$routed_uri == 'sns' || Router::$routed_uri == 'page') {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?php echo $error ?></title>
</head>
<body>
<style type="text/css">
<?php include Phpill::find_file('Views', 'phpill_errors', FALSE, 'css') ?>
</style>
<div id="framework_error" style="width:24em;margin:50px auto;">
<h3><?php echo Phpill\Helpers\Html::specialchars($error) ?></h3>
<p style="text-align:center"><?php echo $message ?></p>
</div>
</body>
</html>
<?php 
} else {
	Network::buffer_error(-10000, "inner_server_error");
}
?>