<?php

// Class PDO required for this test page and PDO driver
require('PDO/PDO.class.php');	// required for this test

// database connection parameters
$host_db = 'mysql:host=localhost;dbname=test';	// host and database name
$user = 'root';					// database user
$pass = '';					// database password
$sqlite = 'sqlite2:test.sqlite';		// slqite filename

// function to test 2 database created with PDO
function PDO_example(&$PDO, $first_query) {
	
	// set permament connection
        $PDO->setAttribute(PDO_ATTR_PERSISTENT, true);
	
	// read PDO informations
        $informations = '<span style="font-size: 7pt;">Server Info: '.$PDO->getAttribute(PDO_ATTR_SERVER_INFO).'<br />';
	$informations .= 'Server Version: '.$PDO->getAttribute(PDO_ATTR_SERVER_VERSION).'<br />';
	$informations .= 'Client Version: '.$PDO->getAttribute(PDO_ATTR_CLIENT_VERSION).'<br />';
	$informations .= $PDO->getAttribute(PDO_ATTR_PERSISTENT) ? 'Permanent Connection: true</span><hr />' : 'Pconnection: false</span><hr />';
	
	// first query, different for this table creation
	$PDO->exec($first_query);
	
	// PDOStatement and prepare example with ? values
	$smtp = $PDO->prepare('INSERT INTO mytable VALUES(?, ?)');
	for($a = 1; $a <= 4; $a++)
		$smtp->execute(array($a, 'Row Number: '.$a));
	
	// PDOStatement and prepare example with name values
	$smtp = $PDO->prepare('INSERT INTO mytable VALUES(:id, :string)');
	for($a = 1; $a <= 4; $a++)
		$smtp->execute(array(':id'=>($a + 5), ':string'=>'Row Number: '.($a + 4)));
	
	// PDOStatement and prepare example with bindParam method and ? values
        $id = 9;
	$generic_string = 'Row Number: '.$id;
	$smtp = $PDO->prepare('INSERT INTO mytable VALUES(?, ?)');
	$smtp->bindParam(1, $id);
	$smtp->bindParam(2, $generic_string);
	$smtp->execute();
	
	// PDOStatement and prepare example with bindParam method and name values
        $id = 10;
	$generic_string = 'Row Number: '.$id;
	$smtp = $PDO->prepare('INSERT INTO mytable VALUES(:id, :string)');
	$smtp->bindParam(':id', $id);
	$smtp->bindParam(':string', $generic_string);
	$smtp->execute();
	
	// PDOStatement while example
        $output = $informations;
        $smtp = $PDO->prepare('SELECT * FROM mytable');
	$smtp->execute();
	while($result = $smtp->fetch(PDO_FETCH_OBJ))
		$output .= "[{$result->id}] &nbsp; [{$result->generic_string}]<br />";
	echo $output;
	
	// PDOStatement rowCount example
	$smtp = $PDO->prepare('UPDATE mytable SET generic_string = ? WHERE id > 6');
	$smtp->execute(array('something ...'));
	echo 'Changed '.$smtp->rowCount().' rows<br />';
	
	// PDOStatement error generation
	$smtp = $PDO->prepare('SELECT * FROM what');
	if(!$smtp->execute()) {
		$error = &$smtp->errorInfo();
		echo '<strong>Error:</strong> '.$error[2];
	}
	
	// last query
	$PDO->exec('DROP TABLE mytable');
	
}

// MYSQL and SQLITE PDO declaration
// for PHP 5.1 users, call _PDO and not PDO if you want to view the differences
$mysql_PDO = new PDO($host_db, $user, $pass);
$sqlite_PDO = new PDO($sqlite);

// test for each PDO object
echo '<div style="font-family: Helvetica;"><strong>PDO with MYSQL example:</strong><br />';
PDO_example($mysql_PDO, 'CREATE TABLE mytable(id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, generic_string VARCHAR(100))');
echo '<hr />&nbsp; <br /><strong>PDO with SQLITE example:</strong><br />';
PDO_example($sqlite_PDO, 'CREATE TABLE mytable(id INTEGER(10) PRIMARY KEY, generic_string VARCHAR(100))');
echo '</div>';
?> 