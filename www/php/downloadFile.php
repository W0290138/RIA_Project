<?php

//include the db connection info
include("dbConnection.php");

//new database connection
$db = getDbConnection();

//check if there are connection errors to database
if ($db->connect_errno > 0) {
	die('Unable to connect to database [' . $db->connect_error . ']');
}

//array to store the data ids from all the rows--also the file id in the database
$rowIDs = json_decode($_GET['rowIDs']);

$filesToZip = array();

for ($i = 0; $i < count($rowIDs); $i++) {
	
	//validate the ID of this row and create variable
	$thisID = mysqli_real_escape_string($db, $rowIDs[$i]);
	
	//create the sql query to select the file location
	if ($sql = mysqli_query($db, "SELECT $locationField FROM $tableName WHERE $fileIDField = '$thisID';")) {
		//fetch the row that matches the sql query if possible
		if ($fileLocation = mysqli_fetch_row($sql)) {
			//if the file exists, download it
			if (file_exists($fileLocation[0])) {
				array_push($filesToZip, $fileLocation[0]);
			}
		}
	}
}

//close the database connection
mysqli_close($db);

//check if there is a temporary zip already and delete
if (file_exists($zip_file)) {
	unlink($zip_file);
}

//if there are more than one file to zip
if (count($filesToZip) > 1) {
	//create the archive object
	$zip = new ZipArchive();
	//create the zip file
	if ($zip->open($zip_file, ZipArchive::CREATE) !== TRUE) {
		die("zip file not created");
	}
	//for the number of files that need to be zipped
	for ($i = 0; $i < count($filesToZip); $i++) {
		//get the contents of the file
		$content = file_get_contents($filesToZip[$i]);
		//add the file to the zip file
		if (!$zip->addFromString(pathinfo($filesToZip[$i], PATHINFO_BASENAME), $content)) {
			die("file at index " + $i + " not added to zip");
		}
	}
	//close the zip folder
	$zip->close();
	//make the location to download the file from equal to the zip file
	$downloadLocation = $zip_file;
	
} else {
	//if only one file, do not zip, just download the file directly
	$downloadLocation = $filesToZip[0];
}

//proceed to download the file
// http headers
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . basename($downloadLocation) . "\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($downloadLocation));
ob_end_flush();
//read the file and initiate the download
@readfile($downloadLocation);
