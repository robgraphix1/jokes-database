<?php


//set up the add form
if(isset($_GET['add']))

{
	$pageTitle = 'Add a Joke Category';
	$action = 'addform';
	$name = '';
	$id = '';
	$button = 'Add category';

	include 'form.html.php';
	exit();
}

//add a category
if(isset($_GET['addform']))
{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';
	try
	{
		$sql = 'INSERT INTO category SET name = :name';
		$s = $pdo->prepare($sql);
		$s->bindValue(':name', $_POST['name']);
		$s->execute();			
	}
	catch (PDOException $e)
	{
		$error = 'Error adding joke to the database.';
		include 'error.html.php';
		exit();		
	}

	header('Location: .');	
}


//set up edit form
if(isset($_POST['action']) and $_POST['action'] == 'Edit')
{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';
	try
	{
		$sql = 'SELECT name, id FROM category WHERE id = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
	}
	catch(PDOException $e)
	{
		$error = 'Error fetching values from database.';
		include 'error.html.php';
		exit();
	}

	$row = $s->fetch();
	$pageTitle = 'Edit category';
	$action = 'editform';
	$name =$row['name'];
	$id = $row['id'];
	$button = 'Edit category';

	include 'form.html.php';
	exit();
}


//edit the category
if(isset($_GET['editform']))
{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';
	try
	{
		$sql = 'UPDATE category SET name = :name WHERE id = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->bindValue(':name', $_POST['name']);
		$s->execute();
	}
	catch(PDOException $e)
	{
		$error = 'Error updating the database.';
		include 'error.html.php';
		exit();
	}

	header('Location: .');
}



// delete 
if(isset($_POST['action']) and $_POST['action'] == 'Delete')
{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';
	//delete categories
	try
	{
		$sql = 'DELETE FROM jokecategory WHERE categoryid = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
	}
	catch(PDOException $e)
	{
		$error = 'Error deleting jokes from category.';
		include 'error.html.php';
		exit();
	}

	//delete category/joke associations
	try
	{
		$sql = 'DELETE FROM category WHERE id = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
	}
	catch(PDOException $e)
	{
		$error = 'Error deleting category from database.';
		include 'error.html.php';
		exit();
	}

	header('Location: .');
}





//display category list
include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';
try
{
	$sql = 'SELECT name, id FROM category;';
	$result = $pdo->query($sql);
}
catch(PDOException $e)
{
	$error = 'Error fetching categories from database.';
	include error.html.php;
	exit();
}

foreach($result as $row)
{
	$categories[] = array('id' =>$row['id'], 'name' =>$row['name']);
}
include 'categories.html.php';


