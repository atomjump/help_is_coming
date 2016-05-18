# help_is_coming
An AtomJump Loop Server plugin to give users a timeframe for a response


## Requirements

AtomJump Loop Server >= 0.5.3


## Installation

Find the server at https://github.com/atomjump/loop-server. Download and install.

Download the .zip file or git clone the repository into the directory loop-server/plugins/help_is_coming

Copy config/configORIGINAL.json to config/config.json

Edit the config file to match your own timeframes on a per forum basis. You can set the default forum as 'default'.

Add "help_is_coming" into the "plugins" array of the server's config/config.json file to activate.

## Future Enhancements

* Have a faster loading of the config with e.g. memcache or nodejs
