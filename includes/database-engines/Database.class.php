<?php
/* Well tested and maintained */

define("DATABASE_CLASS_VERSION", "1.0.6simple-database-pdo"); // BLP 2024-12-11 - remove finger from sqlite create.

/**
 * Database wrapper class
 */

class SimpleDatabase extends SimpledbPdo {
  /**
   * constructor
   * @param $s object. $isSiteClass bool.
   * $s should have all of the $this from SiteClass or $_site from mysitemap.json
   */

  public function __construct(object $s) {
    // If no 'nodb' or 'dbinfo' (no database) in mysitemap.json set everything so the database is not loaded.
    
    if($s->nodb === true || is_null($s->dbinfo)) {
      $s->nodb = true;    // Maybe $this->dbinfo was null
      $s->dbinfo = null;  // Maybe nodb was set
      return; // If we have NO DATABASE just return.
    }

    // Do the parent SimpledbMysqli constructor
    // Now we can do mysql functions.

    parent::__construct($s);

    // Setting noTrack needs to be set before the class in instantiated. It can be done via mysitemap.json or
    // by setting $_site->noTrack. If noTrack is not true we log.
    // This can also be disabled by setting nodb to true or not including dbinfo in mysitemap.json,
    // but then no database action can happen.

    if($this->noTrack !== true) {
      $this->logagent();
    }

    date_default_timezone_set("America/New_York");
  } // END Constructor

  /**
   * setSiteCookie()
   * @return bool true if OK else false
   * BLP 2021-12-20 -- add $secure, $httponly and $samesite as default
   */

  public function setSiteCookie(string $cookie, string $value, int $expire, string $path="/", ?string $thedomain=null,
                                bool $secure=true, bool $httponly=false, string $samesite='Lax'):bool
  {
    $ref = $thedomain ?? "." . $this->siteDomain; // BLP 2021-10-16 -- added dot back to ref.
    
    $options =  array(
                      'expires' => $expire,
                      'path' => $path,
                      'domain' => $ref, // (defaults to $this->siteDomain with leading period.
                      'secure' => $secure,
                      'httponly' => $httponly,    // If true javascript can't be used (defaults to false.
                      'samesite' => $samesite    // None || Lax  || Strict (defaults to Lax)
                     );

    if(!setcookie($cookie, $value, $options)) {
      error_log("Database $this->siteName: $this->self: setcookie failed ". __LINE__);
      return false;
    }

    //error_log("cookie: $cookie, value: $value, options: " . print_r($options, true));
    return true;
  }

  /**
   * getVersion()
   * @return string version number
   * Because there is no $this in the function we can all it on $S->getVersion or Database::getVersion().
   * When $S is SiteClass this is overloaded with the $S of SiteClass.
   */

  public static function getVersion():string {
    return DATABASE_CLASS_VERSION;
  }
  
  /**
   * getIp()
   * Get the ip address
   * @return int ip address
   */

  public function getIp():string {
    return $this->ip;
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

  /*
   * isBot(string $agent):bool
   * Determines if an agent is a bot or not.
   * @return bool
   * Side effects:
   *  it sets $this->isBot
   */
  
  public function isBot(string $agent):bool {
    $this->isBot = false;

    if(($x = preg_match("~\+*https?://|@|bot|spider|scan|HeadlessChrome|python|java|wget|nutch|perl|libwww|lwp-trivial|curl|PHP/|urllib|".
                        "crawler|GT::WWW|Snoopy|MFC_Tear_Sample|HTTP::Lite|PHPCrawl|URI::Fetch|Zend_Http_Client|".
                        "http client|PECL::HTTP|Go-~i", $agent)) === 1) { // 1 means a match
      $this->isBot = true;
      $this->foundBotAs = BOTAS_MATCH;
    } elseif($x === false) { // false is error
      // This is an unexplained ERROR
      throw new SimpleSqlExceiption(__CLASS__ . " " . __LINE__ . ": preg_match() returned false", $this);
    }
    return $this->isBot;
  }

  // ********************************************************************************
  // Private and protected methods.
  // Protected methods can be overridden in child classes so most things that would be private
  // should be protected in this base class

  // **************
  // Start Counters
  // **************

  /**
   * logagent()
   * Log logagent
   * This counts everyone!
   * logagent is used by 'analysis.php'
   */
  
  protected function logagent():void {
    // site, ip and agent(256) are the primary key. Note, agent is a text field so we look at the
    // first 256 characters here (I don't think this will make any difference).

    if($this->dbinfo->engine == "mysql") {
       $sql = "insert into $this->masterdb.logagent (site, ip, agent, count, created, lasttime) " .
              "values('$this->siteName', '$this->ip', '$this->agent', '1', now(), now()) ".
              "on duplicate key update count=count+1, lasttime=now()";

       $this->sql($sql);
    } else {
      if($this->dbinfo->engine == "sqlite") {
        $this->sql("create table if not exists logagent (`site` varchar(25) NOT NULL DEFAULT '',".
                   "`ip` varchar(40) NOT NULL DEFAULT '',".
                   "`agent` varchar(254) NOT NULL,".
                   "`count` int DEFAULT NULL,".
                   "`created` datetime DEFAULT NULL,".
                   "`lasttime` datetime DEFAULT NULL,".
                   "PRIMARY KEY (`site`,`ip`,`agent`))");

        $sql = "insert into logagent (site, ip, agent, count, created, lasttime) " .
               "values('$this->siteName', '$this->ip', '$this->agent', '1', datetime('now'), datetime('now'))";
      } else { // pgsql
        $sql = "insert into logagent (site, ip, agent, count, created, lasttime) " .
               "values('$this->siteName', '$this->ip', '$this->agent', '1', now(), now())";
      }
      try {
        $this->sql($sql);
      } catch(Exception $e) {
        if($e->getCode() == 23000 || $e->getCode() == 23505) { // duplicate key
          if($this->dbinfo->engine == "sqlite") {
            $d = "datetime('now')";
          } else { // pgsql
            $d = "now()";
          }
          $this->sql("update logagent set count=count+1, lasttime=$d where site='$this->siteName' and ip='$this->ip' and agent='$this->agent'");
        } else {
          //echo "Database code1: {$e->getCode()}<br>";
          throw new Exception($e);
        }
      }
    }
  }

  // ************
  // End Counters
  // ************

  public function __toString() {
    return __CLASS__;
  }
}

