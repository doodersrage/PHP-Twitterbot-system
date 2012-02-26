<?PHP

// display edit user form
function user_options_frm() {
  global $system_users;
  
  $system_users->id = $_SESSION['user_id'];
  $system_users->get();
  
  $op = '<form id="form1" name="form1" method="post" action="?mode=process_user_options">
  <table border="0" cellspacing="3" cellpadding="3" class="user_tbl" align="center">
	<tr>
	  <th colspan="2">Edit User Options</th>
	</tr>
	<tr>
	  <td>Email:</td>
	  <td><input name="email" type="text" size="20" maxlength="30" value="'.$system_users->email.'"></td>
	</tr>
	<tr>
	  <td>Send Email On Tweet Post:</td>
	  <td><input name="options[send_email]" type="checkbox" value="1"'.($system_users->options['send_email'] == 1 ? ' checked ' : '').'></td>
	</tr>
	<tr>
	  <td colspan="2" align="center"><input name="id" type="hidden" value="'.$system_users->id.'"><input name="Submit" type="submit" value="Submit"></td>
	</tr>
  </table>
  </form>';
 
return $op;
}

?>