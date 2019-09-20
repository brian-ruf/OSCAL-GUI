<?php
session_start();
require_once("oscal-begin.php");

// assigned a value as a global in PrepareProjectLocation
$project_folder = "";

// may be modified as a global in PrepareProjectLocation
$messages = "";

if (PrepareProjectLocation()) {
	$fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
	$messages .= "FOLDER: " . $project_folder . "\n";

	if ($fn) {
	$messages .= "Ajax upload\n";
	$messages .= "File: " . $fn . "\n";

		// AJAX call
		file_put_contents( $project_folder . "__original/" . $fn, file_get_contents('php://input') );
		$messages .= "$fn uploaded";
//		exit();
	}
	else {
	$messages .= "Form upload\n";

		// form submit
		$files = $_FILES['fileselect'];

		foreach ($files['error'] as $id => $err) {
			$messages .= "FILE: " . var_dump($id) . "\n";
			if ($err == UPLOAD_ERR_OK) {
				$fn = $files['name'][$id];
				move_uploaded_file( $files['tmp_name'][$id], $project_folder . "__original/" . $fn );
				$messages .= "File $fn uploaded.\n";
			}
		}
	}
	if (copy($project_folder . "__original/" . $fn, $project_folder . $fn) == true) {
		$_SESSION["project_file"] = $fn;
	} else {
		$messages .= "Unable to copy file up one level to " . $project_folder ;
	}
} 

if (SHOW_DEBUG) {
	$myfile = fopen(PROJECT_LOCATION . "log.txt", "w") or die("Unable to open file!");
	fwrite($myfile, "TO DISABLE THE CREATION OF THIS FILE, set SHOW_DEBUG to false in oscal-config.php\n\n");
	fwrite($myfile, $messages);
	fclose($myfile);
	$_SESSION["DEBUG"] = $messages;
}


// ====================================================================
function PrepareProjectLocation(){
	global $project_folder, $messages;
	$status = false;
	
	// If project folder is already defined, just use it
	if (isset($_SESSION["project_folder"])) {
		if (file_exists($_SESSION["project_folder"])) {
			$project_folder = $_SESSION["project_folder"];
			$status = true;
		}
	}

	// If project folder is not yet defined, create it, and its sub-folders
	if (! $status) {
		// create unique project directory and supporting sub-directories
		$project_folder = PROJECT_LOCATION . "proj-" . date("Y-m-d--H-i-s-u") . "/";
		
		if (CreateDirectory($project_folder)) {
			if (CreateDirectory($project_folder . "__original")) {
				if (CreateDirectory($project_folder . "__import")) {
					if (CreateDirectory($project_folder . "__resources")) {
						if (CreateDirectory($project_folder . "__output")) {
							if (CreateDirectory($project_folder . "__other_formats")) {
								$status = true;
								$_SESSION["project_folder"] = $project_folder;
							}
						}
					}
				}
			}
		}
	}
	
	if (! $status) {
		$messages .= "Something went wrong creating directories.\n";
	}
	
	return $status;
}


// ====================================================================
function CreateDirectory($new_dir){
	global $messages;
	$ret_val = false;
	
	if (mkdir($new_dir, 0755, true)) {
		$ret_val = true;
	} else {
		$messages .= "Unable to create: " . $new_dir . "\n";
	}

	return $ret_val;
	
}


