<?php

//directory to upload files to
$uploadDir = "../uploads/";

//the database connection credentials
function getDbConnection()
{
	$connection = new mysqli('localhost', 'root', '', 'uploads');
	return $connection;
}

//names of sql objects
$tableName     = "files";
$fileIDField   = "u_id";
$fileNameField = "u_fileName";
$fileSizeField = "u_fileSize";
$dateField     = "u_date";
$locationField = "u_location";

//temporary path for zipped files
$zip_file = '../temp/files.zip';
