<?php

//include the db connection info
include("dbConnection.php");

//new database connection
$db = getDbConnection();

//check if there are connection errors to database
if ($db->connect_errno > 0) {
    die('Unable to connect to database [' . $db->connect_error . ']');
}

//track all the files that get updated
$updatedFilesCount = 0;

//array to store the data ids from all the rows--also the file id in the database
$rowIDs = $_POST['rowIDs'];
//names of files also sent over
$fileNames = $_POST['fileNames'];

//for each of the rowIDs
for ($i = 0; $i < count($rowIDs); $i++) {

    //validate the ID of this row and create variable
    $thisID = mysqli_real_escape_string($db, $rowIDs[$i]);
    //the current file name
    $thisFileName = mysqli_real_escape_string($db, $fileNames[$i]);

    //create the sql query to select the file location
    if ($sql = mysqli_query($db, "SELECT $locationField FROM $tableName WHERE $fileIDField = '$thisID';")) {
        //fetch the row that matches the sql query if possible
        if ($fileLocation = mysqli_fetch_row($sql)) {
            //set the new location
            $newLocation = mysqli_real_escape_string($db, substr($fileLocation[0], 0, strrpos($fileLocation[0], "/") + 1) . $thisFileName);
            //rename the file in the selected location if possible
            if (rename($fileLocation[0], $newLocation)) {
                //if file is successfully renamed, update the database to reflect that
                if ($sql = "UPDATE $tableName SET $fileNameField='$thisFileName',$locationField='$newLocation' WHERE $fileIDField = '$thisID';") {
                    //delete the file from the database
                    $db->query($sql);
                    $updatedFilesCount++;
                }
            }
        }
    }

}

//close the database connection
mysqli_close($db);

echo $updatedFilesCount;
