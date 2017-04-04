<?php

include("dbConnection.php");

//new database connection
$db = getDbConnection();

//get the file type icon based on its extension
function getIcon($fileType)
{
	
	//icon maps as variables
	//format: array(iconClassName, array(extension, extension2, extenstion3, etc.))
	$audio        = array(
		"fa fa-file-audio-o",
		array(
			"AIFF",
			"AIF",
			"AU",
			"WAV",
			"RA",
			"MID",
			"MIDI",
			"IFF",
			"M3U",
			"M4A",
			"MP3",
			"MPA",
			"WMA"
		)
	);
	$video        = array(
		"fa fa-file-video-o",
		array(
			"3G2",
			"3GP",
			"ASF",
			"AVI",
			"FLV",
			"M4V",
			"MPG",
			"RM",
			"SRT",
			"SWF",
			"VOB",
			"WMV"
		)
	);
	$threed       = array(
		"fa fa-file-o",
		array(
			"3DM",
			"3DS",
			"MAX",
			"OBJ"
		)
	);
	$image        = array(
		"fa fa-file-picture-o",
		array(
			"BMP",
			"DDS",
			"GIF",
			"JPG",
			"PNG",
			"PSD",
			"PSPIMAGE",
			"TGA",
			"THM",
			"TIF",
			"TIFF",
			"YUV"
		)
	);
	$vector       = array(
		"fa fa-file-picture-o",
		array(
			"AI",
			"EPS",
			"PS",
			"SVG"
		)
	);
	$pagelayout   = array(
		"fa fa-file-pdf-o",
		array(
			"INDD",
			"PCT",
			"PDF"
		)
	);
	$spreadsheet  = array(
		"fa fa-file-excel-o",
		array(
			"XLR",
			"XLS",
			"XLSX"
		)
	);
	$database     = array(
		"fa fa-file-o",
		array(
			"ACCDB",
			"DB",
			"DBF",
			"MDB",
			"PDB",
			"SQL"
		)
	);
	$executable   = array(
		"fa fa-file-o",
		array(
			"APK",
			"APP",
			"BAT",
			"CGI",
			"COM",
			"EXE",
			"GADGET",
			"JAR",
			"WSF"
		)
	);
	$webfile      = array(
		"fa fa-file-code-o",
		array(
			"ASP",
			"ASPX",
			"CER",
			"CFM",
			"CSR",
			"CSS",
			"HTM",
			"HTML",
			"JS",
			"JSP",
			"PHP",
			"RSS",
			"XHTML"
		)
	);
	$font         = array(
		"fa fa-file-o",
		array(
			"FNT",
			"FON",
			"OTF",
			"TTF"
		)
	);
	$compressed   = array(
		"fa fa-file-archive-o",
		array(
			"7Z",
			"CBR",
			"DEB",
			"GZ",
			"PKG",
			"RAR",
			"RPM",
			"SITX",
			"TAR.GZ",
			"ZIP",
			"ZIPX"
		)
	);
	$devlanguage  = array(
		"fa fa-file-code-o",
		array(
			"C",
			"CLASS",
			"CPP",
			"CS",
			"DTD",
			"FLA",
			"H",
			"JAVA",
			"LUA",
			"M",
			"PL",
			"PY",
			"SH",
			"SLN",
			"SWIFT",
			"VB",
			"VCXPROJ",
			"XCODECPROJ"
		)
	);
	$text         = array(
		"fa fa-file-text-o",
		array(
			"RTF",
			"TXT",
			"ODT"
		)
	);
	$word         = array(
		"fa fa-file-word-o",
		array(
			"DOC",
			"DOCX"
		)
	);
	//array of all file type icon maps
	$fileTypeList = array(
		$audio,
		$video,
		$threed,
		$image,
		$vector,
		$pagelayout,
		$spreadsheet,
		$database,
		$executable,
		$webfile,
		$font,
		$compressed,
		$devlanguage,
		$text,
		$word
	);
	//default file icon class if no match found
	$fileTypeNew  = "fa fa-file-o";
	
	//loop through each item in fileTypeList
	for ($x = 0; $x < count($fileTypeList); $x++) {
		//loop through each item in fileTypeList index "x"
		for ($y = 0; $y < count($fileTypeList[$x][1]); $y++) {
			//if file extension at index "y" of fileTypeList index "x" is a match
			if (strtoupper($fileType) === $fileTypeList[$x][1][$y]) {
				//change file type new to the corresponding class
				$fileTypeNew = $fileTypeList[$x][0];
			}
		}
	}
	
	//return new file type class
	return $fileTypeNew;
	
}

//get the proper output for the file size
function getAdjustedSize($fileSize)
{
	//array of file sizes and types
	$fileUnitArray = array(
		array(
			1000,
			"KB"
		),
		array(
			1000000,
			"MB"
		),
		array(
			1000000000,
			"GB"
		)
	);
	//for each item in fileUnityArray except for last
	for ($i = 0; $i < count($fileUnitArray) - 1; $i++) {
		//if the file size is less than the size of the unit in the next index of the fileUnitArray
		if ($fileSize < $fileUnitArray[$i + 1][0]) {
			//return the file size divided by unit size and append the unit type
			return round(($fileSize / $fileUnitArray[$i][0]), 2) . " " . $fileUnitArray[$i][1];
		}
	}
	
}

//select all of the records from the files table in database
$sql = <<<SQL
SELECT $fileIDField, $fileNameField, $fileSizeField, $dateField, $locationField
FROM $tableName
SQL;

//if there was an error running the query
if (!$result = $db->query($sql)) {
	die('There was an error running the query [' . $db->error . ']');
}

//create an array to hold the returned files
$returnedFiles = array();

//while there are rows to fetch from the query
while ($row = $result->fetch_assoc()) {
    $currentFile = new stdClass();
    $currentFile->id = $row[$fileIDField];
    $currentFile->name = $row[$fileNameField];
    $currentFile->icon = getIcon(substr($row[$fileNameField], strpos($row[$fileNameField], ".") + 1));
    $currentFile->size = getAdjustedSize($row[$fileSizeField]);
    $currentFile->date = $row[$dateField];
    array_push($returnedFiles, $currentFile);
}

echo json_encode($returnedFiles);

//close the database connection
mysqli_close($db);
