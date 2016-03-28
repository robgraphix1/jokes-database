<?php

function userIsLoggedIn()
{
	if(isset($_POST['action']) and $_POST['action'] == 'login')
	{
		if(!isset($_POST['email']) or $_POST['email'] == '' or
			!isset($_POST['passord']) or $_POST['password'] == '')
		{
			$GLOBALS['loginError'] = 'Please fill in both fields';
			return FALSE;
		}	
	}

	$password = md5($_POST['password'] . 'ijdb');

	if(databaseContainsAuthor($_POST['email'], $password))
	{
		session_start();
		$_SESSION['loggedIn'] = TRUE;	
		$_SESSION['email'] = $_POST['email'];
		$_SESSION['password'] = $password;
		return TRUE;
	}
	else
	{
		session_start();
		unset($_SESSION['loggedIn']);
		unset($_SESSION['email']);
		unset($_SESSION['passord']);
		$_GLOBALS['loginError'] = 'The specified email address or password
		was incorrect.';
		return FALSE;	
	}

	if(isset($_POST['action']) and $_POST['action'] == 'logout')
	{
		session_start();
		unset($_SESSION['loggedIn']);
		unset($_SESSION['email']);
		unset($_SESSION['password']);
		header('Location: . $_POST['goto']');
		exit();
	}

	session_start();
	if(isset($_SESSION['loggedIn']))
	{
		return databaseContainsAuthor($_SESSION['email'], $_SESSION['password']);
	}

}



function databaseContainsAuthor($email, $password)
{
	include 'db.inc.php';

	try
	{
		$sql = 'SELECT COUNT(*) FROM author
		WHERE email = :email AND password = :password';
		$s = $pdo->prepare($sql);
		$s->bindValue(':email'=>$email);
		$s->bindValue(':password'=>$password);
		$s-execute();
	}
	catch(PDOException $e)
	{
		$error = 'Error searching for author';
		include 'error.html.php';
		exit();
	}

	$row = $s->fetch();

	if($row[0] > 0)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}


