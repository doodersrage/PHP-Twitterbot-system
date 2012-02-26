<?PHP

// display edit user form
function edit_user_levels_frm() {
  global $user_levels;
  
  $user_levels->id = $_GET['id'];
  $user_levels->get();
    
  $op = '<form id="form1" name="form1" method="post" action="?mode=process_user_levels">
  <table border="0" cellspacing="3" cellpadding="3" class="user_tbl" align="center">
	<tr>
	  <th colspan="2">Edit User Level</th>
	</tr>
	<tr>
	  <td>Name:</td>
	  <td><input name="name" type="text" size="20" maxlength="30" value="'.$user_levels->name.'"></td>
	</tr>
	<tr>
	  <td>Description:</td>
	  <td><textarea name="description" cols="30" rows="4">'.$user_levels->description.'</textarea></td>
	</tr>
	<tr>
	  <td>Options:</td>
	  <td>
	  	<table>
			<tr><td>Manage All System Users:</td><td><input name="options[user_access]" type="checkbox" value="1"'.($user_levels->options['user_access'] == 1 ? ' checked ' : '').'></td></tr>
			<tr><td>Manage User Levels:</td><td><input name="options[user_levels]" type="checkbox" value="1"'.($user_levels->options['user_levels'] == 1 ? ' checked ' : '').'></td></tr>
			<tr><td>Manage All Twitter Accounts:</td><td><input name="options[all_twitters]" type="checkbox" value="1"'.($user_levels->options['all_twitters'] == 1 ? ' checked ' : '').'></td></tr>
			<tr><td>Allowed Twitter Accounts: (0 for unlimited)</td><td><input name="options[twitter_accounts]" type="text" value="'.$user_levels->options['twitter_accounts'].'"></td></tr>
			<tr><td>Allowed Twitter Terms: (0 for unlimited)</td><td><input name="options[twitter_terms]" type="text" value="'.$user_levels->options['twitter_terms'].'"></td></tr>
			<tr><td>Allowed Twitter Tweets: (0 for unlimited)</td><td><input name="options[twitter_tweets]" type="text" value="'.$user_levels->options['twitter_tweets'].'"></td></tr>
			<tr><td>Allowed Twitter Post Retries: (0 for unlimited)</td><td><input name="options[twitter_retries]" type="text" value="'.$user_levels->options['twitter_retries'].'"></td></tr>
		</table>		
	</td>
	</tr>
	<tr>
	  <td colspan="2" align="center"><input type="hidden" name="id" value="'.$user_levels->id.'"><input name="Submit" type="submit" value="Submit"></td>
	</tr>
  </table>
  </form>';
 
return $op;
}

?>