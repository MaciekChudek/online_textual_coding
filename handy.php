<?php

	// set your infomation.
	$host		=	'localhost';
	$user		=	'***';
	$pass		=	'***';
	$database	=	'***';

        //insert names of your tables here.	
	$coded_data = "slingerland_coded_data";
	$assignment = "slingerland_assignment";
	$coders = "slingerland_coders";
	$codes = "slingerland_codes";
	$passages = "slingerland_passages";
	$texts = "slingerland_texts";
	
	
	session_start();
	//to nicely display array contents

	 function displayArray($arrayname,$tab="&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp",$indent=0) {
	 $curtab ="";
	 $returnvalues = "";
	 while(list($key, $value) = each($arrayname)) {
	  for($i=0; $i<$indent; $i++) {
	   $curtab .= $tab;
	   }
	  if (is_array($value)) {
	   $returnvalues .= "$curtab$key : Array: <br />$curtab{<br />\n";
	   $returnvalues .= displayArray($value,$tab,$indent+1)."$curtab}<br />\n";
	   }
	  else $returnvalues .= "$curtab$key => $value<br />\n";
	  $curtab = NULL;
	  }
	 return $returnvalues;
	 }
?>
