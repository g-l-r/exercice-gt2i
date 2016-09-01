<?php 
require('MagicParser.php');
function setProduct($record)
{
	$pdo = new PDO('mysql:dbname=gt2i_exercice;host=localhost','root','', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

	// populate array $values_tmp with tags and their content
	foreach($record as $key => $value)
    {
      $values_tmp[strtolower($key)] = $value;
    }
    // remove the tag 'fligne' and its content from $values_tmp
    $fligne_removed = array_shift($values_tmp);
    // remove empty values and their keys from $values_tmp
    $values_tmp = array_diff($values_tmp, array(''));

	// parse the values_tmp array and populate the placeholders, table fields and insert values 
	foreach ($values_tmp as $key => $value) {
		$placeholders[] = '?';
		$db_fields[] = $key;
		$insert_values[] = $value;
	}
	
	$query = "INSERT INTO `exercice_xml` (".implode(',', $db_fields).") VALUES (".implode(',', $placeholders).")";

	$stmt = $pdo->prepare($query);

    $stmt->execute($insert_values);

}

MagicParser_parse("catalogue.XML", "setProduct", "xml|HF_DOCUMENT/fligne/");
?>