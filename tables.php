<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$sql = "CREATE DATABASE IF NOT EXISTS wirelesssecuritylabs";
if ($conn->query($sql) === TRUE) {
        $conn = new mysqli($servername, $username, $password, 'wirelesssecuritylabs');
} else {
    echo "Error creating database: " . $conn->error;
}
//Creating users table
$sql = "create table if not exists users (
	user_id int NOT NULL AUTO_INCREMENT,
	firstname varchar(20) NOT NULL,
	lastname varchar(20) NOT NULL,
	email varchar(40) NOT NULL,
	password text NOT NULL,
	role varchar(20) NOT NULL,
	approve boolean NOT NULL default 1,
	reg_date TIMESTAMP,
	PRIMARY KEY (user_id),
	UNIQUE (Email)
)";
$password = md5('open');
if (!$conn->query($sql))
    echo "Error creating table: " . $conn->error;
$sql = "INSERT INTO  users (FirstName, LastName, Email, Password, Role, Approve)
VALUES ('Admin', 'support', 'admin@gmail.com', '$password', 'admin', true)";
if (!$conn->query($sql))
    echo "Error: " . $sql . "<br>" . $conn->error;
//$sql = "INSERT INTO  users (FirstName, LastName, Email, Password, Role, Approve)
//VALUES ('Chaitanya', 'Chaitanya', 'chaitanya@gmail.com', '$password', 'user', true)";
//if (!$conn->query($sql))
  //  echo "Error: " . $sql . "<br>" . $conn->error;


//Creating lab marks table for storing student marks
$sql = "create table if not exists labmarks(
	labmarks_id int not null auto_increment,
	submittedby int not null,
	filepath text not null,
	marks int not null default 0,
	sub_date text,
	answered boolean NOT NULL default 0,
	lab_name text not null,
	filename text not null,
	primary key(labmarks_id),
	labref int not null
)";
if (!$conn->query($sql))
    echo "Error creating table: " . $conn->error;


//Creating lab table for creating labs by the admin
$sql = "create table if not exists lab(
	lab_id int not null auto_increment,
	subject text not null,
	lablink text not null,
	sub_date text not null,
	filename text not null,
	submittedby text not null, 
	primary key(lab_id)
)";
if (!$conn->query($sql))
    echo "Error creating table: " . $conn->error;

//FAQ table for storing ques by user and answers by admin
$sql = "create table if not exists faq(
	faq_id int not null auto_increment,
	question text not null,
	answer text ,
	askedby text not null,
	askedbymail text not null,
	asked_date text,
	primary key(faq_id)
)";
if (!$conn->query($sql))
    echo "Error creating table: " . $conn->error;
$conn->close();
?>