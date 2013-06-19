# Pinboard.in feed filter

This project takes a tag feed from pinboard.in and removes duplicate links and links that the configured user has
already in her own bookmark list. 

## Installation

1. Clone repository
2. `composer install`
3. Create `public/.htaccess` and `public/creds.json`

### .htaccess and creds.json example

`.htaccess`

    SetEnv CRED_FILE creds.json

`creds.json`

    {
        "CONFIG": {
            "CONFIG_VARS": {
                "PINBOARD_AUTH_TOKEN": "your_pinboard_username:your_pinboard_token"
            }
        }
    }

You *must* change the values in `creds.json`!


### Installation on CloudControl

The app can be hosted on [CloudControl][1]. Be sure to deploy in the pinky environment, otherwise the APC cache will fail.

## TODO

* Move code from public page to worker. The worker stores the generated file.
* Use [Silex][2] for web stuff, use different web root. See https://github.com/cloudControl/php-silex-example-app
* Configuration of feeds via web (login with OpenID/[Persona][3]).
* Put generated feeds on Dropbox

[1]: http://cloudcontrol.com/
[2]: http://silex.sensiolabs.org/
[3]: http://www.mozilla.org/en-US/persona/
