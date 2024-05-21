<?php 
$servername ="localhost";
$username ="root";  
$password ="";
$dbname ="interlearn";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if($conn) {
	//echo "<br>connection successfull";
}
else {
echo "Error Connecting to the database";
}	
?>