<?php include_once $_SERVER['DOCUMENT_ROOT'] .
    'jokes-database/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php htmlout($pageTitle);?></title>
	</head>
	<body>
		<h1><?php htmlout($pageTitle);?></h1>
		<form action="?<?php htmlout($action);?>" method="post">
		    <label for="name">Name:<input type="text" name="name" id="name" value="<?php htmlout($name);?>">
		    <input type="hidden" name="id" id="id" value="<?php htmlout($id);?>">
		    <input type="submit" value="<?php htmlout($button);?>">
		</form>
	</body>
</html>
