<?php

/**
* Twitter Badge Class File
*
* This Class Object accepts either a JSON stream from twitter.com or
* an already json_decoded object of the JSON stream itself. It will
* parse the object and create an output that matches the user defined
* formatting.
*
* LICENSE: BSD
*
* @package TwitterBadge
* @copyright Copyright (c) 2010 Kien Tran (except where noted)
* @license http://somwhere.com
* @version 1.0
* @since 1.0
*
*/
class TwitterBadge
{
  /*
  * Format of copiled tweet list 
  * @access private
  */
  private $_listFormat = '<ul>[@tweets]</ul>';

  /*
  * Format of tweets 
  * @access private 
  */
  private $_tweetFormat = 
    '<li>[@parsed_text]<span>Posted by [@user->name] [@relative_time]</span></li>';

  /**
  * A parsing function that accepts a JSON string as input and
  * returns decoded json object.
  * @param string $JSON
  * @return object
  */
  public function parseJSON( $JSON ) {

    return $this->parseObject( json_decode($JSON) );

  }

  /**
  * A parsing function that accepts an json decoded object and 
  * returns a HTML string of the tweets.
  * @param object $tweetObjects
  * @return string
  */
  public function parseObject( $tweetObjects ) {
    $tweets = '';
    date_default_timezone_set('UTC');

    foreach ( $tweetObjects as $tweet ) {
      // Assign Relative formatted time
      $tweet->relative_time = 
          $this->distance_of_time_in_words( strtotime( $tweet->created_at ) ) 
          . ' ago';

      // Turn links into links 
      $tweet->parsed_text = 
          eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)',
          '<a href="\\1" target="_blank">\\1</a>', $tweet->text); 
      
      // Turn twitter @username into links to the users Twitter page
      $tweet->parsed_text = preg_replace('/(^|\s)@(\w+)/',
          '\1<a href="http://www.twitter.com/\2">@\2</a>',
          $tweet->parsed_text);
      // Turn #hashtags into searches
      $tweet->parsed_text = preg_replace('/(^|\s)#(\w+)/',
          '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>',
          $tweet->parsed_text);

      // Link to actual Tweet
      $tweet->tweet_link = 'http://twitter.com/' 
                          . $tweet->user->screen_name 
                          . '/status/' . $tweet->id;

      // Get all the tags in the format string
      // Nested keys are denoted via parent->child->childofchild etc.
      preg_match_all('/\[@(\w+[->\w+]*)\]/i', $this->_tweetFormat, $result);


      $tweetOutput = $this->_tweetFormat;
      foreach( $result[1] as $foundkey) {
        $keys = explode( '->', $foundkey);
        $value = $tweet;  //copy tweet object for recursive lookup
        foreach ( $keys as $key ) {
          $value = $value->$key;  //Variable variable
        }
        $tweetOutput = str_replace("[@$foundkey]", $value, $tweetOutput);
      }
      $tweets .= $tweetOutput;
    }

    return str_replace( '[@tweets]', $tweets, $this->_listFormat ); 

  }

  /*
  * Set Individual Tweet Format. Insert tags into the HTML formating
  * as [@key] or [@key->subobject] (ie. [@text] and [@user->screen_name])
  * @param string format - HTML formatted string of one tweet.
  */
  public function setTweetFormat(
    $format = '<li>[@parsed_text]<span>Posted by [@user->name] [@relative_time]</span></li>' ) {

    $this->_tweetFormat = $format;

  }

  /*
  * Set Individual Tweet Format. Insert tags into the HTML formating
  * as [@key] or [@key->subobject] (ie. [@text] and [@user->screen_name])
  * @param string format - HTML formatted string of one tweet.
  */
  public function setListFormat( $format = '<ul>[@tweets]</ul>' ) {

    $this->_listFormat = $format;

  }

  /* 
    This is part of the date helper function of symfony:
    (c) 2004-2006 Fabien Potencier 
    <fabien.potencier@symfony-project.com>
    Thanks to Dustin Whittle for pointing it out
  */
  private function distance_of_time_in_words($from_time, $to_time = null, 
                                     $include_seconds = false){
    $to_time = $to_time? $to_time: time();
    $distance_in_minutes = floor(abs($to_time - $from_time) / 60);
    $distance_in_seconds = floor(abs($to_time - $from_time));
    $string = '';
    $parameters = array();
    if ($distance_in_minutes <= 1){
      if (!$include_seconds){
        $string = $distance_in_minutes == 0 ? 
        'less than a minute' : '1 minute';
      }else{
        if ($distance_in_seconds <= 5){
          $string = 'less than 5 seconds';
        }else if ($distance_in_seconds >= 6 && $distance_in_seconds <= 10){
          $string = 'less than 10 seconds';
        }else if ($distance_in_seconds >= 11 && $distance_in_seconds <= 20){
          $string = 'less than 20 seconds';
        }else if ($distance_in_seconds >= 21 && $distance_in_seconds <= 40){
          $string = 'half a minute';
        }else if ($distance_in_seconds >= 41 && $distance_in_seconds <= 59){
          $string = 'less than a minute';
        }else{
          $string = '1 minute';
        }
      }
    }
    else if ($distance_in_minutes >= 2 && $distance_in_minutes <= 44){
      $string = '%minutes% minutes';
      $parameters['%minutes%'] = $distance_in_minutes;
    }else if ($distance_in_minutes >= 45 && $distance_in_minutes <= 89){
      $string = 'about 1 hour';
    }else if ($distance_in_minutes >= 90 && $distance_in_minutes <= 1439){
      $string = 'about %hours% hours';
      $parameters['%hours%'] = round($distance_in_minutes / 60);
    }else if ($distance_in_minutes >= 1440 && $distance_in_minutes <= 2879){
      $string = '1 day';
    }else if ($distance_in_minutes >= 2880 && $distance_in_minutes <= 43199){
      $string = '%days% days';
      $parameters['%days%'] = round($distance_in_minutes / 1440);
    }else if ($distance_in_minutes >= 43200 && $distance_in_minutes <= 86399){
      $string = 'about 1 month';
    }else if ($distance_in_minutes >= 86400 && 
              $distance_in_minutes <= 525959){
      $string = '%months% months';
      $parameters['%months%'] = round($distance_in_minutes / 43200);
    }else if ($distance_in_minutes >= 525960 && 
              $distance_in_minutes <= 1051919){
      $string = 'about 1 year';
    }else{
      $string = 'over %years% years';
      $parameters['%years%'] = floor($distance_in_minutes / 525960);
    }
    return strtr($string, $parameters);
  }

}

?>
