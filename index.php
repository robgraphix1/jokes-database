<?php

include_once$_SERVER['DOCUMENT_ROOT']  .'jokes-database/includes/magicquotes.inc.php';




//detect addjoke line has been clicked, if so insert joke to database
if(isset($_GET['addjoke']))
{
	include 'form.html.php';
	exit();
}




//add the user input joke from form
if(isset($_POST['joketext']))
{

  include $_SERVER['DOCUMENT_ROOT']  .'jokes-database/includes/db.inc.php';

	try
	{
		$sql = 'INSERT INTO joke SET
		joketext = :joketext,
		jokedate = CURDATE()';
		$s = $pdo->prepare($sql);
		$s->bindValue(':joketext', $_POST['joketext']);
		$s->execute();
	}
	catch(PDOExcetption $e)
	{
		$error = "Error adding joke:" . $e->getMessage();
		include 'error.html.php';
		exit();
	}

	header('Location: .');
	exit();

}

//delete joke from database
if(isset($_GET['deletejoke']))
{

  include $_SERVER['DOCUMENT_ROOT']  .'jokes-database/includes/db.inc.php';

  try
  {
    $sql = 'DELETE FROM joke WHERE id = :id';
    $s = $pdo->prepare($sql);
    $s->bindValue(':id', $_POST['id']);
    $s->execute();

  }
  catch (PDOException $e)
  {
    $error = 'Error deleting joke: ' . $e->getMessage();
  }

  header('Location: .');
  exit();
}


// query to pull joketext from the database
include $_SERVER['DOCUMENT_ROOT']  .'jokes-database/includes/db.inc.php';


try
{
  $sql = 'SELECT joke.id, joketext, name, email
  FROM joke INNER JOIN author
  ON authorid = author.id';
  $result = $pdo->query($sql);
}
catch (PDOException $e)
{
  $error = 'Error fetching jokes: ' . $e->getMessage();
  include 'error.html.php';
  exit();
}

while ($row = $result->fetch())
{
  $jokes[] = array('id' =>$row['id'], 'text' => $row['joketext'], 'name' =>$row['name'], 'email' =>$row['email']);
}

include 'jokes.html.php';