<?php
include('common.php');

$html = new iframe\HTML\HTML;
$sql = new iframe\DB\Sql("Data");

$action = i($QUERY, 'action');
$output = '';
$format = i($QUERY, 'format', 'sql');

$first_table = '';

if($action == 'Generate Code') {
	$db_structure = $PARAM['structure'];
	
	$lines = explode("\n", trim($db_structure) . "\nEND"); //We add the END tag to this to make sure the 'if($last_table) {' check is done on the very last table as well.
	$current_table = '';
	$structure = array();
	
	// Some field presets
	$keys			= array('type' => 'INT',		'length' => 11,		'special' => 'unsigned',	'null_mode' => 'NOT NULL');
	$text_fields	= array('type' => 'VARCHAR',	'length' => 100,	'default' => '',	'null_mode' => 'NOT NULL');
	$textareas		= array('type' => 'VARCHAR',	'length' => 255,	'default' => '',	'null_mode' => 'NOT NULL');
	$datetime_fields= array('type' => 'DATETIME',	'null_mode' => 'NOT NULL');
	$content_fields	= array('type' => 'TEXT',		'null_mode' => 'NOT NULL');
	$date_fields	= array('type' => 'DATE');
	$enum_fields	= array('type' => 'ENUM');
	$int_fields		= array('type' => 'INT',		'length' => 5);
	$float_fields	= array('type' => 'FLOAT',		'length' => 5);
	$binary_fields	= array('type' => 'ENUM', 		'values' => array('0','1'), 	'default' => '1');
	
	$primary_key = -1;
	foreach($lines as $org_line) {
		$comment = '';
		if(strpos($org_line, '#') !== false) {
			$comment = trim(substr($org_line, strpos($org_line, '#') + 1));
		}
		$l = rtrim(preg_replace("/#.*$/",'', $org_line)); //Ignore comments.
		$field_index = 0;
		
		if(ltrim($l) == $l) { //If there is no leading space, its an Table name
			$last_table = $current_table;
			$current_table = $l;
			
			if($last_table) {
				if($primary_key == -1) { // The id field was not specified
					array_unshift($structure[$last_table]['fields'], array_merge($keys, array('name'=>'id', 'auto_increment'=>true, 'primary_key'=>true)));
				} else {
					$primary_key = -1;
				}
			}
			if($current_table == 'END') break;
			
			//Add the table to the list.
			$structure[$current_table] = array(
				'fields'	=> array(), 
				'comment'	=> $comment
			);
		
		} else { // Fields
			$parts = preg_split("/\s+/", trim($l));
			$field_name = $parts[0];
			$field_index++;
			$field_info = array(
				'name'	=> $field_name,
				'comment'=>$comment,
			);
			
			// Some preset names
			$fname = strtolower($field_name);
			switch ($fname) {
				case 'id':
					$primary_key = $field_index;
					$field_info['auto_increment'] = true;
					$field_info['primary_key'] = true;
					$field_info = array_merge($keys, $field_info);
				break;
				
				case 'name':
				case 'url':
				case 'site':
				case 'email':
				case 'phone':
				case 'title':
				case 'city':
				case 'username':
				case 'password':
				case 'login':
				case 'user_name':
					$field_info = array_merge($text_fields, $field_info);
				break;

				case 'amount':
					$field_info = array_merge($float_fields, $field_info);
				break;
				
				case 'address':
				case 'description':
					$field_info = array_merge($textareas, $field_info);
				break;
				
				case 'content':
					$field_info = array_merge($content_fields, $field_info);
				break;
				
				case 'status':
					$field_info = array_merge($binary_fields, $field_info);
				break;

				case 'sort':
				case 'order':
				case 'sort_order':
				case 'sort_by':
					$field_info = array_merge($int_fields, $field_info);
				break;

				default:
					$field_info = array_merge($text_fields, $field_info);
			}

			if($fname == 'url' or $fname == 'link') {
				$field_info['length'] = 200;
			}
			
			// Foreign keys
			if(substr($fname, -3) == '_id') {
				$field_info = array_merge($field_info, $keys);
				$field_info['index'] = true;
			}
			
			// Date/Time fields.
			if(substr($fname, -3) == '_on' or substr($fname, -3) == '_at') {
				$field_info = array_merge($field_info, $datetime_fields);
				unset($field_info['length']);
			}
			
			if(isset($parts[1])) { // If there is a space in the field name, the stuff that comes after the space are keywords...
				switch(strtoupper($parts[1])) {
					case 'TEXT':
					case 'CONTENT':
						$field_info = array_merge($field_info, $content_fields);
						break;

					case 'BINARY':
					case 'STATUS':
						$field_info = array_merge($field_info, $binary_fields);
						break;

					case 'INT':
						$field_info = array_merge($field_info, $int_fields);
						break;

					case 'FLOAT':
						$field_info = array_merge($field_info, $float_fields);
						break;

					case 'FILE':
						$field_info = array_merge($field_info, $text_fields);
						break;

					case 'ENUM':
						$field_info = array_merge($field_info, $enum_fields);
						break;
				}

				if(strpos($parts[1], '(') !== false) {
					$bits = explode('(', $parts[1]);
					$type = strtolower($bits[0]);
					
					$info = '';
					if(isset($bits[1])) $info = str_replace(')','', $bits[1]);
					
					if($type == 'enum') $parts[1] = $info;
					elseif(empty($bits[1])) {
						if(is_numeric($type)) $field_info['length'] = $type; //	Works with stuff like 'description 255'
						else $field_info['type'] = $type;
					}
					elseif($type and is_numeric($info)) {	 //	Works with stuff like 'description varchar(255)'
						$field_info['type'] = strtoupper($type);
						$field_info['length'] = $info;
					}
					else {
						$field_info['type'] = strtoupper($type);	// Works with stuff like 'description text'
					}
				}
				if(strpos($parts[1], ',') !== false) { // The text after the space is CSV - meaning its enum.
					$field_info = array_merge($field_info, $enum_fields);
					$field_info['values']	= explode(',', $parts[1]);
					unset($field_info['length']);
					$field_info['default']	= $field_info['values'][0];
				}
			}
			
			//Nothing fits the field name 
			if(count($field_info) == 1) { // - set it as a text field...
				$field_info = array_merge($text_fields, $field_info);
			}
			
			array_push($structure[$current_table]['fields'], $field_info);
		}
	}
	
	// Code generation
	if($format === 'sql') {
		foreach($structure as $table => $fields) {
			if(!$table) continue;
			code("CREATE TABLE IF NOT EXISTS `$table` (");
			
			$final_statements = array();
			$field_creations = array();
			foreach($fields['fields'] as $f) {
				$length = '';
				$default = '';
				$auto_increment = '';
				$null_mode = '';
				$special = '';
				$comment = '';

				if(i($f,'length'))			$length = "($f[length])";
				elseif(i($f,'values'))		$length = "('" . join("','", $f['values']) . "')";

				if(i($f, 'default')) 		$default = "DEFAULT '" . $f['default'] . "'";
				if(i($f,'auto_increment'))	$auto_increment = 'auto_increment';
				if(i($f,'null_mode'))		$null_mode = $f['null_mode'];
				if(i($f,'special') == 'unsigned') $special = 'unsigned';
				if(i($f,'comment'))			$comment = "COMMENT='$f[comment]'";
							
				if(i($f,'primary_key')) $final_statements[] = "	PRIMARY KEY (`$f[name]`)";
				elseif(i($f,'index')) $final_statements[] = "	KEY (`$f[name]`)";
				
				$field_creations[] = rtrim("	`$f[name]` $f[type] $length $default $special $null_mode $auto_increment $comment");
			}
			
			code(join(",\n", $field_creations), false);
			if($final_statements) code(",");
			
			code(join(",\n", $final_statements));

			$table_comment = "";
			if(i($fields, 'comment')) $table_comment = "COMMENT='$fields[comment]'";
			code(") DEFAULT CHARSET=utf8 $table_comment;\n");
		}


	} elseif($format === 'laravel_migrate') {
		foreach($structure as $table => $fields) {
			if(!$table) continue;
			if(!$first_table) $first_table = $table;

			code("<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Create${table}Table extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('${table}', function (Blueprint \$table) {");
			
			$final_statements = array();
			$field_creations = array();
			foreach($fields['fields'] as $f) {
				$length = '';
				$line = '';

				if(i($f,'auto_increment'))	$line = "\$table->increments('${f['name']}')";

				if(i($f,'length'))			$length = "," . $f['length'];
				elseif(i($f,'values'))		$line = "\$table->enum('${f['name']}', ['" . join("','", $f['values']) . "'])";

				if($f['type'] == 'VARCHAR') {
					$line = "\$table->string('${f['name']}'$length)";
				} elseif($f['type'] == 'INT') {
					$line = "\$table->integer('${f['name']}')";
					if(i($f,'special') == 'unsigned') {
						$line = "\$table->bigInteger('${f['name']}')->unsigned()";
					}
				} elseif($f['type'] == 'DATE') {
					$line = "\$table->date('${f['name']}')";
				} elseif($f['type'] == 'DATETIME') {
					$line = "\$table->dateTime('${f['name']}')";
				} elseif($f['type'] == 'FLOAT') {
					$line = "\$table->float('${f['name']}'$length)";
				} elseif($f['type'] == 'TEXT') {
					$line = "\$table->text('${f['name']}')";
				}
				// ENUM is already handed

				if(i($f, 'default')) $line .= "->default('${f['default']}')";
				
				if(i($f,'null_mode') != 'NOT NULL') $line .= "->nullable()";
				if(i($f,'comment')) $line .= "->default('${f['comment']}')";
							
				if(i($f,'primary_key')) {
					$line = "\$table->increments('${f['name']}')";
				}
				elseif(i($f,'index')) $line .= "->index('${f['name']}')";
				
				code("            " . $line . ";");
			}
			
			code("        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('${table}');
    }
}");
		}
	}
	//dump($structure);
	
	// if(empty($PARAM['name'])) $PARAM['name'] = uniqid();
	// file_put_contents(joinPath('Dump', $PARAM['name'] . '.txt'), $db_structure);
	
} elseif($action == 'Execute' and $format == 'sql') {
	$database = $PARAM['database'];
	
	$output = i($PARAM, 'output');

	if(!$database) die("Database name not provided");
	if(!$output) die("SQL not provided");
	
	Sql::$mode = 'p';
	$sql->execQuery("CREATE DATABASE IF NOT EXISTS $database");
	$sql->selectDb($database);
	$statements = explode(";", $output);
	foreach($statements as $s) {
		if(!trim($s)) continue;
		$sql->execQuery(trim($s));

		if($sql->error_message) $QUERY['error'] = $sql->error_message;
		else $QUERY['success'] = $s;
	}
	
} elseif($action == 'Download' and $format == 'laravel_migrate') {
	$output = i($PARAM, 'output');
	$table = i($PARAM, 'table');
	if(!$output) die("Code not provided");

	$filename = date('Y_m_d_his') . '_create_' . strtolower($table) . '_table.php';
	
	header("Content-Disposition: attachment; filename=". $filename);
	print $output;
	exit;
} 

render();

function code($fragment, $add_newline = true) {
	global $output;
	$output .= $fragment;
	if($add_newline) $output .= "\n";
}
