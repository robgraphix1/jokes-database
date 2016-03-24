<?php include_once $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/helpers.inc.php';
?>
<html>
	<head>
	</head>
	<body>
		<h1>Joke Categories</h1>
		<a href="?add">Add a category</a>
		<ul> 
			<?php foreach($categories as $category): ?>
				<li>
					<form action="" method="post">
						<div>
							<?php htmlout($category['name']);?>
							<input type="hidden" name="id" value="<?php echo $category['id']?>">
							<input type="submit" name="action" value="Edit">
							<input type="submit" name="action" value="Delete">
						</div>
					</form>
				</li>
			<?php endforeach; ?>
		</ul>
		<p><a href="..">Return to JMS home</a>
		<?php include $_SERVER['DOCUMENT_ROOT'] . 'includes/logout.inc.html.php'; ?>
	</body>
</html>