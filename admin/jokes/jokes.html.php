<?php include_once $_SERVER['DOCUMENT_ROOT'] .
    'jokes-database/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Manage Jokes: Search Results</title>
  </head>
  <body>
    <h1>Search Results</h1>
    <p><a href="?add"></a></p>
    <table>
    	<?php if(isset($jokes)): ?>
    		<tr><th>Joke Text</th><th>Options</th></tr>
	    	<?php foreach($jokes as $joke): ?>
	    		<tr>
	    			<td><?php htmlout($joke['text']); ?></td>
	    			<td>
	    				<form action="" method="post">
	    					<div>
	    						<input type="hidden" name="id" value="<?php htmlout($joke['id']);?>">
	    						<input type="submit" name="action" value="Edit">
	    						<input type="submit" name="action" value="Delete">
   	    					</div>
	    				</form>
	    			</td>
    			</tr>
			<?php endforeach;?>
    	<?php endif ?>
    </table>
    <?php if(!isset($jokes)): ?>
    		<p>No jokes were found that fit your criteria.</p>
    <?php endif; ?>
    <p><a href="?">New Search</a></p>
    <p><a href="..">Return to JMS home</a></p>
    <?php include $_SERVER['DOCUMENT_ROOT'] . 'includes/logout.inc.html.php'; ?>
</body>
</html>