<?PHP

// display edit user form
function edit_system_user_frm() {
  global $system_users;
  
  $system_users->id = $_GET['id'];
  $system_users->get();
  
  $user_levels = array(
					   1,
					   2,
					   3,
					   4
					   );
  
  $user_lvl_sel = '';
  foreach($user_levels as $cur_ul){
	  $user_lvl_sel .= '<option value="'.$cur_ul.'"'.($system_users->user_level == $cur_ul ? ' selected ' : '').'>'.$cur_ul.'</option>';
  }
  
  $op = '<form id="form1" name="form1" method="post" action="?mode=process_system_user">
  <table border="0" cellspacing="3" cellpadding="3" class="user_tbl" align="center">
	<tr>
	  <th colspan="2">Edit User Account</th>
	</tr>
	<tr>
	  <td>Email:</td>
	  <td><input name="email" type="text" size="20" maxlength="30" value="'.$system_users->email.'"></td>
	</tr>
	<tr>
	  <td>Password:</td>
	  <td><input name="password" type="text" size="20" maxlength="30" value="'.($system_users->id == '' ? $system_users->password : '').'"></td>
	</tr>
	<tr>
	  <td>User Level:</td>
	  <td>
	  <select name="user_level">
	  '.usr_lvls_dd($system_users->user_level).'
	  </select>
	  </td>
	</tr>
	<tr>
	  <td colspan="2" align="center"><input type="hidden" name="id" value="'.$system_users->id.'"><input name="Submit" type="submit" value="Submit"></td>
	</tr>
  </table>
  </form>';
 
return $op;
}

?>