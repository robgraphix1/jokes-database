<?php

include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/magicquotes.inc.php';

if(isset($_GET['add']))
{
	$pageTitle = 'New Author';
	$action = 'addform';
	$name = '';
	$email = '';
	$id = '';
	$button = 'Add author';

	include 'form.html.php';
	exit();
}


if(isset($_GET['addform']))
{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

	try
	{
		$sql = 'INSERT INTO author SET 
		name = :name,
		email = :email';
		$s = $pdo->prepare($sql);
		$s->bindValue(':name', $_POST['name']);
		$s->bindValue(':email', $_POST['email']);
		$s->execute();

	}
	catch(PDOException $e)
	{
		$error = "Error adding submitted author";
		include 'error.html.php';
		exit();
	}
}


if(isset($_POST['action']) and $_POST['action'] == 'Edit')

{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

	try
	{
		$sql = 'SELECT name, email, id FROM author WHERE id = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
	}
	catch(PDOExcetion $e)
	{
		$error = "Error fetching author details";
		exit();

	}

	$row = $s->fetch();
	$button = 'Edit author';
	$action = 'editform';
	$pageTitle = 'Edit Author';
	$name = $row['name'];
	$email = $row['email'];
	$id = $row['id'];

	include 'form.html.php';
	exit();
}

if(isset($_GET['editform']))
{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

	try
	{
		$sql = 'UPDATE author SET
		name = :name,
		email = :email
		WHERE id = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':name', $_POST['name']);
		$s->bindValue(':email', $_POST['email']);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();


	}
	catch(PDOException $e)
	{
		$error = "Error editing author.";
		include 'error.html.php';
		exit();
	}

	header('Location: .');
	exit();

}



if(isset($_POST['action']) and $_POST['action'] == 'Delete')
{	
	include 'confirm-author-delete.html.php';
	exit();
}

		if(isset($_POST['confirm']) and $_POST['confirm'] == 'Confirm')
		{


			include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

			//get jokes that belong to the author
			try
			{
				$sql = 'SELECT id FROM joke WHERE authorid = :id';
				$s = $pdo->prepare($sql);
				$s->bindValue(':id', $_POST['id']);
				$s->execute();
			}
			catch(PDOExcetion $e)
			{
				$error = "Error fetching jokes from database";
				include 'error.html.php';
				exit();
			}

			$result = $s->fetchAll();

			//delete joke category entries
			try
			{
				$sql = 'DELETE FROM jokecategory WHERE jokeid = :id';
				$s = $pdo->prepare($sql);
				
				//for each joke we have to shuffle through because the joke id can be more than one
				foreach($result as $row)
				{
					$jokeId = $row['id'];
					$s->bindValue(':id', $jokeId);
					$s->execute();
				}

			}
			catch(PDOException $e)
			{
				$error = 'Error deleting jokes from database';
				include 'error.html.php';
				exit();
			}


			//delete jokes belonging to the author
			try  
			{
				$sql = 'DELETE FROM joke WHERE authorid = :id';
				$s = $pdo->prepare($sql);
				$s->bindValue(':id', $_POST['id']);
				$s->execute();

			}
			catch(PDOException $e)
			{
				$error = "Error deleteing jokes from database";
				include 'error.html.php';
				exit();
			}

			//delete author
			try
			{
				$sql = 'DELETE FROM author WHERE id = :id';
				$s = $pdo->prepare($sql);
				$s->bindValue(':id', $_POST['id']);
				$s->execute();
			}
			catch(PDOException $e)
			{
				$error = "Error deleting author from database.";
				include 'error.html.php';
				exit();
			}

			header('Location: .');
			exit();

		
}






//dispay author list

include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

try
{
	$result = $pdo->query('SELECT id, name FROM author');
}
catch(PDOException $e)
{
	$error = 'Error fetching authors from the database';
	include 'error.html.php';
	exit();
}

foreach($result as $row)
{
	$authors[] = array('id' =>$row['id'], 'name' =>$row['name']);
}

include 'authors.html.php';