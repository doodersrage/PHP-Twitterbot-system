<?PHP
// generate encrypted admin user password
function pass_salt($password){

	$salt = '1sa1t9a5s*@';
	
	$new_pass = md5($salt.$password);

return $new_pass;
}

echo pass_salt('pass');