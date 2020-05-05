<h1>Dber</h1>

<form action='' method='post' class="form-area">
<?php 
if($output and $action == 'Generate Code') {
	$action_text = 'Execute';
	if($format == 'laravel_migrate') $action_text = "Download";

	$html->buildInput('output', 'Result', 'textarea', $output, array('rows'=>15, 'cols'=>70));
	$html->buildInput('database','Database');
	$html->buildInput('table','', 'hidden', $first_table);
	$html->buildInput('format','', 'hidden', $format);
	$html->buildInput("action", '', 'submit', $action_text);

} elseif($output and $action == 'Execute') {
	echo "<h3>Result</h3>";
	if(isset($QUERY['error'])) echo $QUERY['error'];
	else echo $QUERY['success'];
	print "<pre><code>" . $output . "</code></pre>";

} else {
	//Create the form.
	$html->buildInput('name');
	$html->buildInput('structure', 'DB Structure', 'textarea', '', array('rows'=>15, 'cols'=>70));
	$html->buildInput('format', 'Format', 'select', $format, ['options' => ['sql' => 'SQL', 'laravel_migrate' => "Laraver Migrate"]]);
	$html->buildInput('action', '&nbsp;', 'submit', 'Generate Code');
}
?>
</form><br />


<h3>Sample DB</h3>
<pre>
# This is a comment - all comments are ignored
Activity 		# Table name - no indentation
	name  		# Field - part of the activity table because of the indentation
	added_on	# This will become a datetime field - any field name that ends with '_on' will become datetime.
	description	TEXT # This will become a text field - It can be any of TEXT, CONTENT, BINARY, STATUS, INT, FLOAT, FILE, ENUM
	priority high,medium,low	# Enum fields with the given three value. As long as there is a ',' in whatever follows the field name, it becomes enum. The first value will be the default.
	activity_id	# This becomes an INT forign key with index enabled.

# Also, even though we didn't explicity give an 'id' field, it will be added to the table.


# Example...
Activity
	id
	name
	description
	product_url
	project_folder_url
	documentation_url
	retrospective_url
	priority medium,high,low
	added_on
	updated_on
	ended_on
	type task,project
	task_type other,report,csv,bugfix,spreadsheet,meeting,non_tech,enhancement
	estimate_hours	INT
	actual_hours	INT
	due_on
	product_owner
	developer
	score	FLOAT
	activity_id
</pre>