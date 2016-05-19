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
function emailCheck () {
	$obj = $_REQUEST;
	$conn = $GLOBALS['conn'];
	$email =  $_REQUEST['email'];
	$sql = "select * from users where email='$email'";
	$result = $conn->query($sql);
	echo (mysqli_num_rows($result) == 0) ? 1 : 0 ;
}
function addUser () {
	$conn = $GLOBALS['conn'];
	$umail = $_REQUEST['umail'];
	$firstname = $_REQUEST['firstname'];
	$lastname = $_REQUEST['lastname'];
	$password =$_REQUEST['password'];
	$role = $_REQUEST['role'];
	$password = md5($password);
	$sql = "insert into users (email, firstname, lastname, password, role, approve) values ('$umail', '$firstname', '$lastname', '$password', '$role' ,'0')";
	$result = $conn->query($sql);
	echo 'Inserted successfull';
}

function credCheck () {
	$conn = $GLOBALS['conn'];
	$email = $_REQUEST['mail'];
	$password = $_REQUEST['password'];
	$password = md5($password);
	$sql = "select * from users where email='$email' and password='$password'";
	$result = $conn->query($sql);
	if(mysqli_num_rows($result) == 0){
		echo 0;
	} else {
		while($row = $result->fetch_assoc()) {
	        $approve = $row['approve'];
	        $name = $row['firstname'];
	        $role = ($row['role'] == 'admin') ? 1 : (($row['role'] == 'faculty') ? 2 : 0 );
	        $mailid = $row['email'];
	        $user_id = $row['user_id'];
	    }
	    if($approve == 0) 
    	{
    		echo 1;
    	}
    	else{
    		echo '{"name":"'.$name.'","email":"'.$mailid.'", "isadmin":"'.(int)$role.'", "user_id":"'. $user_id.'"}';
    	}
	}
}
?>