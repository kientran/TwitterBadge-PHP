TwitterBadge-PHP
================
[TwitterBadge-PHP 1.0][dl] -- 5 Aug 2010

[dl]: http://github.com/kientran/TwitterBadge-PHP

Introduction
------------

TwitterBadge-PHP is a PHP5 Class that will accept either a JSON feed in as a
string or a already decoded JSON feed object (using the PHP5 method
json_decode). It will output a string that consists of the tweets formatted
as specificed by the implementor.

It supports customized output using any data element found inside of the
[TwitterAPI JSON][1] tweet object with a few helpered elements for easier
formatting.

[1]: http://dev.twitter.com/doc/get/statuses/user_timeline

Usage
-----

### Basic JSON usage

`$jsonData = $some_json_string_from_twitter

$tb = new TwitterBadge();

echo $tb->parseJSON( $jsonData );`

### Basic Object usage

`$jsonData = $some_json_string_from_twitter

$dataObject = json_decode ($jsonData);

$tb = new TwitterBadge();

echo $tb->parseObject( $dataObject );`

Customizing Output
------------------

The class accepts formatting via a tag replacement system.

Tags are in the format: `[@tagname]` or `[@parenttag->childtag]`

### Individual Tweets 

You can use the class function `setTweetFormat( $string )` to set the
formatting of the tweet by passing in a string of the format desired.
Not providing a formatting string will reset it to the default style.

`<li>[@text] by [@user->name]</li>` will subsitute the `[@text]` tag with the
`text` keyvalue from the JSON object. It will also subsitute the
`[@user->name]` field with the JSON keyvalue `user->name` (a nested object
inside the main JSON object).

You can take any object/value inside the Twitter JSON data object. Please
see the [TwitterAPI docs][1] for a listing of available keys.

#### Default Style

`<li>[@parsed_text]<span>Posted by [@user->name] [@relative_time]</span></li>`

### List of Tweets 

You can use the class function `setListFormat ( $string )` to set the
formatting of the list by passing in a string of the format desired.
Not providing a formatting string will reset it to the default style.

The Class will take the compiled listing of tweets and create the final
formatting wraper around it. *This format string only accepts one tag*.

`<ul>[@tweets]</ul>` will replace the `[@tweet]` tag with the compiled
list of formatted tweets.

#### Default Style

`<ul>[@tweets]</ul>`

### Custom fields not in TwitterAPI

I've included a few helper tags to assist in creation of the formatted
tweets. These are not part of the default TwitterAPI Object

`[@parsed_text]` - Returns the tweet text with @replies, #hashtags, and
http links incased in anchor links. It's smart enough to not linkify
email addresses in the text.

`[@tweet_link]`- Returns the tweet's URL for direct linking.

`[@relative_time]` - Returns the time the tweet was created relative to
now. (eg. 1 hour ago, 2 days ago, 1 week ago, 2 months ago, etc.)
