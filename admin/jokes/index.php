<?php
include_once $_SERVER['DOCUMENT_ROOT'] .
    'jokes-database/includes/magicquotes.inc.php';

include_once $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/access.inc.php';

//check to see if user is logged in
if(!userIsLoggedIn())
{
  include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/login.html.php';
  exit();
}


//check to see if user has access rights
if(!userHasRole())
{
  $error = 'Only Content Editors have access to this page.';
  include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/accessdenied.html.php';
  exit();
} 



if(isset($_GET['add']))
{
	include $_SERVER['DOCUMENT_ROOT'] .
    'jokes-database/includes/db.inc.php';

	$pageTitle = 'New Joke';
	$action = 'addform';
	$text = '';
	$authorid = '';
	$id = '';
	$button = 'Add joke';

	try
	{
	 $result = $pdo->query('SELECT id, name FROM author');
	}
	catch(PDOException $e)
	{
		$error = 'Error fetching list of authors.';
		include 'error.html.php';
		exit();
	}

	foreach($result as $row)
	{
		$authors[] = array('id'=>$row['id'], 'name'=>$row['name']);
	}

	try
	{
		$result = $pdo->query('SELECT id, name FROM category');
	}
	catch(PDOException $e)
	{
		$error = 'Error fetching categories from database';
		include 'error.html.php';
		exit();
	}

	foreach($result as $row)
	{
		$categories[]= array('id'=>$row['id'], 'name'=>$row['name'], 'selected'=>FALSE);
	}

	include 'form.html.php';
	exit();
}


if(isset($_POST['action']) and $_POST['action'] == "Edit")
{
    include $_SERVER['DOCUMENT_ROOT'] .
    'jokes-database/includes/db.inc.php';    

    try
    {
      $query = 'SELECT id, joketext, authorid FROM joke WHERE id = :id';
      $s = $pdo->prepare($query);
      $s->bindValue(':id', $_POST['id']);
      $s->execute();
    }
    catch(PDOException $e)
    {
      $error = 'Error fetching jokes from database';
      include 'error.html.php';
      exit();
    }

    $row = $s->fetch();

    $pageTitle = 'Edit joke';
    $action = 'editform';
    $text = $row['joketext'];
    $authorid = $row['authorid'];
    $id = $row['id'];
    $button = 'Update joke';

    try
    {
      $result = $pdo->query('SELECT id, name FROM author');
    }
    catch(PDOException $e)
    {
      $error = 'Error fetching authors from database';
      include 'error.html.php';
      exit();
    }

    foreach($result as $row)
      { 
        $authors[] = array('id'=>$row['id'], 'name'=>$row['name']);
      }

    //get all categories chosen joke belongs to
    try
    {
      $sql = 'SELECT categoryid FROM jokecategory 
      WHERE jokeid = :id';
      $s = $pdo->prepare($sql);
      $s->bindValue(':id', $id);
      $s->execute();
    }
    catch(PDOException $e)
    {
      $error = 'Error fetching list of selected categories';
      include 'error.html.php';
      exit();  
    }

    foreach($s as $row)
    {
      $selectedCategories[] = $row['categoryid'];  
    }

    //get all categories in the database
    try
    {
      $result = $pdo->query('SELECT id, name FROM category');

    }
    catch(PDOException $e)
    {
      $error = 'Error fetching categories from database.';
      include 'error.html.php';
      exit();
    }

    foreach($result as $row)
    {
      $categories[] = array('id'=>$row['id'], 'name'=>$row['name'], 
                            'selected'=>in_array($row['id'], $selectedCategories));
    }
    

    include 'form.html.php';
    exit();

}


if(isset($_GET['addform']))
{
  include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

  try 
  {
    if($_POST['author'] == '')
    {
      $error = 'You must chose an author from the dropdown
      before submitting a joke. Click &lsquo;back&rsquo; and try again.';
      include 'error.html.php';
      exit();
    }

   $sql = 'INSERT INTO joke SET
   joketext = :joketext,
   jokedate = CURDATE(); 
   authorid = :authorid'; 
   $s = $pdo->prepare($sql);
   $s->bindValue(':joketext', $_POST['text']);
   $s->bindValue(':authorid', $_POST['author']);
   $s->execute();
  } 
  catch(PDOException $e)
  {
    $error = 'Error submitting the joke.';
    include 'error.html.php';
    exit();   
  } 

  $jokeid = $pdo->lastInsertId(); 

  if(isset($_POST['categories']))
  {
    try
    {
      $sql = 'INSERT INTO jokecategory SET
      jokeid = :jokeid,
      categoryid = :categoryid';
      $s = $pdo->prepare($sql);

      foreach($_POST['categories'] as $categoryid)
      {
      $s->bindValue(':jokeid', $jokeid);
      $s->bindvalue(':categoryid', $categoryid);
      $s->execute();
      }
    }
    catch(PDOException $e)
    {
      $error = 'Error insering joke into selected categories.';
      include 'error.html.php';
      exit();
    }
  }

  header('Location: .');
  exit();
}

if(isset($_GET['editform']))
{
  include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

  if($_POST['author'] == '')
  {
    $error = 'You must chose an author.';
    include 'error.html.php';
    exit();
  }

  try
  {
    $sql = 'UPDATE joke SET
    joketext = :joketext,
    authorid = :authorid WHERE
    id = :id';
    $s = $pdo->prepare($sql);
    $s->bindValue(':joketext', $_POST['text']);
    $s->bindValue(':authorid', $_POST['author']);
    $s->bindValue(':id', $_POST['id']);
    $s->execute();
  }
  catch(PDOExcetion $e)
  {
    $error = 'Error updating submitted joke';
    include 'error.html.php';
    exit();
  }

  //delete the old associations
  try
  {
    $sql = 'DELETE FROM jokecategory WHERE jokeid = :id';
    $s = $pdo->prepare($sql);
    $s->bindValue(':id', $_POST['id']);
    $s->execute();
  }
  catch(PDOException $e)
  {
  	$error = 'Error deleting jokecategories';
  	include 'error.html.php';
  	exit();
  }

  //update the joke category associations
  if(isset($_POST['categories']))
  {
	  try
	  {
	  	$sql = 'INSERT INTO jokecategory SET
	      categoryid = :categoryid,
	      jokeid = :jokeid';
	      $s = $pdo->prepare($sql);
	      

	    foreach($_POST['categories'] as $categoryid)
	    {
	      $s->bindValue(':categoryid', $categoryid);
	      $s->bindValue(':jokeid', $_POST['id']);
	      $s->execute();
	    }
	  }
	  catch(PDOException $error)
	  {
	    $error = 'Error updating jokecategory.';
	    include 'error.html.php';
	    exit();
	  }
  }
 
  header('Location: .');
  exit();

}








if(isset($_POST['action']) and $_POST['action'] == 'Delete')
{
  include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

  //delete chosen joke
  try
  {
  $sql = 'DELETE FROM joke WHERE id = :id';
  $s = $pdo->prepare($sql);
  $s->bindValue(':id', $_POST['id']);
  $s->execute();
  }
  catch(PDOException $e)
  {
    $error = 'Error deleting joke.';
    include 'error.php.html';
    exit();
  }

  //delete category associations with joke
  try
  {
    $sql = 'DELETE FROM jokecategory WHERE jokeid = :id';
    $s = $pdo->prepare($sql);
    $s->bindValue(':id', $_POST['is']);
    $s->execute();
  }
  catch(PDOException $e)
  {
    $error = 'Error deleting joke from joke categories.';
    include 'error.html.php';
    exit();
  }

  header('Location: .');
  exit();
}



//display search results
if (isset($_GET['action']) and $_GET['action'] == 'search')
{
	include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';
	
	//base statement
	$select = 'SELECT id, joketext';
	$from = ' FROM joke';
	$where = ' WHERE TRUE';

	$placeholders = array();

	if($_GET['author'] != '')
	{
		$where .= " AND authorid = :authorid";
		$placeholders[':authorid'] = $_GET['author'];
	}

	if($_GET['category'] != '')
	{	
		$from .= " INNER JOIN jokecategory ON id = jokeid";
		$where .= " AND categoryid = :categoryid";
		$placeholders[':categoryid'] = $_GET['category'];
	}

	if($_GET['text'] != '')
	{
		$where .= ' AND joketext LIKE :joketext';
		$placeholders[':joketext'] = '%' .$_GET['text'] . '%';
	}

	try
	{
		$sql = $select . $from . $where;
		$s = $pdo->prepare($sql);
		$s->execute($placeholders);
	}
	catch(PDOException $e)
	{
		$error = 'Error fetching jokes from database.';
		include 'error.html.php';
		exit();
	}

	foreach($s as $row)
	{
		$jokes[] = array('id'=>$row['id'], 'text'=>$row['joketext']);
	}

	include 'jokes.html.php';
	exit();
	
}


//display search form
include $_SERVER['DOCUMENT_ROOT'] . 'jokes-database/includes/db.inc.php';

try
{
	$result = $pdo->query('SELECT id, name FROM author');

}
catch(PDOException $e)
{
	$error = "Error fetching authors from database.";
	include 'error.html.php';
	exit();
}

foreach($result as $row)
{
	$authors[] = array('id'=>$row['id'], 'name'=>$row['name']);
}


try 
{
	$result = $pdo->query('SELECT id, name FROM category');		
}
catch (PDOException $e)
{
	$error = 'Error fetching categories from database.';
	include 'error.html.php';
	exit();		
}

foreach($result as $row)
{
	$categories[] = array('id'=>$row['id'], 'name'=>$row['name']);
}

include 'searchform.html.php';