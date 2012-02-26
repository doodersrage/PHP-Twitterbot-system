<?PHP

// print login form
function login_frm() {
    
  $op = '<form id="form1" name="form1" method="post">
  <table border="0" cellspacing="3" cellpadding="3" class="user_tbl" align="center">
	<tr>
	  <th colspan="2">Admin Login Form</th>
	</tr>
	<tr>
	  <td>Email:</td>
	  <td><input name="email" type="text" size="20" maxlength="140" value="'.$_SESSION['email'].'"></td>
	</tr>
	<tr>
	  <td>Password:</td>
	  <td><input name="password" type="password" size="20" maxlength="30" value="'.$_SESSION['password'].'"></td>
	</tr>
	<tr>
	  <td colspan="2" align="center"><input name="admin_login" type="hidden" value="1"><input name="Submit" type="submit" value="Submit"></td>
	</tr>
  </table>
  </form>';
 
return $op;
}

?>