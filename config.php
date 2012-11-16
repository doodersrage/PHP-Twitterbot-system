<?PHP
// disable error reporting
error_reporting(0);

// database connection constants
define('DB_HOST','localhost');
define('DB_NAME','twitter');
define('DB_USERNAME','twitter');
define('DB_PASSWORD','twitter');

define('HOME_PATH',dirname(__FILE__).'/');
define('SITE_DOMAIN','http://twitterbot.macserv.home/');
define('SITE_ADMIN_DOMAIN',SITE_DOMAIN.'admin/');

ini_set("include_path", HOME_PATH.'PEAR');

// disable magic quotes
if (get_magic_quotes_gpc() && !function_exists('stripslashes_deep')) {
    function stripslashes_deep($value)
    {
        $value = is_array($value) ?
                    array_map('stripslashes_deep', $value) :
                    stripslashes($value);

        return $value;
    }

    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

require_once('MDB2.php');
$dsn = array(
    'phptype'  => "mysql",
    'hostspec' => DB_HOST,
    'database' => DB_NAME,
    'username' => DB_USERNAME,
    'password' => DB_PASSWORD
			);

$options = array ( 
	'persistent' => false,
	'result_buffering' => false,
 );

$dbh = MDB2::factory($dsn);
$dbh->setFetchMode(MDB2_FETCHMODE_ASSOC);

if(PEAR::isError($dbh)) {
    die("Error while connecting : " . $dbh->getMessage());
}

// disconnect from database
function db_disconnect() {
	global $dbh, $dsn;

	$dbh->disconnect();
}

// connect to database
function db_connect() {
  global $dbh, $dsn;
  
  $dbh = MDB2::factory($dsn);
  $dbh->setFetchMode(MDB2_FETCHMODE_ASSOC);
  
  if(PEAR::isError($dbh)) {
	  die("Error while connecting : " . $dbh->getMessage());
  }
  
}

function db_check_conn() {
  global $dbh, $dsn;
	
	// if connection is found cycle connection
	if(MDB2::isConnection($dbh)) {
		db_disconnect();
		db_connect();
	// if connection is not found connect to db
	} else {
		db_connect();
	}
	
}

?>