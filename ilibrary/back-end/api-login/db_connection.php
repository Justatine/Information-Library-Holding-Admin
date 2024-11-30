<?php
$connection = mysqli_connect("localhost", "root", "", "ilibrary");

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
