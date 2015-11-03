<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Simple Coding System</title>
</head>
<body style="width:95%">
	<?php
	
	
	
	
	
	
	include("handy.php");
	
	$this_page = "recode.php";	
	
$action = 0;

if (isset($_POST["coder_id"])) //gets set for coding
{	
	$action = -1;
}
elseif (isset($_POST['coder_id2'])) //gets set after coding
{
	$action = 3;
} 
elseif (isset($_SESSION["coder"])) //not sure why i'm using this - left over from different version?
{
	$action = 2;
}
elseif (isset($_POST["coder"])) //after login page
{
	$action = 1;
}

if (isset($_GET["logout"])) //gets set for coding
{
	$action = -1000;
}

	
if ($action == -1000)
{
	unset($_SESSION["coder"]);
	unset($_POST["coder"]);	
	unset($_POST["coder_id"]);
	unset($_POST["coder_id2"]);
	$action = 0;
}	


if ($action == 0) //display login form
{
		if(isset($_SESSION["error"]))
		{
			echo "<h1>Unidentified Coder</h1><p>Try capitalising your name</p>";
		}
		//
		?>
		<form action="<?php echo $this_page;?>" method="post">
		Coder Name: 
		<input type="text" name="coder">
		<br>
		<input type="submit" value="Login">
		</form>
	
		<?php
		exit;
}
else
{
	echo "<a href='".$this_page."?logout=1'>Logout</a><hr />";	
}


// connect to the mysql database server.
$connect = mysql_connect ($host, $user, $pass);
if ( $connect )
{
	mysql_select_db ( $database, $connect );
}
else 
{
	trigger_error ( mysql_error(), E_USER_ERROR );
}



if ($action == 1) //coder was posted
{
	$_SESSION['coder'] = $_POST["coder"];
	$action = 2;
}
	
if ($action == 2) //session[coder] is set
{
	//check that we have a valid coder and save their id number
	$query = "SELECT * FROM $coders";
	$results = mysql_query ( $query );
	if ( $results )
	{
		$coder_id = -1;
		while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) 
		{
			if ($row["Coder Name"] == $_SESSION['coder'])
			{
				$coder_id = $row["Coder ID"];
			}
		}
		if ($coder_id == -1)
		{
			//they aren't a valid coder - send them back
			$_SESSION['error'] = 1;
			unset($_SESSION['coder']);
			echo "Invalid User Name. Remember to capitalise the first letter.</body></html>";
			exit;
		}
		$action = 4;
	}
	else {
		die ( "Database Error: ".mysql_error () );
	}
}

if ($action == 3) //coder_id2 was posted
{
	$coder_id = $_POST['coder_id2'];
	$action = 4;
}











if ($action == 4) //coder_id is set display recoding page
{
	$query = "SELECT * FROM $assignment WHERE Coder = $coder_id ORDER BY  `slingerland_assignment`.`Passage` ASC";
	$results = mysql_query ( $query );
	if ( !$results )
	{
		die ( "Database Error: ".mysql_error () );
	}

	?>
	<form action="code.php" method="post">
	<h1> Coding Assignment Table</h1>
	<table  bgcolor="#dddddd" border="1" width="100%">
	<tr><th> Passage ID </th><th>Coded?</th><th>Recode?</th></tr>
	<input TYPE="hidden" NAME="coder_id2" VALUE="<?php echo $coder_id;?>" >
	
	<?php
	
	while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) 
	{
		//echo "<tr><td>".$row['Passage']."</td><td>".$row['Coded']."</td><td>		<Input type = 'Radio' Name ='recode[".$row["Passage"]."]' value= '1'>"
		
				echo "<tr><td style='text-align:center;'>".$row['Passage']."</td>";
				
				if ($row['Coded'] == 1)
				{
					echo "<td style='text-align:center; color: green;'>".Yes."</td>";
				}
				else
				{
					echo "<td style='text-align:center; color: red;'>".No."</td>";
				}
				
				echo "<td style='text-align:center;'><Input type = 'Radio' Name ='recode' value= '".$row['Passage']."'></td></tr>";
	}
	?>
	
	</table>
	<input type="submit" value="Recode">
	</form>
	<?php
}

?>

</body>
</html>

