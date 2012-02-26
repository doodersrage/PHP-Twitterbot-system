<?PHP

// edit tweet string
function send_tweet_frm() {
  global $tweets;
  			
	$op = '<form id="form1" name="form1" method="post" action="?mode=send_tweet">
	<table border="0" cellspacing="3" cellpadding="3" class="user_tbl" align="center">
	  <tr>
		<th colspan="2">Send tweet</th>
	  </tr>
	  <tr>
		<td>tweet:</td>
		<td><textarea onkeyup="limitChars(\'tweet\', 140, \'charsop\')" name="tweet" id="tweet" cols="50" rows="3">'.$_POST['tweet'].'</textarea></td>
	  </tr>
	  <tr>
		<td>Characters:</td>
		<td id="charsop"></td>
	  </tr>
	  <tr>
		<td colspan="2" align="center"><input name="userid" type="hidden" value="'.$_GET['userid'].'"><input name="Submit" type="submit" value="Submit"></td>
	  </tr>
	</table>
	</form>
  <script language="javascript">
	function limitChars(textid, limit, infodiv) {
	  var text = $(\'#\'+textid).val(); 
	  var textlength = text.length;
	  if(textlength > limit) {
		$(\'#\' + infodiv).html(\'You cannot write more then \'+limit+\' characters!\');
		$(\'#\'+textid).val(text.substr(0,limit));
	  return false;
	  } else {
		$(\'#\' + infodiv).html(\'You have \'+ (limit - textlength) +\' characters left.\');
	  return true;
	  }
	}
  </script>';  
  
return $op;
}

?>