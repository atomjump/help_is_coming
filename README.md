# help_is_coming
An AtomJump Loop Server plugin to give users a timeframe for a response


## Requirements

AtomJump Loop Server >= 0.5.1


## Installation

Find the server at https://github.com/atomjump/loop-server. Download and install.

Download the .zip file or git clone the repository into the directory loop-server/plugins/rss_feed

Copy config/configORIGINAL.json to config/config.json

Edit the config file to match your own timeframes on a per forum basis. You can set the default forum as 'default'.


## Future Enhancements

* Send response privately to the user
* Have a time delay before the message gets sent again e.g. one day
* Don't message the forum's owners
* Store the ids of forums in the .json file for faster retrieval
* Have a faster loading of the config with e.g. memcache or nodejs
