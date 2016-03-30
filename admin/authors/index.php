<?php

include_once $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/magicquotes.inc.php';

require_once $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/access.inc.php';

//check to see if user is logged in
if(!userIsLoggedIn())
{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/admin/login.html.php';
	exit();
}

//check to see if user has access rights
if(!userHasRole('Account Administrator'))
{
	$error = 'Only Account Administrators may access this page.';
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/admin/accessdenied.html.php';
	exit();
}

if(isset($_GET['add']))
{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

	$pageTitle = 'New Author';
	$action = 'addform';
	$name = '';
	$email = '';
	$id = '';
	$button = 'Add author';

	try
	{
		$result = $pdo->query('SELECT id, description FROM role');
	}
	catch(PDOException $e)
	{
		$error = "Error fetching the list of roles.";
		include 'error.html.php';
		exit();
	}

	foreach($result as $row)
	{
	$roles[] = array('id'=>$row['id'], 'description'=>$row['description'],
	'selected'=>FALSE);	
	}

	include 'form.html.php';
	exit();
}

//set up the add form
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

	$authorid = $pdo->lastInsertId();

	if($_POST['password'] != '')
	{
		$password = md5($_POST['password'] . 'ijdb');

		try
		{
			$sql = 'UPDATE author SET
			password = :password
			WHERE id = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':password',$password);
			$s->bindValue(':id', $authorid);
			$s->execute();
		}
		catch(PDOException $e)
		{
			$error = "Error setting author password.";
			include 'error.html.php';
			exit();
		}
	}

	if(isset($_POST['roles']))
	{
		foreach($_POST['roles'] as $role)
		{
			try
			{
				$sql = 'INSERT INTO authorrole SET
				authorid = :authorid,
				roleid = :roleid';
				$s = $pdo->prepare($sql);
				$s->bindValue(':authorid', $authorid);
				$s->bindValue(':roleid', $role);
				$s->execute();
			}
			catch(PDOException $e)
			{
				$error = "Error setting selected author roles.";
				include 'error.html.php';
				exit();
			}
		}
	}
	header('Location: .');
	exit();
}


if(isset($_POST['action']) and $_POST['action'] == 'Edit')

{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

	try
	{
		$sql = 'SELECT id, name, email FROM author WHERE id = :id';
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

	//Get all the roles associated with this author

	try
	{
		$sql = 'SELECT roleid FROM authorrole WHERE authorid = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $id);
		$s->execute();
	}
	catch(PDOException $e)
	{
		$error = 'Error fetching roles assigned to this author.';
		include 'error.html.php';
		exit();
	}

	$selectedRoles = array();
	foreach($s as $row)
	{
		$selectedRoles[] = $row['roleid'];
	}

	include 'form.html.php';
	exit();
}


//edit and author entry
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