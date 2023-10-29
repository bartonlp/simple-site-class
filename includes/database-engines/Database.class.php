<?php
/* Well tested and maintained */

define("DATABASE_CLASS_VERSION", "3.0.0database"); // BLP 2023-02-24 -

/**
 * Database wrapper class
 */

class Database extends dbAbstract {
  /**
   * constructor
   * @param $s object. $isSiteClass bool.
   * $s should have all of the $this from SiteClass or $_site from mysitemap.json
   * To just pass in the required database options set $s->dbinfo = (object) $ar
   * where $ar is an assocative array with ["host"=>"localhost",...]
   * $isSiteClass is true if this is from SiteClass.
   */

  protected $hitCount = 0;

  public function __construct(object $s, ?bool $isSiteClass=null) {
    // If we came from SiteClass $isSiteClass is true.
    
    if($isSiteClass !== true) {
      // If we did not come from SiteClass and $s->noTrack does not have a value,
      // then set it to true. Just Database should NOT do tracker (usually).
      
      $s->noTrack = $s->noTrack ?? true; // If not set to false set it to true.
      if($s->noTrack === true) $s->count = false; // BLP 2023-08-11 - Force count false if noTrack is true
    }

    // Do the parent dbAbstract constructor
    
    parent::__construct($s);

    date_default_timezone_set("America/New_York");
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
   * isMyIp($ip):bool
   * Given an IP address check if this is me.
   */

  public function isMyIp(string $ip):bool {
    if($this->isMeFalse === true) return false;
    return (array_intersect([$ip], $this->myIp)[0] === null) ? false : true;
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
   *  it sets $this->foundBotAs
   * These side effects are used by checkIfBot():void see below.
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

    if($this->query("select robots from $this->masterdb.bots where ip='$this->ip'")) { // Is it in the bots table?
      // Yes it is in the bots table.

      $tmp = '';

      // Look at each posible entry in bots. The entries may be for different sites and have
      // different values for $robots. The BOTAS_... are a string while BOTS_... are integers.
      
      while([$robots] = $this->fetchrow('num')) {
        if($robots & BOTS_ROBOTS) {
          $tmp .= "," . BOTAS_ROBOT;
        }
        if($robots & BOTS_SITEMAP) {
          $tmp .= "," . BOTAS_SITEMAP;
        }
        if($robots & BOTS_CRON_ZERO) {
          $tmp .= "," . BOTAS_ZERO;
        }
        if($tmp != '') break;
      }
      
      if($tmp != '') {
        $tmp = ltrim($tmp, ','); // remove the leading comma
        $this->foundBotAs .= "," . $tmp; // BLP 2023-10-23 - foundBotAs could be BOTAS_MATCH
        $this->isBot = true; 
      } 
    }

    /* if(!$this->isMe()) error_log("Database isBot(): ip=$this->ip, foundBotAs=$this->foundBotAs,
    /* robots=$tmp"); */

    if(str_contains($this->foundBotAs, BOTAS_MATCH)) {
      return $this->isBot;
    }

    // The ip was NOT in the bots table either.

    $this->foundBotAs = BOTAS_NOT; // not a bot
    $this->isBot = false;
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

  /**
   * checkIfBot() before we do any of the other protected functions in SiteClass.
   * *** This is ONLY called by the constructor in dbAbstract!
   * Calls the public isBot().
   * Checks if the user-agent looks like a bot or if the ip is in the bots table
   * or previous tracker records had something other than zero or 0x2000.
   * Set $this->isBot true/false.
   * return bool.
   * SEE defines.php for the values for TRACKER_BOT, BOTS_SITECLASS
   * $this-isBot() is false if there is no 'match' or no entry in the bots table
   */

  protected function checkIfBot():bool {
    if($this->isMe()) { // I am never a bot!
      return false; 
    }

    return $this->isBot($this->agent);
  }

  public function __toString() {
    return __CLASS__;
  }
}
