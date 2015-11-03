<?php 

if (isset($_POST["password"]))
 	{
 		if ($_POST["password"] != "xinCoding")
 		{
 			die("Password incorrect. Attempt logged.");
 		}
 		else
 		{
 			setcookie("password", "xinaslocus", time()+3600);
 			$pass_ok = 1;
 			$test = "boo ha ha ha";
 		}
 	}




?>




<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Slingerland Coding System</title>
</head>
<body>

<?php
	

	//set up conection info and handy scripts
	include("handy.php");
	
 
		
#determine action


$action = 0;

if ((!isset($_COOKIE["password"]) || $_COOKIE["password"] != "xinaslocus") && !(isset($pass_ok) && $pass_ok == 1 )){$action = -10000;}

if ($action == -10000)
 {
 		
 	#echo "<h1> ----- $pass_ok --- $test ---- </h1>"
 	?>
 			<h1> Slingerland Data Coding System </h1>
			<form action="admin.php" method="post">
			Password required for access: 
			<input type="password" name="password">
			<br>
			<input type="submit" value="Login">
			</form>
			</body>
			</html>
		<?php 	
		exit;
 }



if (isset($_POST["action"])){	
	switch ($_POST["action"]) {
	    case "csv":
	        	$action = -1;
	        break;
	    case "results":
	        	$action = -2;
	        break;
	    case "something else":
	        	$action = -3;
	        break;
	}
}

if (isset($_GET["action"])){	
	switch ($_GET["action"]) {
	    case "upload":
	        	$action = 1;
	        break;
	    case "results":
	        	$action = 2;
	        break;	    
		case "results2":
	        	$action = 5;
	        break;
	    case "reset":
	        	if (isset($_POST["confirm"]))
	        	{if( $_POST["confirm"] == 1){$action = 4;}else{$action = 0;}}
	        	else {$action = 3;}
	        break;
	}
}
#upload spread sheets

	#passages
	#source documents
	#coders
	#assignment
	

#start coding

#get results






if ($action == 0) #no action - basic admin page
{
?>
	<h2> Admin Options	</h2>
	
	<ul>
		<li> <a href= "admin.php?action=upload"> Upload a spreadsheet table </a></li>
		<li> <a href= "admin.php?action=reset"> Reset coding </a></li>
		<li> <a href= "admin.php?action=results"> Download Results - Wide Format</a></li>
		<li> <a href= "admin.php?action=results2"> Download Results - Long Format</a></li>
	</ul>
		<hr />
	<ul>	
		<li> <a href= "code.php"> Code Passages </a></li>
	</ul>
	
<?php 	
}
elseif ($action != -10000)
{
	echo "<a href='admin.php'>Back to main admin page </a><hr />";
	
}


if ($action == 1) //upload
{
?>
	<h2> Upload a Table </h2>
	
	<p> Save your table as a .csv file. Open the file in a text editor and copy-paste the entire contents into the text box below. </p>
	
	<form action="admin.php" method="post">
		<input TYPE="hidden" NAME="action" VALUE="csv" >
		<textarea name="csv" rows="20" cols="50"> </textarea>
		<br />
		<select name="type">
		  <option value="<?php echo $passages?>">Passages</option>
		  <option value="<?php echo $texts?>">Texts</option>
		  <option value="<?php echo $codes?>">Codes</option>
		  <option value="<?php echo $coders?>">Coders</option>
		  <option value="<?php echo $assignment?>">Coder Assignment</option>
		</select>
		
		<input type="submit" value="Upload">	
	</form>
	
<?php 	
}

if ($action == 2) //get results
{
	$csv_string = 'Passage, Text, Coded_by, ';

	$connect = mysql_connect ($host, $user, $pass);
	if ( $connect )
	{
		mysql_select_db ( $database, $connect );
	}
	else 
	{
		trigger_error ( mysql_error(), E_USER_ERROR );
	}

	//get the codes
	$code_ids = array();
	$query =  "SELECT * FROM $codes";
	$results = mysql_query ( $query );	if (!$results)	{die("error writting to database: " . mysql_error() );}
	//iterate through appending codes to string
	while ($row = mysql_fetch_array($results, MYSQL_ASSOC))
	{
		array_push($code_ids, $row["Code ID"]);
		$csv_string .= $row["Code"].", ".$row["Code"]."-comments, ";
	}
	$csv_string = trim(substr($csv_string,0,-2));  //remove the extra comma
	
	//select the passages
	$query =  "SELECT `Passage ID`,`Source Text` FROM $passages";
	$results = mysql_query ( $query );	if (!$results)	{die("error writting to database: " . mysql_error() );}
	
	//iterate through them
	while ($row = mysql_fetch_array($results, MYSQL_ASSOC))
	{
		$passage_id = $row["Passage ID"];
		
		
		
		//fetch the coders who have coded that passages
		$query = "SELECT `Coder` FROM $assignment WHERE `Passage` = $passage_id AND `coded` = 1;";
		$results2 = mysql_query ( $query );	if (!$results2)	{die("error writting to database: " . mysql_error() );}
		
		//iterate through them
		
		while ($coder_row = mysql_fetch_array($results2, MYSQL_ASSOC))
		{
			$csv_string .= "\n".$passage_id.", ".$row["Source Text"].", ";
			
			$coder_id = $coder_row["Coder"];
			$csv_string .= $coder_id.", ";
			
			$query = "SELECT Code,Value,Comments FROM $coded_data WHERE `Coder` = $coder_id AND `Passage` = $passage_id;";
			$results3 = mysql_query ( $query );	if (!$results3)	{die("error writting to database: " . mysql_error() );}
			
			$coded_data_array = array();
			$comments_array = array();
			
			while($row=mysql_fetch_assoc($results3)) 
			{
				$coded_data_array[$row["Code"]] = $row["Value"];
				$comments_array[$row["Code"]] = $row["Comments"];
			} 
			
			echo get_object_vars($results3); 
			
			foreach ($code_ids as $cid)
			{
				if (isset($coded_data_array[$cid]))
				{
					$csv_string .= $coded_data_array[$cid].", ";
					$csv_string .= $comments_array[$cid].", ";
				}
				else
				{
					$csv_string .= "NA, ,";
				}
			}
			
			 $csv_string = trim(substr($csv_string,0,-2));  //remove the extra comma
		}
	}
	
	//write the data to a handy file
	
	
	$file_name="results.csv";               
	$fp = fopen ($file_name, "w"); 
	fwrite ($fp,$csv_string);          // entering data to the file
	fclose ($fp);                                // closing the file pointer
	//chmod($file_name,0777);           // changing the file permission.
?>
	<h2> Results </h2>
	
	<p> The results have been prepared in .csv format. You can either <a href=<?php echo $file_name; ?>> download them by clicking here </a> or copy and paste the details below into a blank text file then save it with the extension ".csv" and re-open it with a spread-sheet program.</p>
	
	<p> These results should be ready-to-analyse with any standard statistical package.</p>
	
	<textarea rows="20" cols="50" readonly="readonly"> <?php echo $csv_string; ?> </textarea>
<?php 	
} //end if action = 2

if ($action == 3) //confirm reset
{
?>
	<h2> Reset all data </h2>
	
	<p> Are you sure? Hittting yes will wipe all data coded so far. </p>
	
	<form action="admin.php?action=reset" method="post">
		<input TYPE="radio" NAME="confirm" VALUE="0" checked="checked" > No
		<input TYPE="radio" NAME="confirm" VALUE="1" > Yes
		<br />
		
		<input type="submit" value="Confirm Decision">	
	</form>
	
<?php 	
}

if ($action == 4) //reset
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
		
		$query = "UPDATE $assignment SET Coded=0";
		$results = mysql_query ( $query ) ;	if ( !$results )	{ die ( "Database Error: ".mysql_error () );}
		$query = "TRUNCATE $coded_data";
		$results2 = mysql_query ( $query ) ;	if ( !$results2 )	{ die ( "Database Error: ".mysql_error () );}
	
?>
	<h2> Reset Complete </h2>
	<hr />
	<p> Debugging info - Database reply: </p>
	
<?php 	
echo $results."<br />";
echo $results2."<br />";
}







if ($action == 5) //get results in long format
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

	$csv_string = "Passage, Coded_by, Code, Value, Comment\n";

	$query = "SELECT Passage,Coder,Code,Value,Comments FROM $coded_data";
	$results3 = mysql_query ( $query );	if (!$results3)	{die("error reading from database: " . mysql_error() );}
	
	while($row=mysql_fetch_assoc($results3)) 
	{
		$csv_string = $csv_string . $row['Passage'] .", ". $row['Coder'] .", ". $row['Code'] .", ". $row['Value'] .", ". $row['Comments']."\n";
	} 
	
	//write the data to a handy file
	
	
	$file_name="results.csv";               
	$fp = fopen ($file_name, "w"); 
	fwrite ($fp,$csv_string);          // entering data to the file
	fclose ($fp);                                // closing the file pointer
	//chmod($file_name,0777);           // changing the file permission.
?>
	<h2> Results </h2>
	
	<p> The results have been prepared in .csv format. You can either <a href=<?php echo $file_name; ?>> download them by clicking here </a> or copy and paste the details below into a blank text file then save it with the extension ".csv" and re-open it with a spread-sheet program.</p>
	
	<p> These results should be ready-to-analyse with any standard statistical package.</p>
	
	<textarea rows="20" cols="50" readonly="readonly"> <?php echo $csv_string; ?> </textarea>
<?php 	
} //end if action = 5







if ($action == -1) #user posted csv to be updated to database
{
	 
    $delim = ","; 
    $table_name = $_POST["type"];
	
	$csv = split("\n",$_POST["csv"]);
	
	$vals = explode($delim,$csv[0]);       
    $fields = ""; 
    foreach ($vals as $field) 
    { 
     $fields .= "`".trim(trim($field), '\"')."`,"; 
    } 
	$fields = trim(substr($fields,0,-1)); 
	
    $queries[] = array();
    $queries[0] = "TRUNCATE ".$table_name.";\n"; 
	
	for ($i=1;$i<count($csv);$i++)            
    { 
        $line = trim($csv[$i]);
    	if($line != "")
    	{
      $vals = explode($delim,$csv[$i]); 
      $values = ""; 
      foreach ($vals as $val) 
      { 
       $val=trim(trim($val), '\"'); 
       if (eregi("NULL",$val) == 0) 
          $values .= "'".addslashes($val)."',"; 
       else 
          $values .= "NULL,"; 
      } 
      $values = trim(substr($values,0,-1)); 
      $query = "INSERT INTO `".$table_name."` (".$fields.") VALUES (".trim($values).");";    
      
	  $queries[$i] = $query."\n";
    	}
    }
    
		$connect = mysql_connect ($host, $user, $pass);
		if ( $connect )
		{
			mysql_select_db ( $database, $connect );
		}
		else 
		{
			trigger_error ( mysql_error(), E_USER_ERROR );
		}
    	
		$results = array();
		$results[0] = "<br /><hr />MYSQL Responses:\n";
		set_time_limit(60);
    	for ($i=0;$i<count($queries);$i++)
    	{
			//echo "processing:  ".$queries[$i];
    		$result = mysql_query ( $queries[$i] );	
    		if (!$result)	{die("Error writting to database, please inform Maciek. Details: " . mysql_error()."<br />".nl2br(implode($queries)) );}
    		else { $results[$i+1] = $result."\n";}
    	}
    
    echo "Update sucessful. <br /><br />";
	echo "<h3>Technical details for debugging purposes only: </h3><br /> <br /> Database query executed: <br /><hr />";
    echo nl2br(implode($queries));
    
    echo nl2br(implode($results));
}






?>

</body>
</html>