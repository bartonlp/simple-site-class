<?php
// SITE_CLASS_VERSION must change when the GitHub Release version changes.
// BLP 2023-10-02 - Note that the constructor calls the Database constructor which in turn call the
// dbMysqli constructor which does all of the heavy lifting.

define("SITE_CLASS_VERSION", "1.0.2simple-pdo"); 

// One class for all my sites
// This version has been generalized to not have anything about my sites in it!
/**
 * SiteClass
 *
 * @package SiteClass
 * @author Barton Phillips <barton@bartonphillips.com>
 * @link http://www.bartonphillips.com
 * @copyright Copyright (c) 2022, Barton Phillips
 * @license  MIT
 */

/**
 * @package SiteClass
 * This class can be extended to handle special issues and add methods.
 */

// BLP 2023-09-08 - make an alias for getinfo.

use \bartonlp\siteload\getinfo as load;

class SimpleSiteClass extends SimpleDatabase {
  // Give these default values incase they are not mentioned in mysitemap.json.
  // Note they could still be null from mysitemap.json!

  public $count = true;
  public $doctype = "<!DOCTYPE html>";

  /**
   * Constructor
   *
   * @param object $s. If this is not an object we get an error!
   *  The $s is almost always from mysitemap.json.
   *  Once in a while they can be changed by the program instantiating the class.
   *  'count' is default true.
   *  $s has the values from $_site = require_once(getenv("SITELOADNAME"));
   *  which uses siteload.php to gets values from mysitemap.json.
   */
  
  public function __construct(object $s) {
    // Do the parent Database constructor which does the dbMysqli constructor.
    
    parent::__construct($s); 

    // BLP 2018-07-01 -- Add the date to the copyright notice if one exists

    if($this->copyright) {
      $this->copyright = date("Y") . " $this->copyright";
    }
  } // End of constructor.

  /**
   * getVersion()
   * @return string version number
   */

  public static function getVersion():string {
    return SITE_CLASS_VERSION;
  }

  /**
   * getHitCount()
   */

  public function getHitCount():int {
    return $this->hitCount;
  }

  /**
   * getDoctype()
   * Returns the CURRENT DocType used by this program
   */

  public function getDoctype():string {
    return $this->doctype;
  }

  /**
   * getPageTopBottom()
   * Get Page Top (<head> and <header> ie banner) and Footer
   * @return array top, footer
   */

  public function getPageTopBottom():array {
    // Do getPageTop and getPageFooter

    $top = $this->getPageTop();

    // BLP 2022-04-09 - We can pass in a footer via $h.
    
    $footer = $this->footer ?? $this->getPageFooter();

    // return the array which we usually get via '[$top, $footer] = $S->getPageTopBottom($h, $b)'

    return [$top, $footer];
  }

  /**
   * getPageTop()
   * Get Page Top
   * Gets both the page <head> and <header> sections
   * @return string with the <head>  and <header> (ie banner) sections
   */
  
  public function getPageTop():string {
    // Get the page <head> section

    $head = $this->getPageHead();

    // Get the page's banner section (<header>...</header>)
    
    $banner = $this->getPageBanner();

    return "$head\n$banner";
  }

  /**
   * getPageHead()
   * Get the page <head></head> stuff including the doctype etc.
   * @return string $pageHead
   */

  public function getPageHead():string {
    $this->getPageHead = true; // BLP 2023-01-31 -
    
    $h = new stdClass;

    // use either $h or $this values or a constant

    $dtype = $this->doctype; // note that $this->doctype could also be from mysitemap.json see the constructor.

    $h->base = $this->base ? "<base href='$this->base'>" : null;

    // All meta tags

    $h->title = $this->title ? "<title>$this->title</title>" : null;
    $h->desc = $this->desc ? "<meta name='description' content='$this->desc'>" : null;
    $h->keywords = $this->keywords ? "<meta name='keywords' content='$this->keywords'>" : null;
    $h->copyright = $this->copyright ? "<meta name='copyright' content='$this->copyright'>" : null;
    $h->author = $this->author ? "<meta name='author' content='$this->author'>" : null;
    $h->charset = $this->charset ? "<meta charset='$this->charset'>" : "<meta charset='utf-8'>";
    $h->viewport = $this->viewport ? "<meta name='viewport' content='$this->viewport'>" :
                   "<meta name='viewport' content='width=device-width, initial-scale=1'>";
    $h->canonical = $this->canonical ? "<link rel='canonical' href='$this->canonical'>" : null;
    $h->meta = $this->meta; // BLP 2023-10-29 - full value <meta ...>
    
    // link tags
    
    $h->favicon = $this->favicon ? "<link rel='shortcut icon' href='$this->favicon'>" :
                  "<link rel='shortcut icon' href='https://bartonphillips.net/images/favicon.ico'>";

    if($this->defaultCss === false) { // If this is false NO default
      $h->defaultCss = null;
    } else { // Else either add the value or the default.
      $h->defaultCss = $this->defaultCss ? "<link rel='stylesheet' href='$this->defaultCss' title='default'>" :
                       "<link rel='stylesheet' href='https://bartonphillips.net/css/blp.css' title='default'>";
    }
    
    // $h->css is a special case. If the style is not already there incase the text in <style> tags.

    $h->css = $this->css;
    
    if($this->css && preg_match("~<style~", $this->css) == 0) {
      $h->css = "<style>\n$this->css\n</style>";
    }

    // We set the $h->inlineScript here with h_inlineScript
    
    $h->inlineScript = $this->h_inlineScript ? "<script>\n$this->h_inlineScript\n</script>" : null;
    
    // The rest, $h->link, $h->script and $h->extra need the full '<link' or '<script' text.

    $h->script = $this->h_script;
    $h->link = $this->link;
    $h->extra = $this->extra;
    
    $preheadcomment = $this->preheadcomment; // Must be a real html comment ie <!-- ... -->
    $lang = $this->lang ?? 'en';
    $htmlextra = $this->htmlextra; // Must be full html
    
    if($this->nojquery !== true) {
      $logoImgLocation = $this->logoImgLocation ?? "https://bartonphillips.net"; // BLP 2023-08-08 -
      $headerImg2Location = $this->headerImg2Location ?? $logoImgLocation ?? "https://bartonphillips.net"; // BLP 2023-08-10 -

      // The trackerImg... can start with http or https. If so use the full url.

      if(strpos($this->trackerImg1, "http") === 0) {
        $desktopImg = $this->trackerImg1;
      } else {
        $desktopImg = $this->trackerImg1 ? "$logoImgLocation/$this->trackerImg1" : null; // BLP 2023-08-08 -
      }
      if(strpos($this->trackerImgPhone, "http") === 0) {
        $phoneImg = $this->trackerImgPhone;
      } else {
        $phoneImg = $this->trackerImgPhone ? "$logoImgLocation/$this->trackerImgPhone" : null; // BLP 2023-08-08 - 
      }
      if(strpos($this->trackerImg2, "http") === 0 ) {
        $desktopImg2 = $this->trackerImg2;
      } else {
        $desktopImg2 = $this->trackerImg2 ? "$headerImg2Location/$this->trackerImg2" : null; // BLP 2023-08-10 -
      }
      if(strpos($this->trackerImgPhone2, "http") === 0) {
        $phoneImg2 = $this->trackerImgPhone2;
      } else {
        $phoneImg2 = $this->trackerImgPhone2 ? "$headerImg2Location/$this->trackerImgPhone2" : null; // BLP 2023-08-10 - 
      }
      $jQuery = <<<EOF
  <!-- jQuery BLP 2022-12-21 - Latest version -->
  <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-migrate-3.4.0.min.js" integrity="sha256-mBCu5+bVfYzOqpYyK4jm30ZxAZRomuErKEFJFIyrwvM=" crossorigin="anonymous"></script>
  <script>
jQuery.migrateMute = false; jQuery.migrateTrace = false;
const phoneImg = "$phoneImg";
const desktopImg = "$desktopImg";
const phoneImg2 = "$phoneImg2";
const desktopImg2 = "$desktopImg2";
console.log("phoneImg: " +phoneImg);
jQuery(document).ready(function($) {
  // BLP 2023-08-08 - desktopImg and phoneImg are supplied by
  // SiteClass::getPageHead(); They are trackerImg1 and trackerImgPhone
  // BLP 2023-08-10 - Same logic as Img2

  // If I do not have a phoneImg then I can just do a normal <img>

  let picture = '';

  console.log("phoneImg="+phoneImg+", desktopImg="+desktopImg+", phoneImg2="+phoneImg2+", desktopImg2="+desktopImg2);
  
  if(!phoneImg) {
    picture += "<img id='logo' src=" + desktopImg + " alt='desktopImage'>";
  } else if(!desktopImg) {
    picture += "<img id='logo' src=" + phoneImg + " alt='phoneImage'>";
  } else { // We have a phone and desktop image.
    picture = "<picture id='logo'>";
    picture += "<source srcset=" + phoneImg + " media='((hover: none) and (pointer: coarse))' alt='phoneImage'>";
    picture += "<source srcset=" + desktopImg + " media='((hover: hover) and (pointer: fine))' alt='desktopImage'>";
    picture += "<img src=" + phoneImg + " alt='phoneImage'>";
    picture += "</picture>";
  }

  // BLP 2023-08-10 - This will remove the <img> tag and replace it
  // with the <picture> tag.

  if(phoneImg || desktopImg) {
    $("header a:first-of-type").first().html(picture);
  }
  
  // BLP 2023-08-10 - Here we need to remove the <img
  // id='headerImage2'> before we replave it with a posible <picture>
  // tag.
  
  $("#headerImage2").remove();

  // BLP 2023-08-10 - look to see if we have any Img2 items.

  picture = '';
  
  if(!phoneImg2) {
    picture += "<img id='headerImage2' src=" + desktopImg2 + " alt='desktopImage2'>";
  } else if(!desktopImg2) {
    picture += "<img id='headerImage2' src=" + phoneImg2 + " alt='phoneImage2'>";
  } else {
    picture = "<picture id='headerImage2'>";
    picture += "<source srcset=" + phoneImg2 + " media='((hover: none) and (pointer: coarse))' alt='phoneImage2'>";
    picture += "<source srcset=" + desktopImg2 + " media='((hover: hover) and (pointer: fine))' alt='desktopImage2'>";
    picture += "<img src=" + phoneImg2 + " alt='phoneImage'>";
    picture += "</picture>";
  } 

  if(phoneImg2 || desktopImg2) {
    $("header a:first-of-type").after(picture);
  }
  
  // Show the Image we are using.

  console.log("VARIABLES -- phoneImg: " + phoneImg + ", desktopImg: " + desktopImg +
              ", phoneImg2: " + phoneImg2 + ", desktopImg2: " + desktopImg2);
});
</script>              
EOF;              
    }
    
    $html = '<html lang="' . $lang . '" ' . $htmlextra . ">"; // stuff like manafest etc.

    // What if headFile is null? Use the Default Head.

    if(!is_null($this->headFile)) {
      if(($p = require_once($this->headFile)) != 1) {
        $pageHeadText = "{$html}\n$p";
      } else {
        throw new SqlException(__CLASS__ . " " . __LINE__ .": $this->siteName, getPageHead() headFile '$this->headFile' returned 1", $this);
      }
    } else {
      // Make a default <head>
      
      $pageHeadText =<<<EOF
$html
<!-- Default Head -->
<head>
$h->title
  <!-- METAs -->
  <meta charset="utf-8"/>
  <meta name="description" content="{$this->desc}"/>
  <!-- local link -->
$this->link
$jQuery
  <!-- extra -->
$h->extra
  <!-- remote script -->
$h->script
  <!-- inline script -->
$h->inlineScript
  <!-- local css -->
$h->css
</head>
EOF;
    }

    // Default header has < /> elements. If not XHTML we remove the /> at the end!
    $pageHead = <<<EOF
{$preheadcomment}{$dtype}
$pageHeadText
EOF;

    return $pageHead;
  }
  
  /**
   * getPageBanner()
   * Get Page Banner
   * BLP 2022-01-30 -- New logic
   * @return string banner
   * NOTE: The body tag is done HERE!
   */

  public function getPageBanner():string {
    $h = new stdClass;

    // BLP 2022-04-09 - These need to be checked here.
    
    $bodytag = $this->bodytag ?? "<body>";
    $mainTitle = $this->banner ?? $this->mainTitle;

    $h->logoAnchor = $this->logoAnchor ?? "https://www.$this->siteDomain";
    
    if(!is_null($this->bannerFile)) {
      $pageBannerText = require($this->bannerFile);
    } else {
      // a default banner
      $pageBannerText =<<<EOF
<!-- Default Header/Banner -->
<header>
<div id='pagetitle'>
$mainTitle
</div>
<noscript style="color: red; border: 1px solid black; padding: 10px; font-size: large;">
<strong>Your browser either does not support JavaScripts
or you have JavaScripts disabled.</strong>
</noscript>
</header>
EOF;
    }

    // Return the Banner

    return <<<EOF
$bodytag
$pageBannerText

EOF;
  }

  /**
   * getPageFooter()
   * Get Page Footer
   * @return string
   */
  
  public function getPageFooter():string {
    // BLP 2022-01-02 -- if nofooter is true just return an empty footer

    $b = new stdClass;
    
    if($this->nofooter === true) {
      return <<<EOF
<footer>
</footer>
</body>
</html>
EOF;
    }
    
    // BLP 2022-02-23 -- added the following.
    
    $b->ctrmsg = $this->ctrmsg;
    $b->msg = $this->msg;
    $b->msg1 = $this->msg1;
    $b->msg2 = $this->msg2;
    
    $b->address = $this->noAddress ? null : ($this->address . "<br>");
    $noCopyright = $this->noCopyright;
    $b->copyright = $noCopyright ? null : ($this->copyright . "<br>");
    if(preg_match("~^\d{4}~", $b->copyright) === 1) {
      $b->copyright = "Copyright &copy; $b->copyright";
    }
    
    $b->aboutwebsite = $this->aboutwebsite ??
                       "<h2><a target='_blank' href='https://bartonlp.com/otherpages/aboutwebsite.php?site=$this->siteName&domain=$this->siteDomain'>About This Site</a></h2>";
    
    $b->emailAddress = $this->noEmailAddress ? null : ($this->emailAddress ?? $this->EMAILADDRESS);
    $b->emailAddress = $this->emailAddress ? "<a href='mailto:$this->emailAddress'>$this->emailAddress</a>" : null;

    // Set the $b values from the b_ values
    
    $b->inlineScript = $this->b_inlineScript ? "<script>\n$this->b_inlineScript\n</script>" : null;
    $b->script = $this->b_script;

    // BLP 2021-10-24 -- lastmod is also available to footerFile to use if wanted.

    if($this->noLastmod !== true) {
      $lastmod = "Last Modified: " . date("M j, Y H:i", getlastmod());
    }

    // BLP 2022-04-09 - We can put the footerFile into $b or use it from mysitemap.json
    // If either is set to 'false' then use the default footer, else use $this->footerFile unless
    // it is false.
    
    if($this->footerFile !== false && $this->footerFile !== null) {
      $pageFooterText = require($this->footerFile);
    } else {
      $pageFooterText = <<<EOF
<!-- Default Footer -->
<footer>
$b->aboutwebsite
$lastmod
$b->script
$b->inlineScript
</footer>
</body>
</html>
EOF;
    }

    return $pageFooterText;
  }

  /**
   * __toString();
   */

  public function __toString() {
    return __CLASS__;
  }
} // End of Class
