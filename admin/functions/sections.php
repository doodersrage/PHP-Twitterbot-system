<?PHP

// load site forms
// open forms directory 
$myDirectory = opendir(HOME_PATH.'admin/forms/');

// load each form file
while($entryName = readdir($myDirectory)) {
	if(strtolower(substr($entryName, -4)) == '.php') require(HOME_PATH.'admin/forms/'.$entryName);
}


?>