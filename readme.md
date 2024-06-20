**Pff2 MVC PHP framework**
==============================

[![Build Status](https://app.travis-ci.com/stonedz/pff2.svg?branch=master)](https://travis-ci.org/stonedz/pff2)
[![Coverage Status](https://img.shields.io/coveralls/stonedz/pff2.svg)](https://coveralls.io/r/stonedz/pff2?branch=master)
[![Latest Stable Version](https://poser.pugx.org/stonedz/pff2/v/stable.svg)](https://packagist.org/packages/stonedz/pff2)
[![License](https://poser.pugx.org/stonedz/pff2/license.svg)](https://packagist.org/packages/stonedz/pff2)

## Composer Installation

To setup a new project:

   - Create e new directory
   - Install composer in the directory (or do a global composer install). See [here for the instructions](https://getcomposer.org/doc/00-intro.md).
    - Create a composer.json file with the following content:

```json
{
    "name": "company/project-name",
    "description": "",
    "minimum-stability": "beta",
    "license": "proprietary",
    "authors": [
        {
            "name": "",
            "email": ""
        }
    ],
    "require": {
        "stonedz/pff2": "~2",
        "stonedz/pff2-installers": "v2.0.7",
        "stonedz/pff2-doctrine": "3.0.x-dev"
    },
    "autoload": {
        "psr-4": {
            "pff\\models\\": "app/models",
            "pff\\controllers\\": "app/controllers",
            "pff\\services\\": "app/services"
        }
    }
}

```

 - Run <code>php composer.phar install</code>
 - Run <code>vendor/bin/init</code> (and follow on screen instructions)

## Docker integration

Install docker and docker-compose on your system, then

```
  $ docker-compose up
```

The first time the containers are generated a new Mariadb admin password will be
created and shown on the console, use that to connect your app to the DB. you
can also use the same username and password in phpmyadmin to manage your db.

You can modify the file docker-compose.yml to change ports and settings for the
containers.

**Please see the [Wiki](https://github.com/stonedz/pff2/wiki) for more informations.**
