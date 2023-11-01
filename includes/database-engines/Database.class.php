<?php
/* Well tested and maintained */

define("DATABASE_CLASS_VERSION", "3.0.0database"); // BLP 2023-02-24 -

/**
 * Database wrapper class
 */

class SimpleDatabase extends simpledbAbstract {
  /**
   * constructor
   * @param $s object. $isSiteClass bool.
   * $s should have all of the $this from SiteClass or $_site from mysitemap.json
   * To just pass in the required database options set $s->dbinfo = (object) $ar
   * where $ar is an assocative array with ["host"=>"localhost",...]
   * $isSiteClass is true if this is from SiteClass.
   */

  public function __construct(object $s) {
    // Do the parent dbAbstract constructor
    //error_log("Database for simple-site-class");
    parent::__construct($s);

    date_default_timezone_set("America/New_York");
    error_log("Database.class.php ON HP-Envy simple-site-class: site=$this->siteName");
  } // END Construct

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

  /*
   * isBot(string $agent):bool
   * *** This is ONLY called from checkIfBot() in the dbAbstract constructor!
   * *** However, it can be called by applications using $S.
   * Determines if an agent is a bot or not.
   * @return bool
   * Side effects:
   *  it sets $this->isBot
   */
  
  public function isBot(string $agent):bool {
    $this->isBot = false;

    if(($x = preg_match("~\+*https?://|@|bot|spider|scan|HeadlessChrome|python|java|wget|nutch|perl|libwww|lwp-trivial|curl|PHP/|urllib|".
                        "crawler|GT::WWW|Snoopy|MFC_Tear_Sample|HTTP::Lite|PHPCrawl|URI::Fetch|Zend_Http_Client|".
                        "http client|PECL::HTTP~i", $agent)) === 1) { // 1 means a match
      $this->isBot = true;
      $this->foundBotAs = BOTAS_MATCH;
    } elseif($x === false) { // false is error
      // This is an unexplained ERROR
      throw new SqlExceiption(__CLASS__ . " " . __LINE__ . ": preg_match() returned false", $this);
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

    $sql = "insert into $this->masterdb.logagent (site, ip, agent, count, created, lasttime) " .
           "values('$this->siteName', '$this->ip', '$this->agent', '1', now(), now()) ".
           "on duplicate key update count=count+1, lasttime=now()";

    $this->query($sql);
  }

  // ************
  // End Counters
  // ************

  public function __toString() {
    return __CLASS__;
  }
}
