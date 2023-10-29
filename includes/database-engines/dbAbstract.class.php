<?php
/* MAINTAINED and WELL TESTED */

define("ABSTRACT_CLASS_VERSION", "3.0.1ab"); // BLP 2023-03-07 - remove $arg use $dbinfo. Pass $dbinfo items to dbMysqli

// Abstract database class
// Most of this class is implemented here. This keeps us from having to duplicate this over and
// over again in each higher level class like SiteClass or Database.
// The db engines (dbMysqli.class.php, etc.) have most of these methods implemented.

abstract class dbAbstract {
  protected $db;
  
  /*
   * constructor.
   * @param: object $s. This usually has the info from mysitemap.json.
   */
  
  protected function __construct(object $s) {
    $this->errorClass = new ErrorClass();

    // If we have $s items use them otherwise get the defaults

    $s->ip = $s->ip ?? $_SERVER['REMOTE_ADDR']; // BLP 2023-01-18 - Added for NODE with php view.
    $s->agent = $s->agent ?? $_SERVER['HTTP_USER_AGENT']; // BLP 2022-01-28 -- CLI agent is NULL and $__info[1] wil be null
    $s->self = $s->self ?? htmlentities($_SERVER['PHP_SELF']);
    $s->requestUri = $s->requestUri ?? $_SERVER['REQUEST_URI'];

    // Put all of the $s values into $this.
    
    foreach($s as $k=>$v) {
      $this->$k = $v;
    }
    
    // If no 'dbinfo' (no database) in mysitemap.json set everything so the database is not loaded.
    
    if($this->nodb === true || is_null($this->dbinfo)) {
      $this->nodb = true;    // Maybe $this->dbinfo was null
      $this->dbinfo = null;  // Maybe nodb was set
      return; // If we have NO DATABASE just return.
    }

    $db = null;
    $dbinfo = $this->dbinfo;

    if(isset($dbinfo->engine) === false) {
      $this->errno = -2;
      $this->error = "'engine' not defined";
      throw new SqlException(__METHOD__, $this);
    }

    // BLP 2023-01-26 - currently there is only ONE viable engine and that is dbMysqli
    
    $class = "db" . ucfirst(strtolower($dbinfo->engine));
    
    if(class_exists($class)) {
      $db = new $class($dbinfo);
    } else {
      throw new SqlException(__METHOD__ . ": Class Not Found : $class<br>", $this);
    }

    if(is_null($db) || $db === false) {
      throw new SqlException(__METHOD__ . ": Connect failed", $this);
    }
    
    $this->db = $db;

    // Escapte the agent in case it has something like an apostraphy in it.
    
    $this->agent = $this->escape($this->agent);
  }

  // Each child class needs to have a __toString() method

  abstract public function __toString() ;
    
  public static function getAbstractName() {
    return __CLASS__;
  }
  
  public static function getVersion() {
    return ABSTRACT_CLASS_VERSION;
  }

  /**
   * getDbName()
   * This is the name of the database, like 'bartonphillips' or 'barton'
   */
  
  public function getDbName():string {
    $database = $this->db->database;
    if($database) {
      return $database;
    }
    return $this->db->db->database;
  }

  public function getDb() {
    return $this->db;
  }

  public function setDb($db) {
    $this->db = $db;
  }

  public function getDbError() {
    return $this->db->error;
  }

  public function getDbErrno() {
    return $this->db->errno;
  }
  
  // The following methods either execute or if the method is not defined throw an Exception
  
  public function query($query) {
    if(method_exists($this->db, 'query')) {
      return $this->db->query($query);
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }
  
  public function fetchrow($result=null, $type="both") {
    if(method_exists($this->db, 'fetchrow')) {
      return $this->db->fetchrow($result, $type);
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }

  public function finalize($result) {
    if(method_exists($this->db, 'finalize')) {
      return $this->db->finalize($result);
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }

  public function queryfetch($query, $type=null, $retarray=false) {
    if(method_exists($this->db, 'queryfetch')) {
      return $this->db->queryfetch($query, $type, $retarray);
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }

  public function getLastInsertId() {
    if(method_exists($this->db, 'getLastInsertId')) {
      return $this->db->getLastInsertId();      
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }

  public function getResult() {
    if(method_exists($this->db, 'getResult')) {
      return $this->db->getResult();
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }
    
  public function escape($string) {
    if(method_exists($this->db, 'escape')) {
      return $this->db->escape($string);
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }
  
  public function escapeDeep($value) {
    if(method_exists($this->db, 'escapeDeep')) {
      return $this->db->escapeDeep($value);
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }
  
  public function getNumRows($result=null) {
    if(method_exists($this->db, 'getNumRows')) {
      return $this->db->getNumRows($result);
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }
  
  public function prepare($query) {
    if(method_exists($this->db, 'prepare')) {
      return $this->db->prepare($query);
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }
  
  public function bindParam($format) {
    if(method_exists($this->db, 'bindParam')) {
      return $this->db->bindParam($format);
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }
  
  public function bindResults($format) {
    if(method_exists($this->db, 'bindResults')) {
      return $this->db->bindResults($format);
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }

  public function execute() {
    if(method_exists($this->db, 'execute')) {
      return $this->db->execute();
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }

  public function getErrorInfo() {
    if(method_exists($this->db, 'getErrorInfo')) {
      return $this->db->getErrorInfo();
    } else {
      throw new Exception(__METHOD__ . " not implemented");
    }
  }

  /*
   * debug()
   * @param $exit. If true throw and exception. Else just output via error_log().
   * If noErrorLog is set in mysitemap.json then don't do error_log()
   */

  protected function debug(string $msg, $exit=false):void {
    if($this->noErrorLog === true) {
      if($exit === true) {
        throw new SqlException($msg, $this);
      }
      return;
    }

    error_log("debug:: $msg");

    if($exit === true) {
      throw new SqlException($msg, $this);
    }
  }
}
