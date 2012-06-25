#!/usr/bin/php
<?php
require_once 'lib/init.php';
require_once 'lib/attribute_functions.php';

if (count($argv) < 2) {
	echo "Import attributes\n";
	echo "Usage: attr-import.php INPUT_XML\n";
	exit(1);
}

$defaultParentId = Mage::app()->getDefaultStoreView()->getRootCategoryId();

// Load File XML
$xmlFilename = $argv[1];
echo "Loading $xmlFilename...";
$attrsXml = simplexml_load_file($xmlFilename);
echo " Loaded.\n";
foreach ($attrsXml as $attrEl) {
	$code = (string) $attrEl->id;
	$label = (string) $attrEl->name;
	$type = (string) $attrEl->type;
	$configurable = $attrEl->type == '1' || $attrEl->type == 'true';
	
	switch ($type) {
		case 'data':
			$datatype = (string) $attrEl->datatype;
			$backendTypes = array(
				'string'	=> 'varchar',
				'varchar'	=> 'varchar',
				'int'		=> 'int',
				'decimal'	=> 'decimal',
				'double'	=> 'decimal',
				'datetime'	=> 'datetime',
				'text'		=> 'text',
				'currency'	=> 'decimal');
			$frontendInputs = array(
				'string'	=> 'text',
				'varchar'	=> 'text',
				'int'		=> 'text',
				'decimal'	=> 'text',
				'double'	=> 'text',
				'datetime'	=> 'date',
				'text'		=> 'textarea',
				'currency'	=> 'price');
			createDataAttribute($code, $label,
					$backendTypes[$datatype], $frontendInputs[$datatype],
					$configurable);
			break;
		case 'select':
			$optionsStr = (string) $attrEl->options;;
			$options = isset($optionsStr) ? explode(',', $optionsStr) : array();
			createSelectAttribute($code, $label, $configurable, $options);
			break;
		default:
			throw new Exception("Unknown attribute type: $type");
	}
}
