<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Simple Coding System</title>
</head>
<body style="width:95%">
	<?php
	
	
	
	
	
	
	include("handy.php");
	
	$this_page = "code.php";	
	
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
	echo "<a href='code.php?logout=1'>Logout</a><hr />";	
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


if ($action == 4) //coder_id is set display coding page
{
//find which quotes need coding
	$query = "SELECT * FROM $assignment WHERE coder = $coder_id AND coded = 0;";
	$results = mysql_query ( $query );
	if ( !$results )
	{
		die ( "Database Error: ".mysql_error () );
	}
	if(mysql_num_rows($results) == 0) //coding complete
	{
			?>
			<body>
			<h1> Coding Complete</h1>
			<p> You have coded all the passages assigned to you. Please inform the primary investigator. </p>
			<?php
			exit;
	}
	

//count how many are left to do	
	$todo = mysql_num_rows($results);
		
		
	
	if (isset($_POST['recode'])) //check if we're recoding	 
	{
		$selected_quote = $_POST['recode'];
	}
	else //otherwise select one at random
	{
		mysql_data_seek($results, mt_rand(0, mysql_num_rows($results)-1 ));
		$selected_quote_row = mysql_fetch_assoc($results);
		$selected_quote = $selected_quote_row["Passage"];
	}
	$query = "SELECT Passage, Location FROM $passages WHERE `Passage ID` = $selected_quote;";
	$results = mysql_query ( $query ) ;	if ( !$results )	{ die ( "Database Error: ".mysql_error () );}
	$selected_quote_row = mysql_fetch_assoc($results);
	$quote_text = $selected_quote_row["Passage"];
	$quote_location = $selected_quote_row["Location"];

//display the coding table
?>
<body>

<div  style="background-color:#B00000;"> You have <?php echo $todo;?> passages left to code. </div>

<h2>Location:</h2>

<p> <?php echo nl2br($quote_location); ?> </p>


<h2>Passage:</h2>

<p> <?php echo nl2br($quote_text); ?> </p>

<hr />

<form id="coding_form" action="<?php echo $this_page;?>" method="post">

<input TYPE="hidden" NAME="coder_id" VALUE="<?php echo $coder_id;?>" >
<input TYPE="hidden" NAME="quote_id" VALUE="<?php echo $selected_quote;?>" >

<table  bgcolor="#dddddd" border="1" width="100%">

<tr><th width=5%>ID</th><th>Code</th><th width=10%>Applies?</th><th width=20%>Comment</th></tr>

<?php 
//display coding options
	$query = "SELECT * FROM $codes;";
	$results = mysql_query ( $query );
	if ( !$results )
	{
		die ( "Database Error: ".mysql_error () );
	}
	else
	{
		$code_ids = array();
		while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) 
		{
			array_push($code_ids, $row["Code ID"]);
			if ($row["Code ID"] == $row["Supercode"])
			{
				?>
					</table>
					<br />
					<table  bgcolor="#dddddd" border="1" width="100%">
					<tr><th width=5%>ID</th><th>Code</th><th width=10%>Applies?</th><th width=20%>Comment</th></tr>
					<tr style="text-align:justify; background:#eeeeee;">
				<?php
			}
			else
			{
				?><tr style="text-align:justify;"><?php
			}
			?>
			
			
				<td><?php echo $row["Code ID"] ?> </td>
				<td><div id='code_text<?php echo $row["Code ID"] ?>'> <?php echo $row["Code"] ?></div></td>
				<td align="justify">
					<table width=100%><tr>
					<td><Input type = 'Radio' Checked=Checked Name ='code[<?php echo $row["Code ID"] ?>]' value= '0'>No</td>
					<td style='text-align:right;'><Input type = 'Radio' Name ='code[<?php echo $row["Code ID"] ?>]' value= '1'>Yes</td>
					</tr></table>
				</td>
				<td> <Input type="text"  style="width:90%" name='comment[<?php echo $row["Code ID"] ?>]' /> </td>
			</tr>
			
			<?php 
		}
	}
//submit button
?>
</table>
<br /><br />
<input type="button" value="Coded" onClick="checkRadios()">
</form>
<p id="invalid" style="display: none; color: red;"> You have not yet finished coding this passage. </p>
<script type="text/javascript">

var coding_form =  document.getElementById("coding_form");
function highlight(id, red)
{
	var quote = document.getElementById(id);
	if (red){quote.style.color = 'red';}else{quote.style.color = 'black';}
}

function checkRadio(frm, btnName)
{

		var btn = frm[btnName];
		var valid;
		
		for (var x = 0;x < btn.length; x++)
		{
			valid = btn[x].checked;
			if (valid) {break;}
		}
		if(!valid)
		{
			return false;
		}
		else
		{
			return true;
		}
}

function displayValidationMessage(){ document.getElementById('invalid').style.display='block'; }

function checkRadios()
{
	var error_found = false; 
<?php 

 foreach($code_ids as $pid) 
 {
?>
if (checkRadio(coding_form, "code[<?php echo $pid?>]") == false){highlight("code_text<?php echo $pid?>", true);	error_found = true;}else{highlight("code_text<?php echo $pid?>", false);}
<?php
 }
?>
	if (error_found) {displayValidationMessage(); return false;}
	else {coding_form.submit(); return true;}
}

</script>
	
<?php
 } //end action 5 - coder id is set, display coding page
 
 
 	if($action == -1)//submit codes to database
	{
		
		$connect = mysql_connect ($host, $user, $pass);
		if ( $connect )
		{
			mysql_select_db ( $database, $connect );
		}
		else 
		{
			trigger_error ( mysql_error(), E_USER_ERROR );
		}
		
		
		$coder_id = $_POST["coder_id"];
		$passage_id = $_POST["quote_id"];
	
		$codes = $_POST["code"];
		$comments = $_POST["comment"];
		
		$code_ids =  array_keys($codes);
		
//check that this hasn't already been coded
		$query = 	$query = "SELECT * FROM $assignment WHERE coder = $coder_id AND passage = $passage_id;";
		$results = mysql_query ( $query );	if (!$results)	{die("error writting to database: " . mysql_error() );}
		$temp = mysql_fetch_assoc($results);
		
		
		
// if already coded, give option to recode		
		if ($temp["Coded"] != 0 )
		{
			if (!isset($_POST["confirmed"]) || $_POST["confirmed"] != "1")
			{
				echo "<h1>Intentional Recoding?</h1><p>Attempting recode previously coded data. Did you hit submit twice by accident?</p> 
				
				<p>If you intended to over-write your old old data: click 'Confirm', otherwise hit 'Start Again'.</p>";
				
				?>
				<form id="coding_form" action="<?php echo $this_page;?>" method="post">
				
				<?php
			
				foreach (array_keys($_POST) as $key) { 
				
					$var_val = $_POST[$key];
					if (is_array($var_val))
					{
						foreach (array_keys($var_val) as $key2) {
							echo "<input TYPE='hidden' NAME='".$key."[".$key2."]' VALUE='".$var_val[$key2]."' >";
						}
					}
					else
					{
						echo "<input TYPE='hidden' NAME='$key' VALUE='$var_val' >";
					}
				}
				
				?>
				<input TYPE="hidden" NAME="confirmed" VALUE="1" >
				<input type="submit" value="Confirm">
				</form>
				<a href='$this_page'> Start Again </a><br /><hr /><br />
				<?php
				exit;
			} //end if unconfirmed
			else //confirmed over-write, delete old data
			{
				$query = "DELETE FROM $coded_data WHERE Passage=$passage_id AND Coder=$coder_id";
				$results = mysql_query ( $query );	if (!$results)	{die("error deleting old results: " . mysql_error() );} else {echo "Previous records sucessfully deleted<br /><br />";}
			}
		} //end if already coded
		
		
		
		$query = "UPDATE $assignment SET coded = 1 WHERE coder = $coder_id AND passage = $passage_id;";
		
		#echo $query;
		$results = mysql_query ( $query );	if (!$results)	{die("error writting to database: " . mysql_error() );}
		
		foreach($code_ids as $cid)
		{
			$comment = str_replace(",",";",$comments[$cid]);
			
			$query = "INSERT INTO $coded_data (`Passage`, `Coder`, `Code`, `Value`, `Comments`) VALUES ('$passage_id', '$coder_id', '$cid', '$codes[$cid]', '$comment');";
			$results = mysql_query ( $query );
			if (!$results)
			{
				die("error writting to database: " . mysql_error() );
			}
			else
			{
				echo "
				<!-- $query -->";
			}
		}
		
		?>

		
		
		Successfully coded passage #<?php echo $passage_id;?> - keep it up! Click below to code your next quote.
		<form action="<?php echo $this_page;?>" method="post">
		<input TYPE="hidden" NAME="coder_id2" VALUE="<?php echo $coder_id;?>" >
		<input type="submit" value="Continue coding">
		</form>
		
		<?php 
		exit;
	} //end if submitting
 
 
?>
  
</body>
</html>