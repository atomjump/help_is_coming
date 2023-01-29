# help_is_coming

__WARNING: this project has now moved to https://src.atomjump.com/atomjump/help_is_coming.git__

An AtomJump Messaging Server plugin to give users a timeframe for a response


## Requirements

AtomJump Messaging Server >= 0.5.3


## Installation

Find the server at https://src.atomjump.com/atomjump/loop-server. Download and install.

Download the .zip file or git clone this repository into the directory messaging-server/plugins/help_is_coming

Copy config/configORIGINAL.json to config/config.json

Edit the config file to match your own timeframes on a per forum basis. You can set the default forum as 'default'.

```javascript
{
    "phpPath": "\/usr\/bin\/php",			//Note escaping the '/' may be necessary
    "storeInDb": false,						//Switch this to true if you wish to set this per forum within the database
    "staging": false,						//false is to use the staging version of the messaging-server config
    "serverPath": "your\/atomjump\/messaging\/server\/path\/"
    "forums": [
        {
            "aj": "aj_your_forum_name",			//The forum this applies to, 'default' will apply to all unspecified forums in this list.
            "labelRegExp": "^your_specific_forum_string_start",		//Optional: regular expression used for different 'default' messages when there are several scaleUp databases
            "timeframe": "In seconds this message will remain on the group",
            "message": "The message that gets sent.",
            "helperName": "Any helper name for this forum - this will appear as the author of the automated message",
            "helperEmail": "This email must be set, and must not be anyone else's email, so it should be yours as the administrator",
            "comeBackWithin": "This is the number of seconds when the user can come back and not be re-notified. 86400 seconds = 1 day"
        }
    ]
}
```

Add "help_is_coming" into the "plugins" array of the server's config/config.json file to activate.


## Database messages (Optional)

If you decide to set this on a per forum basis within the database (storeInDb = true), you must run 

```
php install.php
```

once first to add the database field, then within each forum's tbl_layer entry, fill in the 'var_help_is_coming_json' field with e.g

```
{
	"timeframe": 60,
	"message": "Thanks for your message. Our current response time is estimated at 1 day. Please hold while we get in touch with support.",
	"helperName": "AtomJump",
	"helperEmail": "peter@atomjump.com",
	"comeBackWithin": 86400
}
```

