<?php
/* MAINTAINED and WELL TESTED. This is the default Database and has received extensive testing */
/**
 * dbPdo Class
 *
 * Wrapper around PDO Database Class. 
 * @package dbPod
 * @author Barton Phillips <barton@bartonphillips.com>
 * @link http://www.bartonphillips.com
 * @copyright Copyright (c) 2010, Barton Phillips
 * @license http://opensource.org/licenses/gpl-3.0.html GPL Version 3
 */
// BLP 2024-04-20 - set mysql time zone.

use SendGrid\Mail\Mail; // Use SendGrid for email

define("PDO_CLASS_VERSION", "1.0.7simple-pdo"); // BLP 2024-11-15 - add isMe() and isMyip(). Add myip, move defines.php from Database and $s.
require_once(__DIR__ . "/../defines.php"); // This has the constants for TRACKER, BOTS, BOTS2, and BEACON

/**
 * @package PDO Database
 * This is the base class for Database. SiteClass extends Database.
 * This class can also be used standalone. $siteInfo must have a dbinfo with host, user, database and optionally port.
 * The password is optional and if not pressent is picked up form my $HOME.
 */

class SimpledbPdo extends PDO {
  public $myIp = [MY_IP]; // BLP 2024-11-07 - meeded by some programs, get it from defines.php
  private $result; // for select etc. a result set.
  static public $lastQuery = null; // for debugging
  static public $lastNonSelectResult = null; // for insert, update etc.

  /**
   * Constructor
   * @param object $siteInfo. Has the mysitemap.json info
   * as a side effect opens the database, that is connects the database
   */

  public function __construct(object $s) {
    set_exception_handler("SimpledbPdo::my_exceptionhandler"); // Set up the exception handler

    // BLP 2021-03-06 -- New server is in New York

    date_default_timezone_set('America/New_York');

    // BLP 2023-10-02 - ask for sec headers
    
    header("Accept-CH: Sec-Ch-Ua-Platform,Sec-Ch-Ua-Platform-Version,Sec-CH-UA-Full-Version-List,Sec-CH-UA-Arch,Sec-CH-UA-Model"); 

    // If we have $s items use them otherwise get the defaults
    // BLP 2024-11-07 - setting the default was moved from Database.
    
    $s->ip = $_SERVER['REMOTE_ADDR'];

    $s->agent = $s->agent ?? $_SERVER['HTTP_USER_AGENT'];
    $s->agent = preg_replace("~'~", "", $s->agent); // BLP 2024-10-29 - remove appostrophies.

    $s->agent = $_SERVER['HTTP_USER_AGENT']; 
    $s->self = htmlentities($_SERVER['PHP_SELF']);
    $s->requestUri = $_SERVER['REQUEST_URI'];

    // Extract the items from dbinfo. This is $host, $user and maybe $password and $port.

    foreach($s as $k=>$v) {
      $this->$k = $v;
    }

    extract((array)$this->dbinfo); // Cast the $dbinfo object into an array
      
    // $password is almost never present, but it can be under some conditions.
    
    $password = $password ?? require("/home/barton/database-password");

    // BLP 2024-11-07 - Note, Sqlite3 does not support set time_zone. All times are UTC.
    
    if($engine == "sqlite") {
      parent::__construct("$engine:$database");
    } else {
      parent::__construct("$engine:dbname=$database; host=$host; user=$user; password=$password");
      $this->sql("set time_zone='America/New_York'"); 
    }
    $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $this->database = $database;
  } // End of constructor.

  /*
   * getVersion.
   * @return the version of the Pdo class.
   */
  
  public static function getVersion() {
    return PDO_CLASS_VERSION;
  }

  /*
   * getDbErrno
   * @returns $this->errno from PDO.
   */
  
  public function getDbErrno() {
    return $this->errno;
  }

  /*
   * getDbError
   * @returns $this->error from PDO
   */
  
  public function getDbError() {
    return $this->error;
  }

   /**
   * isMyIp($ip):bool
   * Given an IP address check if this is me.
   */

  public function isMyIp(string $ip):bool {
    if($this->isMeFalse === true) return false;
    return (in_array($ip, $this->myIp));
  }
  
  /**
   * isMe()
   * Check if this access is from ME
   * @return true if $this->ip == $this->myIp else false!
   */

  public function isMe():bool {
    return $this->isMyIp($this->ip);
  }

  /**
   * sql()
   * Query database table
   * BLP 2016-11-20 -- Query is for a SINGLE query ONLY. Don't do multiple querys!
   * @param string $query SQL statement.
   * @return: if $result === true returns the number of affected_rows (delete, insert, etc). Else ruturns num_rows.
   * if $result === false throws Exception().
   */

  public function sql($query) {
    self::$lastQuery = $query; // for debugging

    $m = null;
    preg_match("~^(\w+).*$~", $query, $m);
    $m = $m[1];

    //echo "m=$m<br>";

    if($m == 'insert' || $m == 'delete' || $m == 'update') {
      try {
        $numrows = $this->exec($query);
      } catch(Exception $e) {
        //error_log("dbPdo.class.php: $query\n\$e=$e");
        throw $e;
      }
      if($numrows === false) {
        throw new Exception($query);
      }
    } else { // could be select, create, etc.
      $result = $this->query($query);

      // If $result is false then exit

      if($result === false) {
        throw new Exception($query);
      }

      $this->result = $result;

      if($this->dbinfo->engine == 'mysql' || $this->dbinfo->engine == 'pgsql') {
        $numrows = $result->rowCount();
      } elseif($m == 'select') {
        $last = self::$lastQuery;
        $last = preg_replace("~^(select) .*?(from .*)$~", "$1 count(*) $2", $last);
        $stm = $this->query($last);
        $numrows = $stm->fetchColumn();
        //echo "numrows=$numrows<br>";
      } else $numrows = 0;
    }
    return $numrows;
  }
  
  /**
   * sqlPrepare()
   * PDO::prepare()
   * used as follows:
   * 1) $username="bob"; $query = "select one, two from test where name=?";
   * 2) $stm = PDO::prepare($query);
   * 3) $stm->bind_param("s", $username);
   * 4) $stm->execute();
   * 5) $stm->bind_result($one, $two);
   * 6) $stm->fetch();
   * 7) echo "one=$one, two=$two<br>";
   * BLP 2021-12-11 -- NOTE: we do not have a bind_param(), execute(), bind_result() or fetch() functions in this module.
   * You will have to use the native PHP functions with the returned $stm.
   */
  
  public function sqlPrepare($query) {
    $stm = $this->prepare($query);
    return $stm;
  }

  /**
   * queryfetch()
   * Dose a query and then fetches the associated rows
   * @param string, the query
   * @param string|null, $type can be 'num', 'assoc', 'obj' or 'both'. If null then $type='both'
   * @param bool|null, if null then false.
   *   if param1, param2=bool then $type='both' and $returnarray=param2
   * @return:
   *   1) if $returnarray is false returns the rows array.
   *   2) if $returnarray is true returns an array('rows'=>$rows, 'numrows'=>$numrows).
   * NOTE the $query must be a 'select' that returns a result set. It can't be 'insert', 'delete', etc.
   */
  
  public function queryfetch($query, $type=null, $returnarray=null) {
    if(stripos($query, 'select') === false) { // Can't be anything but 'select'
      throw new Exception($query, $this);
    }

    // queryfetch() can be
    // 1) queryfetch(param1) only 1 param in which case $type is set to
    // 'both'.
    // 2) queryfetch(param1, param2) where param2 is a string like 'assoc', 'num', 'obj' or 'both'
    // 3) queryfetch(param1, param2) where param2 is a boolian in which case $type is set to
    // 'both' and $returnarray is set to the boolian value of param2.
    // 4) queryfetch(param1, param2, param3) where the param values set the corisponding values.

    if(is_null($type)) {
      $type = 'both';
    } elseif(is_bool($type) && is_null($returnarray)) {
      $returnarray = $type;
      $type = 'both';
    }  
    
    $numrows = $this->query($query);

    while($row = $this->fetchrow($type)) {
      $rows[] = $row;
    }

    return ($returnarray) ? ['rows'=>$rows, 'numrows'=>$numrows] : $rows;
  }

  /**
   * fetchrow()
   * @param resource identifier returned from query.
   * @param string, type of fetch: assoc==associative array, num==numerical array, obj==object, or both (for num and assoc).
   * @return array, either assoc or numeric, or both
   * NOTE: if $result is a string then $result is the $type and we use $this->result for result.
   */
  
  public function fetchrow($result=null, $type="both") {
    if(is_string($result)) { // a string like num, assoc, obj or both
      $type = $result;
      $result = $this->result;
    }
    
    if(!$result) {
      throw new Exception(__METHOD__ . ": result is null");
    }

    switch($type) {
      case "assoc": // associative array
        $row = $result->fetch(PDO::FETCH_ASSOC);
        break;
      case "num":  // numerical array
        $row = $result->fetch(PDO::FETCH_NUM);
        break;
      case "obj": // object BLP 2021-12-11 -- added
        $row = $result->fetch(PDO::FETCH_OBJ);
        break;
      case "both":
      default:
        $row = $result->fetch(PDO::FETCH_BOTH); // This is the default
        break;
    }
    //error_log("dbPdo: fetchrow, query=" . self::$lastQuery . ", row=" . var_export($row, true));
    return $row;
  }
  
  /**
   * getLastInsertId()
   * See the comments below. The bottom line is we should NEVER do multiple inserts
   * with a single insert command! You just can't tell what the insert id is. If we need to do
   * and 'insert ... on duplicate key' we better not need the insert id. If we do we should do
   * an insert in a try block and an update in a catch. That way if the insert succeeds we can
   * do the getLastInsertId() after the insert. If the insert fails for a duplicate key we do the
   * update in the catch. And if we need the id we can do a select to get it (somehow).
   * Note if the insert fails because we did a 'insert ignore ...' then last_id is zero and we return
   * zero.
   * @return the last insert id if this is done in the right order! Otherwise who knows.
   */

  public function getLastInsertId() {
    return $this->lastInsertId();
  }
  
  /**
   * getNumRows()
   */

  public function getNumRows($result=null) {
    if(!$result) $result = $this->result;
    if($result === true) {
      return $this->affected_rows;
    } else {
      return $result->num_rows;
    }
  }

  /**
   * getResult()
   * This is the result of the most current query. This can be passed to
   * fetchrow() as the first parameter.
   */
  
  public function getResult() {
    return $this->result;
  }

  /**
   * getErrorInfo()
   * get the error info from the most recent query
   */
  
  public function getErrorInfo() {
    return ['errno'=>$this->getDbErrno(), 'error'=>$this->getDbError()];
  }
  
  // real_escape_string
  
  public function escape($string) {
    return $this->quote($string);
  }

  public function escapeDeep($value) {
    if(is_array($value)) {
      foreach($value as $k=>$v) {
        $val[$k] = $this->escapeDeep($v);
      }
      return $val;
    } else {
      return $this->escape($value);
    }
  }

  public function __toString() {
    return __CLASS__;
  }

  /*
   * my_exceptionhandler
   * Must be a static
   * BLP 2024-07-07 - Uses New SendGrid version for email
   */

  public static function my_exceptionhandler($e) {
    $from =  get_class($e);

    $error = $e; // get the full error message

    // Remove all html tags.

    $err = html_entity_decode(preg_replace("/<.*?>/", '', $error));
    $err = preg_replace("/^\s*$/", '', $err); // remove blank lines

    // Callback to get the user ID if the callback exists

    $userId = '';

    if(function_exists('ErrorGetId')) {
      $userId = "User: " . ErrorGetId();
    }

    if(!$userId) $userId = "agent: ". $_SERVER['HTTP_USER_AGENT'] . "\n";

    /* BLP 2024-07-01 - NEW VERSION using sendgrid */

    if(SimpleErrorClass::getNoEmail() !== true) {
      $s = $GLOBALS["_site"];

      $email = new Mail();

      $email->setFrom("ErrorMessage@bartonphillips.com");
      $email->setSubject($from);
      $email->addTo($s->EMAILADDRESS);
  
      $email->addContent("text/plain", 'View this in HTML mode');

      $contents = preg_replace(["~\"~", "~\\n~"], ['','<br>'], "$err<br>$userId");

      $email->addContent("text/html", $contents);

      $apiKey = require "/var/www/PASSWORDS/sendgrid-api-key";
  
      $sendgrid = new \SendGrid($apiKey);

      $response = $sendgrid->send($email);

      if($response->statusCode() > 299) {
        error_log("dbPod sendgrid error: $response->statusCode(), response header: " . print_r($response->headers()));
      }
    }

    /* BLP 2024-07-01 - END NEW VERSION */
    
    // Log the raw error info.
    // This error_log should always stay in!! *****************
    error_log("dbPdo.class.php: $from\n$err\n$userId");
    // ********************************************************

    if(SimpleErrorClass::getDevelopment() !== true) {
      // Minimal error message
      $error = <<<EOF
<p>The webmaster has been notified of this error and it should be fixed shortly. Please try again in
a couple of hours.</p>

EOF;
      $err = " The webmaster has been notified of this error and it should be fixed shortly." .
      " Please try again in a couple of hours.";
    }

    if(SimpleErrorClass::getNoHtml() === true) {
      $error = "$from: $err";
    } else {
      $error = <<<EOF
<div style="text-align: center; background-color: white; border: 1px solid black; width: 85%; margin: auto auto; padding: 10px;">
<h1 style="color: red">$from</h1>
$error
</div>
EOF;
    }

    if(SimpleErrorClass::getNoOutput() !== true) {
      //************************
      // Don't remove this echo
      echo $error; // BLP 2022-01-28 -- on CLI this outputs to the console, on apache it goes to the client screen.
      //***********************
    }
    return;
  }

  /*
   * debug
   * Displays $msg
   * if $exit is true throw an exception.
   * else error_log and return.
   */
  
  public function debug($msg, $exit=null) {
    if($exit === true) {
      throw new Exception($msg);
    } else {
      error_log("dbPdo.class.php Error: $msg");
      return;
    }
  }
}

