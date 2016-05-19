<?php
error_reporting(E_ALL ^ E_DEPRECATED);
include "dbsettings.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postaction = $_POST["action"];
	$postaction();
}
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $getaction = $_GET["action"];
 	$getaction();
}
function addlab (){
	function copy_directory($src,$dst) {
	    $dir = opendir($src);
	    @mkdir($dst);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            if ( is_dir($src . '/' . $file) ) {
	                recurse_copy($src . '/' . $file,$dst . '/' . $file);
	            }
	            else {
	                copy($src . '/' . $file,$dst . '/' . $file);
	            }
	        }
	    }
	    closedir($dir);
	    $files = glob('./upload/temp/*'); // get all file names
		foreach($files as $file){ // iterate files
			if(is_file($file))
		    	unlink($file); // delete file
		}
	}

	function deleteDirectory($dir) {
	    if (!file_exists($dir)) {
	        return true;
	    }

	    if (!is_dir($dir)) {
	        return unlink($dir);
	    }

	    foreach (scandir($dir) as $item) {
	        if ($item == '.' || $item == '..') {
	            continue;
	        }

	        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
	            return false;
	        }

	    }

	    return rmdir($dir);
	}

	$conn = $GLOBALS['conn'];
	$lab_name = $_REQUEST['lab_name'];
	$filename = $_REQUEST['filename'];
	$submittedby = $_REQUEST['submittedby'];
	$destname = $_REQUEST['destname'];
	$subdate = $_REQUEST['subdate'];
	if(file_exists($destname))
		deleteDirectory($destname);
	mkdir($destname);
	copy_directory('./upload/temp/',$destname);
	

	$rootPath = realpath($destname);

	// Initialize archive object
	$zip = new ZipArchive();
	$zip->open($destname.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

	// Initialize empty "delete list"
	$filesToDelete = array();

	// Create recursive directory iterator
	/** @var SplFileInfo[] $files */
	$files = new RecursiveIteratorIterator(
	    new RecursiveDirectoryIterator($rootPath),
	    RecursiveIteratorIterator::LEAVES_ONLY
	);

	foreach ($files as $name => $file)
	{
	    // Skip directories (they would be added automatically)
	    if (!$file->isDir())
	    {
	        // Get real and relative path for current file
	        $filePath = $file->getRealPath();
	        $relativePath = substr($filePath, strlen($rootPath) + 1);

	        // Add current file to archive
	        $zip->addFile($filePath, $relativePath);

	        // Add current file to "delete list"
	        // delete it later cause ZipArchive create archive only after calling close function and ZipArchive lock files until archive created)
	        if ($file->getFilename() != 'important.txt')
	        {
	            $filesToDelete[] = $filePath;
	        }
	    }
	}
	$labref = $_REQUEST['lab_id'];
	$destname = $destname.'.zip';
	$sql = "insert into labmarks (submittedby, filepath, lab_name, filename, labref, sub_date) values ('$submittedby', '$destname', '$lab_name', '$filename', '$labref', '$subdate')";
	if (!$conn->query($sql))
	    echo "Error creating table: " . $conn->error;
	else 
		echo 1;
}

function getLabDetails () {
	$conn = $GLOBALS['conn'];
	$serialno = 0;
	$lab_id = $_REQUEST['lab_id'];
	$sql = "select * from lab where lab_id='$lab_id'";
	$result = $conn->query($sql);
	$datastr = '';
	if(mysqli_num_rows($result) == 0){
		echo 0;
	} else {
		while($row = $result->fetch_assoc()) {
			$datastr = $row['subject'].';'. $row['lablink'] .';'.$row['sub_date'] .';'.$row['filename'];
		}
		echo $datastr;
	}
}
function getSubNames () {
	$conn = $GLOBALS['conn'];
	$sql = "select DISTINCT subject from lab";
	$result = $conn->query($sql);
	$datastr = '<option></option>';
	if(mysqli_num_rows($result) == 0){
		echo 0;
	} else {
		while($row = $result->fetch_assoc()) {
			$datastr = $datastr. '<option value="'. $row['subject'].'">'. $row['subject'].'</option>';
		}
		echo $datastr;
	}
}
function loadlabs () {
	$conn = $GLOBALS['conn'];
	$serialno = 1;
	$paginate ='';
	$i = 0;
	$not = array();
	$datastr = '';
	$perpage = 10;
	$page = $_REQUEST['page'];
	$subject = $_REQUEST['subject'];
	$newsql =  "SELECT  count(lab_id) from lab  where subject = '$subject' ";
	$result = $conn->query($newsql);
	if($result){
		$count = (int)$result->fetch_assoc()['count(lab_id)'];
		$pages = ceil($count/$perpage);
		$start = (($page - 1)*$perpage);
		$serial = $start+1;
		$result = $conn->query("SELECT * from lab where subject = '$subject' limit $start, $perpage");
		while($row = $result->fetch_assoc()){
			$subject = $row['subject'];
	        $lablink = $row['lablink'];
	        $sub_date = $row['sub_date'];
	        $filename = $row['filename'];
	        $lab_id = $row['lab_id'];
	        $datastr = $datastr.'<tr><td>'.$subject.'</td><td><a href="user-addlab.html?lab_id='.$lab_id.'">'.$filename.'</a></td><td>'.$sub_date.'</td></tr>';
	        $serial = $serial + 1;
		}
		if($pages != 1)
		for($num =1;$num<=$pages;$num++){
			$class='';
			if($num == $page) $class='active';
			$paginate = $paginate . '<li class="'.$class.'"><a  href="?page='.$num.'">'.$num.'</a></li>';
		}
		echo $datastr.'|'.$paginate;
	}
	else {
		echo $not;
	}



}
function postfaq () {
	$conn = $GLOBALS['conn'];
	$question = $_REQUEST['question'];
	$askedby = $_REQUEST['askedby'];
	$askedbymail = $_REQUEST['askedbymail'];
	$asked_date = $_REQUEST['asked_date'];
	$sql = "insert into faq (question, askedby, asked_date, askedbymail) values ('$question', '$askedby', '$asked_date', '$askedbymail' )";
	$result = $conn->query($sql);
	if(mysqli_num_rows($result) == 0){
		echo 0;
	} else {
	    echo $askedbymail;
	}
}
function userdetail () {
	$conn = $GLOBALS['conn'];
	$us_id = $_REQUEST['us_id'];
	$sql = "select * from users where user_id = '$us_id'";
	$result = $conn->query($sql);
	$rows = array();
	if(mysqli_num_rows($result) == 0){
		echo 0;
	} else {
		while($row = $result->fetch_assoc()) {
			$rows[] = $row;
	    }
	    echo json_encode($rows);;
	}

}
function proedit () {
	$conn = $GLOBALS['conn'];
	$firstname = $_REQUEST['firstname'];
	$lastname = $_REQUEST['lastname'];
	$us_id = $_REQUEST['us_id'];
	$password = $_REQUEST['password'];
	if ($password) {
		$password = md5($password);
		$sql = "update users set firstname = '$firstname', lastname = '$lastname', password = '$password' where user_id = '$us_id' ";
	} else {
		$sql = "update users set firstname = '$firstname', lastname = '$lastname' where user_id = '$us_id' ";
	}
	$result = $conn->query($sql);
	if(mysqli_num_rows($result) == 0){
		echo 0;
	} else {
		echo 'success';
	}
}
function userfaqlist () {
	$conn = $GLOBALS['conn'];
	$datastr = '';
	$perpage = 10;
	$page = $_REQUEST['page'];
	$query = "select count(faq_id) from faq where answer is not null";
	$result = $conn->query($query);
	$count = (int)$result->fetch_assoc()['count(faq_id)'];
	$paginate ='';
	if($count == 0){
		echo 0;
	}
	else {
		$pages = ceil($count/$perpage);
		$start = (($page - 1)*$perpage);
		$serialno = $start + 1;
		$result = $conn->query("select * from faq where answer is not null limit $start, $perpage");
		while($row = $result->fetch_assoc()){
			$question = $row['question'];
			$qid = $row['faq_id'];
			$datastr = $datastr.'<tr><td>'.$serialno.'</td><td>'.$question.'</td><td><a href="user-faqanswer.html?id='.$qid.'"><button type="button" class="btn btn-success btn-xs">View</button></a></td></tr>';
	        $serialno = $serialno + 1;
		}
		if($pages != 1)
		for($num =1;$num<=$pages;$num++){
			$class='';
			if($num == $page) $class='active';
			$paginate = $paginate . '<li class="'.$class.'"><a  href="?page='.$num.'">'.$num.'</a></li>';
		}
		echo $datastr.'|'.$paginate;
	}
}
function faqdetail () {
	$conn = $GLOBALS['conn'];
	$faq_id = $_REQUEST['faq_id'];
	$sql = "select * from faq where faq_id='$faq_id'";
	$user = $conn->query($sql)->fetch_assoc()['askedby'];
	$username = $conn->query("select * from users where user_id ='$user'")->fetch_assoc()["firstname"];
	$result = $conn->query($sql)->fetch_assoc();
	$result['askedbyname'] = $username;
	echo json_encode($result);

}
function userlabscore () {
	$conn = $GLOBALS['conn'];
	$user_id = $_REQUEST['user_id'];
	$datastr = '';

	$perpage = 10;
	$page = $_REQUEST['page'];
	$query = "select count(labmarks_id) from labmarks where submittedby = '$user_id' and answered = '1'";
	$result = $conn->query($query);
	$count = (int)$result->fetch_assoc()['count(labmarks_id)'];
	$paginate ='';
	if($count == 0){
		echo 0;
	}
	else {
		$pages = ceil($count/$perpage);
		$start = (($page - 1)*$perpage);
		$serialno = $start + 1;
		$result = $conn->query("select * from labmarks where submittedby = '$user_id' and answered = '1' limit $start, $perpage");
		while($row = $result->fetch_assoc()){
			$datastr = $datastr.'<tr><td>'.$serialno.'</td><td>'.$row['lab_name'].'</td><td>'.explode(" ",$row['sub_date'])[0].'</td><td><input type="text" value="'.$row['marks'].'" disabled></td></tr>';
			$serialno = $serialno + 1;
		}
		if($pages != 1)
		for($num =1;$num<=$pages;$num++){
			$class='';
			if($num == $page) $class='active';
			$paginate = $paginate . '<li class="'.$class.'"><a  href="?page='.$num.'">'.$num.'</a></li>';
		}
		echo $datastr.'|'.$paginate;
	}
		
}
?>