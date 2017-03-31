<?php

//include the db connection info
include("dbConnection.php");

//todays date
$date = date("Y-m-d");

//new database connection
$db = getDbConnection();

//check if there are connection errors to database
if ($db->connect_errno > 0) {
    die('Unable to connect to database [' . $db->connect_error . ']');
}

//track all the files that get uploaded
$uploadedFilesCount = 0;

//if there are files submitted
//if none found, skip the upload and just create the table
if ($_FILES) {
    // Loop through each file
    for ($i = 0; $i < count($_FILES); $i++) {

        //get current file
        $currentFile = $_FILES['file_' . $i];

        //Get the temp file path
        $tmpFilePath = $currentFile['tmp_name'];

        //Make sure we have a filepath
        if ($tmpFilePath != "") {
            //Setup our new file path
            $newFilePath = $uploadDir . $currentFile['name'];

            //Upload the file into the temp dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {

                //file path to be inserted into mySQL with escaped special characters
                $filePath = mysqli_real_escape_string($db, $newFilePath);

                //get the file name and escape special characters for mySQL
                $fileName = mysqli_real_escape_string($db, $currentFile['name']);

                //get the size of file in bytes
                $fileSize = mysqli_real_escape_string($db, $currentFile['size']);

                //the sql to be inserted into the table
                $sql = "INSERT INTO $tableName ($fileNameField, $fileSizeField, $dateField, $locationField) VALUES ('$fileName', '$fileSize', '$date', '$filePath');";

                //log error if sql is not inserted
                if ($db->query($sql) === TRUE) {
                    $uploadedFilesCount++;
                } else {
                    die($db->error);
                }

            }
        }
    }
}

//close the database connection
mysqli_close($db);

echo $uploadedFilesCount;
