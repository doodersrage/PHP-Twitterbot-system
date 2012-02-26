<?PHP

class tweetmanage {
  public $userid;
  public $twitter_user;
  public $twitter_pass;
  public $api_key;
  public $consumer_key;
  public $consumer_secret;
  public $oauthtoken;
  public $oauthsecret;
  public $twitter_search_word;
  public $twitter_search_word_id;
  public $tweet;
  public $tweet_id;
  public $term_id;
  public $username;
  public $city;
  public $state;
  public $radius;
  public $sid;
  private $max_retries = 3;
  private $cur_retry = 0;
  
  // gathers random tweet
  function get_tweet() {
	global $tweets,$system_users;
	
	$tweets->sid = $system_users->id;
	$tweets->userid = $this->userid;
	$tweets->linked_tem = $this->twitter_search_word_id;
	$tweets->get_random();
	
	$this->tweet_id = $tweets->id;
	$this->tweet = $tweets->tweet;

  }
  
  // checks for existing tweet to twitter user
  function username_check() {
	global $dbh;
	
	$sql_query = "SELECT 
					count(*) as cnt
				FROM 
					twitter_user_check
				WHERE 
					username = ?
				AND
					userid = ?
				AND
					sid = ? ;";

	$values = array(
					$this->username,
					$this->userid,
					$this->sid,
					);
	
	$stmt = $dbh->prepare($sql_query);					 
	$result = $stmt->execute($values);
	
	$row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
	
	$found = $row['cnt'];

	// clear result set
	$result->free();
	
	// reset DB conn
	db_check_conn();

  return $found;
  }
  
  // gathers username from twitter and updates stored twitter data
  function username_insert() {
	global $twitter_user_check, $twitter_terms, $tweets;
	
	// added to retrieve user image
	$bot = new Twitter($this->consumer_key, $this->consumer_secret);
	//set Access Token
	$bot->setOAuthToken($this->oauthtoken);
	//set Access Token Secret
	$bot->setOAuthTokenSecret($this->oauthsecret);
	$profile_image = $bot->usersProfileImage($this->username);
	
	$twitter_user_check->username = $this->username;
	$twitter_user_check->tweet_id = $this->tweet_id;
	$twitter_user_check->tweet = $this->tweet;
	$twitter_user_check->profile_image = $profile_image;
	$twitter_user_check->userid = $this->userid;
	$twitter_user_check->sid = $this->sid;
	$twitter_user_check->post();
	
	// update twitter count
	$twitter_terms->id = $this->term_id;
	$twitter_terms->userid = $this->userid;
	$twitter_terms->sid = $this->sid;
	$twitter_terms->update_count();
	
	// update tweet use count
	$tweets->id = $this->tweet_id;
	$tweets->userid = $this->userid;
	$tweets->sid = $this->sid;
	$tweets->update_count();
	
  }
  
  // post tweet to twitter
  function post_tweet() {
	 global $bot;
	
	$bot = new TwitterBot($this->consumer_key, $this->consumer_secret, $this->api_key, $this->oauthtoken, $this->oauthsecret);
	
	$bot->city = $this->city;
	$bot->state = $this->state;
	$bot->radius = $this->radius;
	
	$this->username = $bot->searchAndReturnUsername($this->twitter_search_word);
	
	if($this->username_check($this->username) < 1) {
		$this->get_tweet();
		$message = '@'.$this->username.' '.$this->tweet;
		$bot->tweetmessage($message);
		$this->gather_rts();
		$this->username_insert();
	} elseif($this->max_retries <= $this->cur_retry) {
	  $this->cur_retry++;
	  $this->post_tweet();
	} else {
	  $this->cur_retry = 0;
	}
	  
  }
  
  // gathers restweets assigned to the user
  function gather_rts(){
	 global $retweets;
	$bot = new Twitter($this->consumer_key, $this->consumer_secret);
	//set Access Token
	$bot->setOAuthToken($this->oauthtoken);
	//set Access Token Secret
	$bot->setOAuthTokenSecret($this->oauthsecret);
	$found_retweets = $bot->statusesMentions();
	 foreach($found_retweets as $tweet_info){
		$tweet_data = serialize($tweet_info);
		// check for existing tweet and if found stop checking others (tweets are read in descending order)
		$retweets->reset_vars();
		$retweets->id = (int)$tweet_info['id_str'];
		$retweets->get();
		if($retweets->screen_name == ''){
			$retweets->id = (int)$tweet_info['id_str'];
			$retweets->screen_name = $tweet_info['user']['screen_name'];
			$retweets->profile_image_url = $tweet_info['user']['profile_image_url'];
			$retweets->url = $tweet_info['user']['url'];
			$retweets->friends_count = $tweet_info['user']['friends_count'];
			$retweets->followers_count = $tweet_info['user']['followers_count'];
			$retweets->favourites_count = $tweet_info['user']['favourites_count'];
			$retweets->listed_count = $tweet_info['user']['listed_count'];
			$retweets->name = $tweet_info['user']['name'];
			$retweets->data = $tweet_data;
			$retweets->userid = $this->userid;
			$retweets->sid = $this->sid;
			$retweets->rt_date = date('Y-m-d H:i:s',strtotime($tweet_info['created_at']));
			$retweets->post();
		} else {
			break;
		}
	 }
  }
  
}

?>