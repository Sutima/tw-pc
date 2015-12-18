<?php

// EVE SDE table name
$eve_dump = 'eve_carnyx';

try {
    $mysql = new PDO(
        'mysql:host=localhost;dbname=tripwire;charset=utf8',
        'root',
        'password',
        Array(
            PDO::ATTR_PERSISTENT     => true
        )
    );
} catch (PDOException $error) {
    echo 'DB error';//$error;
}

?>
