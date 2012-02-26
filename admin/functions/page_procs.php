<?PHP

if($_POST['admin_login'] == 1) {
	$system_users->email = $_POST['email'];
	$system_users->password = $_POST['password'];
	$system_users->login_user();
  if($system_users->id == '') {
	$message = 'The login information provided does not appear to be valid. Please review your submission and try again.';
  }
}

if($_SESSION['user_logged_in'] == 1) {
	  
  // set page value
  switch($_GET['mode']){
	case 'logoff':
	  session_destroy();
	  header("Location: ".SITE_ADMIN_DOMAIN);  
	break;
	case 'create_user':
	case 'edit_user':
	  $output = edit_user_frm();
	break;
	case 'process_user':
	  $twitter_users->username = $_POST['username'];
	  $twitter_users->password = $_POST['password'];
	  $twitter_users->enabled = $_POST['enabled'];
	  $twitter_users->sid = $_SESSION['user_id'];
	  $twitter_users->api_key = $_POST['api_key'];
	  $twitter_users->consumer_key = $_POST['consumer_key'];
	  $twitter_users->consumer_secret = $_POST['consumer_secret'];
	  $twitter_users->oauthtoken = $_POST['oauthtoken'];
	  $twitter_users->oauthsecret = $_POST['oauthsecret'];
	  $twitter_users->id = $_POST['id'];
	  if(!empty($_POST['id'])) {
		$twitter_users->update();
	  } else {
		$twitter_users->post();		
	  }
	  header("Location: ".SITE_ADMIN_DOMAIN);
	break;
	case 'edit_terms':
	  if(!empty($_POST['delete'])) {
		foreach($_POST['delete'] as $sel_id) {
		  execute_qry("DELETE FROM twitter_terms WHERE id = '".$sel_id."' ;");
		}
	  }
	  $output = term_listing();
	break;
	case 'edit_selected_term':
	  $output = edit_term_frm();
	break;
	case 'add_term':
	  $output = edit_term_frm();
	break;
	case 'process_term':
	  $twitter_terms->id = $_POST['id'];
	  $twitter_terms->term = $_POST['term'];
	  $twitter_terms->datestart = $_POST['datestart'];
	  $twitter_terms->dateend = $_POST['dateend'];
	  $twitter_terms->city = $_POST['city'];
	  $twitter_terms->state = $_POST['state'];
	  $twitter_terms->radius = $_POST['radius'];
	  $twitter_terms->max_tweets = $_POST['max_tweets'];
	  $twitter_terms->tweet_count = $_POST['tweet_count'];
	  $twitter_terms->enabled = $_POST['enabled'];
	  $twitter_terms->userid = $_POST['userid'];
	  $twitter_terms->sid = $_SESSION['user_id'];
	  if(!empty($_POST['id'])) {
		$twitter_terms->update();
	  } else {
		$twitter_terms->post();		
	  }
	  header("Location: ".SITE_ADMIN_DOMAIN."?mode=edit_terms&userid=".$_POST['userid']);
   
	break;
	case 'edit_tweets';
	  if(!empty($_POST['delete'])) {
		foreach($_POST['delete'] as $sel_id) {
		  execute_qry("DELETE FROM tweets WHERE id = '".$sel_id."' ;");
		}
	  }
	  $output = tweet_listing();
	break;
	case 'edit_selected_tweet':
	  $output = edit_tweet_frm();
	break;
	case 'new_tweet':
	  $output = edit_tweet_frm();
	break;
	case 'sent_tweets':
	  $output = sent_tweets_listing();
	break;
	case 'process_tweet':
	  $tweets->id = $_POST['id'];
	  $tweets->tweet = $_POST['tweet'];
	  $tweets->userid = $_POST['userid'];
	  $tweets->linked_tem = $_POST['linked_tem'];
	  $tweets->max_uses = $_POST['max_uses'];
	  $tweets->uses = $_POST['uses'];
	  $tweets->sid = $_SESSION['user_id'];
	  if(!empty($_POST['id'])) {
		$tweets->update();
	  } else {
		$tweets->post();		
	  }
	  header("Location: ".SITE_ADMIN_DOMAIN."?mode=edit_tweets&userid=".$_POST['userid']);  
	break;
	case 'view_system_users':
	  if($_SESSION['user_options']['user_access'] == 1){
		if(!empty($_POST['delete'])) {
		  foreach($_POST['delete'] as $sel_id) {
			execute_qry("DELETE FROM system_users WHERE id = '".$sel_id."' ;");
			execute_qry("DELETE FROM twitter_users WHERE sid = '".$sel_id."' ;");
			execute_qry("DELETE FROM tweets WHERE sid = '".$sel_id."' ;");
			execute_qry("DELETE FROM twitter_user_check WHERE sid = '".$sel_id."' ;");
			execute_qry("DELETE FROM twitter_terms WHERE sid = '".$sel_id."' ;");
		  }
		}
		$output = system_user_listing();
	  }
	break;
	case 'add_system_users':
	case 'edit_system_users':
	  if($_SESSION['user_options']['user_access'] == 1){
		$output = edit_system_user_frm();
	  }
	break;
	case 'process_system_user':
	  if($_SESSION['user_options']['user_access'] == 1){
		$system_users->assign_vars();
		if(str_chk($system_users->email,'@') == FALSE) $message = 'You did not enter a valid email address. ';
		if(check_req_fld($system_users->required) == '' && str_chk($system_users->email,'@') != FALSE) {
		  if($system_users->id > 0){
			$system_users->update();
		  } else {
			$system_users->post();
		  }
		  header("Location: ".SITE_ADMIN_DOMAIN."?mode=view_system_users");
		} else {
		  $output = edit_system_user_frm();
		}
	  }
	break;
	case 'view_user_levels':
	  if($_SESSION['user_options']['user_levels'] == 1){
		if(!empty($_POST['delete'])) {
		  foreach($_POST['delete'] as $sel_id) {
			execute_qry("DELETE FROM user_levels WHERE id = '".$sel_id."' ;");
		  }
		}
		$output = user_level_listing();
	  }
	break;
	case 'add_user_levels':
	case 'edit_user_levels':
	  if($_SESSION['user_options']['user_levels'] == 1){
		$output = edit_user_levels_frm();
	  }
	break;
	case 'process_user_levels':
	  if($_SESSION['user_options']['user_levels'] == 1){
		$user_levels->assign_vars();
		if(check_req_fld($user_levels->required) == '') {
		  if($user_levels->id > 0){
			$user_levels->update();
		  } else {
			$user_levels->post();
		  }
		  header("Location: ".SITE_ADMIN_DOMAIN."?mode=view_user_levels");
		} else {
		  $output = edit_user_levels_frm();
		}
	  }
	break;
	case 'edit_user_options':
	  $output = user_options_frm();
	break;
	case 'process_user_options':
	  $system_users->update_options();
	  $output = user_options_frm();
	break;
	case 'send_tweet';
	
	  if(!empty($_POST['userid'])){
		$twitter_users->id = $_POST['userid'];
		$twitter_users->sid = $_SESSION['user_id'];
		$twitter_users->get();

		$bot = new TwitterBot($twitter_users->consumer_key, $twitter_users->consumer_secret, $twitter_users->api_key, $twitter_users->oauthtoken, $twitter_users->oauthsecret);
		$bot->tweetmessage($_POST['tweet']);
		
		$twitter_user_check->username = '';
		$twitter_user_check->tweet = $_POST['tweet'];
		$twitter_user_check->userid = $twitter_users->id;
		$twitter_user_check->sid = $_SESSION['user_id'];
		$twitter_user_check->post();
		
		
		$message = 'Your tweet has been posted to the selected account. Login to twitter or check the tweets logs here to verify.';
		$output = send_tweet_frm();
	  } else {
		$output = send_tweet_frm();
	  }
	  
	break;
	case 'sent_tweets_csv':
	  prnt_tweets_lst_csv();
	break;
	case 'sent_tweets_pdf':
	  prnt_tweets_lst_pdf();
	break;
	case 'retweets':
	  $output = get_retweets();
	break;
	default:
	  // delete selected users
	  if(!empty($_POST['delete'])) {
		foreach($_POST['delete'] as $sel_id) {
		  execute_qry("DELETE FROM twitter_users WHERE id = '".$sel_id."' ;");
		  execute_qry("DELETE FROM tweets WHERE userid = '".$sel_id."' ;");
		  execute_qry("DELETE FROM twitter_user_check WHERE userid = '".$sel_id."' ;");
		  execute_qry("DELETE FROM twitter_terms WHERE userid = '".$sel_id."' ;");
		}
	  }
	  // enable or disable users as selected
	  if(!empty($_POST['cur_users'])) {
		foreach($_POST['cur_users'] as $sel_id) {
		  // set cur user status
		  $user_status = $_POST['user_status'][$sel_id];
		  if($user_status == '') $user_status = 0;
		  execute_qry("UPDATE twitter_users SET enabled = ".$user_status." WHERE id = '".$sel_id."' ;");
		}
	  }
	  $output = user_listing();
	break;
  }
  
} else {
  $output = login_frm();
}

?>