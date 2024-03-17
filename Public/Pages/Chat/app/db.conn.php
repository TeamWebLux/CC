<?php

# server name
$sName = "89.116.139.118";
# user name
$uName = "jd";
# password
$pass = "Jayesh8169";

# database name
$db_name = "cc";

#creating database connection
try {
  $conn = new PDO(
    "mysql:host=$sName;dbname=$db_name",
    $uName,
    $pass
  );
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed : " . $e->getMessage();
}
