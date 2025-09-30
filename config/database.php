<?php
$host = 'localhost';
$username = 'root';
$password= '';
$dbname = 'user_management';

try{
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $createTableQuery = "
     CREATE TABLE IF NOT EXISTS users(
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
         username VARCHAR(50) NOT NULL UNIQUE,
         email VARCHAR(100) NOT NULL UNIQUE,
         password VARCHAR(255) NOT NULL,
         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP     
     )";

     $pdo->exec($createTableQuery);

}catch(PDOException $e){
     die("database connection failed" .$e->getMessage());
}
?>