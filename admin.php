<?php
error_reporting(E_ALL ^ E_DEPRECATED);
require_once("pmail.php");
include "dbsettings.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postaction = $_POST["action"];
	$postaction();
}
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $getaction = $_GET["action"];
 	$getaction();
}
function checkLabname () {
	$conn = $GLOBALS['conn'];
	$filename = $_REQUEST['filename'];
	$sql = "select * from lab where filename='$filename'";
	$result = $conn->query($sql);
	if(mysqli_num_rows($result) == 0){
		echo 0;
	}
	else {
		echo 1;
	}
}
function addmaterial (){
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
	$conn = $GLOBALS['conn'];
	$subject = $_REQUEST['subject'];
	$filename = $_REQUEST['filename'];
	$sub_date = $_REQUEST['sub_date'];
	$submittedby =$_REQUEST['submittedby'];
	$destination = './upload/'.$filename;
	mkdir($destination);
	copy_directory('./upload/temp/',$destination);


	$rootPath = realpath($destination);

	// Initialize archive object
	$zip = new ZipArchive();
	$zip->open($destination.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

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
	$destination = $destination.'.zip';

	$sql = "insert into lab (subject, lablink,  sub_date, filename, submittedby) values ('$subject', '$destination', '$sub_date', '$filename', '$submittedby')";
	if (!$conn->query($sql))
	    echo "Error creating table: " . $conn->error;
	echo 'labname:'.$labname.'filename:'.$filename.'lastdate:'.$lastdate .'destination:'.$destination;
}
function loadlabs () {
	$conn = $GLOBALS['conn'];
	$datastr = '';
	
	$perpage = 10;
	$page = $_REQUEST['page'];
	$submittedby = $_REQUEST['submittedby'];
	$query = "select count(lab_id) from lab where submittedby = '$submittedby' ";
	$result = $conn->query($query);
	$count = (int)$result->fetch_assoc()['count(lab_id)'];
	$paginate ='';
	if($count == 0){
		echo 0;
	}
	else {
		$pages = ceil($count/$perpage);
		$start = (($page - 1)*$perpage);
		$serialno = $start+1;
		$result = $conn->query("select * from lab where submittedby = '$submittedby' limit $start, $perpage");
		while($row = $result->fetch_assoc()){
			$subject = $row['subject'];
	        $lablink = $row['lablink'];
	        $sub_date = $row['sub_date'];
	        $filename = $row['filename'];
	        $lab_id = $row['lab_id'];
	        $datastr = $datastr.'<tr><td><div class="btn-group" data-toggle="buttons"><label class="btn btn-primary bcheck" id="'.$lab_id.'"><input type="checkbox" autocomplete="off"><span class="glyphicon glyphicon-ok"></span></label></div></td><td>'.$serialno.'</td><td>'.$subject.'</td><td><a href="'.$lablink.'">'.$filename.'</a></td><td>'.$sub_date.'</td></tr>';
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
	
	// if(mysqli_num_rows($result) == 0){
	// 	echo 0;
	// } else {
	// 	while($row = $result->fetch_assoc()) {
	//         $labname = $row['labname'];
	//         $lablink = $row['lablink'];
	//         $lastsub_date = $row['lastsub_date'];
	//         $filename = $row['filename'];
	//         $lab_id = $row['lab_id'];
	//         $datastr = $datastr.'<tr><td><div class="btn-group" data-toggle="buttons"><label class="btn btn-primary bcheck" id="'.$lab_id.'"><input type="checkbox" autocomplete="off"><span class="glyphicon glyphicon-ok"></span></label></div></td><td>'.$serialno.'</td><td>'.$labname.'</td><td><a href="'.$lablink.'">'.$filename.'</a></td><td>'.$lastsub_date.'</td></tr>';
	//         $serialno = $serialno + 1;
	//     }
	//     echo $datastr;
	// }
}
function deletelabs () {
	$conn = $GLOBALS['conn'];
	$deletelist = explode("," ,$_REQUEST['deletelist']);
	foreach ($deletelist as $list) {
		$list = $list;
		$sql = "delete from lab where lab_id='$list'";
		$result = $conn->query($sql);
	}
	echo 'success';

}
function loadusers () {
	$conn = $GLOBALS['conn'];
	$datastr = '';

	$perpage = 10;
	$page = $_REQUEST['page'];
	$query = "select count(user_id) from users where approve=0";
	$result = $conn->query($query);
	$count = (int)$result->fetch_assoc()['count(user_id)'];
	$paginate ='';
	if($count == 0){
		echo 0;
	}
	else {
		$pages = ceil($count/$perpage);
		$start = (($page - 1)*$perpage);
		$result = $conn->query("select * from users where approve=0 limit $start, $perpage");
		while($row = $result->fetch_assoc()){
			$firstname = $row['firstname'];
	        $lastname = $row['lastname'];
	        $email = $row['email'];
	        $user_id = $row['user_id'];
	        $datastr = $datastr.'<tr><td>'.$firstname.'</td><td>'.$lastname .'</td><td>'.$email.'</td><td><button type="button" class="btn btn-success btn-xs" name="'.$user_id.'">Accept</button></td><td><button type="button" class="btn btn-danger btn-xs" name="'.$user_id.'">Deny</button></td></tr>';
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
function approveuser () {
	
	$conn = $GLOBALS['conn'];
	$addusr_id = $_REQUEST['addusr_id'];
	// $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
 //    $temppass = substr( str_shuffle( $chars ), 0, 8 );
 //    $password = md5($temppass);
	$sql = "update users set approve = '1' where user_id = '$addusr_id' ";
	$result = $conn->query($sql);
	//$mailresult = $conn->query("select * from users where user_id='$usr_id'");
	echo 'success';
	// while($row = $mailresult->fetch_assoc()){
	// 	$email = $row['email'];
	// }

	
	// $content = "<p>Your password for ".$email." is: <b>". $temppass."</b></p>";
	// $subject ="Your account has been approved by the Admin";
	// mymail($email, $subject, $content);
}
function deleteuser () {
	$conn = $GLOBALS['conn'];
	$delusr_id = $_REQUEST['delusr_id'];
	$sql = "delete from users where user_id = '$delusr_id'";
	$result = $conn->query($sql);
	if(mysqli_num_rows($result) == 0){
		echo 0;
	} else {
	    echo 'success';
	}
}
function loadapprovedusers () {
	$conn = $GLOBALS['conn'];
	$datastr = '';
	$serialno = 1;
	$perpage = 10;
	$page = $_REQUEST['page'];
	$query = "select count(user_id) from users  where approve='1' and ( role='user' or role='faculty') ";
	$result = $conn->query($query);
	$count = (int)$result->fetch_assoc()['count(user_id)'];
	$paginate ='';
	if($count == 0){
		echo 0;
	}
	else {
		$pages = ceil($count/$perpage);
		$start = (($page - 1)*$perpage);
		$result = $conn->query("select * from users  where approve='1' and ( role='user' or role='faculty') limit $start, $perpage");
		while($row = $result->fetch_assoc()){
			$firstname = $row['firstname'];
	        $lastname = $row['lastname'];
	        $email = $row['email'];
	        $role = ($row['role']=='user')?'student':$row['role'] ;
	        $user_id = $row['user_id'];
	        $datastr = $datastr.'<tr><td><div class="btn-group" data-toggle="buttons"><label class="btn btn-primary bcheck" id="'.$user_id.'"><input type="checkbox" autocomplete="off"><span class="glyphicon glyphicon-ok"></span></label></div></td><td>'.$firstname.'</td><td>'.$lastname.'</td><td>'.$email.'</td><td>'.$role.'</td><td><a href="admin-edituser.html?id='.$user_id.'" style="color:white;"<button type="button" class="btn btn-success btn-xs">Edit</button></a></td></tr>';
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
function deleteapproved() {
	$conn = $GLOBALS['conn'];
	$deletelist = explode("," ,$_REQUEST['deletelist']);
	foreach ($deletelist as $list) {
		if($list != ""){
			$list = $list;
			$sql = "delete from users where user_id='$list'";
			$result = $conn->query($sql);
		}
	}
	echo 'success';
}
function loadfaq () {
	$conn = $GLOBALS['conn'];
	$sql = "select * from faq where answer is NULL";
	$result = $conn->query($sql);
	$datastr = '';
	$perpage = 10;
	$page = $_REQUEST['page'];
	$query = "select count(faq_id) from faq where answer is NULL";
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
		$result = $conn->query("select * from faq where answer is NULL limit $start, $perpage");
		while($row = $result->fetch_assoc()){
			$question = $row['question'];
			$askedby = $row['askedby'];
	        $faq_id = $row['faq_id'];
	        $user = $row['askedby'];
	        $email = $row['askedbymail'];
	        $datastr = $datastr.'<tr><td>'.$serialno.'</td><td>'.$question.'</td><td>'.$user .'</td><td>'.$email.'</td><td><a href="admin-faqanswer.html?id='.$faq_id.'"><button type="button" class="btn btn-success btn-xs">Answer</button></a></td><td><button type="button" class="btn btn-danger btn-xs" id="'.$faq_id.'">Decline</button></td></tr>';
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
function deletefaq () {
	$conn = $GLOBALS['conn'];
	$deletefaqid = $_REQUEST['deletefaqid'];
	$sql = "delete from faq where faq_id='$deletefaqid'";
	$result = $conn->query($sql);
	echo 'success';
}
function faqdetail () {
	$conn = $GLOBALS['conn'];
	$faq_id = $_REQUEST['faq_id'];
	$sql = "select * from faq where faq_id='$faq_id'";
	$result = $conn->query($sql)->fetch_assoc();
	echo json_encode($result);

}
function faqans() {
	$conn = $GLOBALS['conn'];
	$answer = $_REQUEST['answer'];
	$faq_id = $_REQUEST['faq_id'];
	$sql = "update faq set answer = '$answer' where faq_id = '$faq_id'";
	$result = $conn->query($sql);

	$sql = "select * from faq where faq_id = '$faq_id'";
	$mailresult = $conn->query($sql);
    while($row = $mailresult->fetch_assoc()){
		$email = $row['askedbymail'];
		$question = $row['question'];
	}
	$content = "<p><b>Question: </b>".$question."</p></br><p><b>Answer: </b>".$answer."</p>";
	$subject ="Your FAQ has been Answered";
	mymail($email, $subject, $content);
	// if(mysqli_num_rows($result) == 0){
	// 	echo 0;
	// } else {
		// $sql = "select * from faq where faq_id = '$faq_id'";
		// $mailresult = $conn->query($sql);
	 //    while($row = $mailresult->fetch_assoc()){
		// 	$email = $row['askedbymail'];
		// 	$question = $row['question'];
		// }
		// $content = "<h2>Question: </h2>".$question."</br><h2>Answer: </h2>".$answer;
		// $subject ="Your FAQ has been Answered";
		// mymail('saitejagoli0@gmail.com', 'admin test', 'success');
	// }
}
function getlablist () {
	$conn = $GLOBALS['conn'];
	$sql = "select * from lab";
	$result = $conn->query($sql);
	$str = '<option></option>';
	if(mysqli_num_rows($result) == 0){
		echo 0;
	} else {
	    while($row = $result->fetch_assoc()){
	    	$sub_name = $row["subject"];
	    	$str = $str.'<option value="'.$sub_name.'">'.$sub_name.'</option>';
	    }
	 	echo $str;
	}
}
function loadlabscore () {
	$conn = $GLOBALS['conn'];
	$labname = $_REQUEST['labname'];
	$serialno = 1;
	$datastr = '';
	$perpage = 10;
	$page = $_REQUEST['page'];
	$query = "select count(labmarks_id) from labmarks where lab_name='$labname'";
	$result = $conn->query($query);
	$count = (int)$result->fetch_assoc()['count(labmarks_id)'];
	$paginate ='';
	if($count == 0){
		echo 0;
	}
	else {
		$pages = ceil($count/$perpage);
		$start = (($page - 1)*$perpage);
		$result = $conn->query("select * from labmarks where lab_name='$labname' limit $start, $perpage");
		while($row = $result->fetch_assoc()){
			$user_id = $row['submittedby'];
			$sql = "select * from users where user_id='$user_id'";
			$username = $conn->query($sql)->fetch_assoc()['firstname'];
			$email = $conn->query($sql)->fetch_assoc()['email'];
	    	$datastr = $datastr.'<tr><td>'.$serialno.'</td><td>'.$username.'</td><td>'.$email.'</td><td><input type="number" min="0" id="'.$row['labmarks_id'].'" value="'.$row['marks'].'"></td><td><a href="'.$row['filepath'].'">'.$row['filename'].'</a></td></tr>';
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
function updatemarks () {
	$conn = $GLOBALS['conn'];
	$updatemarks = explode("," ,$_REQUEST['marksupdate']);
	foreach ($updatemarks as $list) {
		$list = explode(":",$list);
		$marks = (int)$list[1];
		$lab_id = $list[0];
		$sql = "update labmarks set answered = 1, marks = '$marks'  where labmarks_id = '$lab_id'";
		$result = $conn->query($sql);
	}
	echo 'success';
}
?>