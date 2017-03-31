<?php

//include the db connection info
include("dbConnection.php");

//new database connection
$db = getDbConnection();

//check if there are connection errors to database
if ($db->connect_errno > 0) {
	die('Unable to connect to database [' . $db->connect_error . ']');
}

//track all the files that get delete
$deletedFilesCount = 0;

//array to store the data ids from all the rows--also the file id in the database
$rowIDs = $_POST['rowIDs'];

//for each of the rowIDs
for ($i = 0; $i < count($rowIDs); $i++) {
	
	//validate the ID of this row and create variable
	$thisID = mysqli_real_escape_string($db, $rowIDs[$i]);
	
	//create the sql query to select the file location
	if ($sql = mysqli_query($db, "SELECT $locationField FROM $tableName WHERE $fileIDField = '$thisID';")) {
		//fetch the row that matches the sql query if possible
		if ($fileLocation = mysqli_fetch_row($sql)) {
			//unlink the file in the selected location if possible
			if (unlink($fileLocation[0])) {
				//if file is successfully deleted, create a statement to remove it from the database
				if ($sql = "DELETE FROM $tableName WHERE $fileIDField = '$thisID';") {
					//delete the file from the database
					$db->query($sql);
                    $deletedFilesCount++;
				}
			}
		}
	}
	
}

//close the database connection
mysqli_close($db);

echo $deletedFilesCount;
