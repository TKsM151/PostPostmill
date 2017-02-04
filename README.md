raddit
======

**raddit** is a web-based forum with voting and threaded comments. It is built
on the [Symfony](https://symfony.com/) framework. Any similarities between this
software and that of a large community symbolised by an alien logo are purely
coincidental.

## Requirements

* PHP >= 5.6 with the PDO_PGSQL extension
* PostgreSQL >= 9.2
* [Composer](https://getcomposer.org/)
* [Node.js](https://nodejs.org/en/), optionally with [Yarn](https://yarnpkg.com)

My dev environment runs PHP 7.1 and PostgreSQL 9.4 under macOS Sierra. If any
compatibility issues with other software versions or operating systems should
arise, a bug report would be most appreciated.

## Getting started

Clone the repository somewhere and navigate there with the command line.

### Building frontend assets

1. Run `npm install` (`yarn install` is preferred if you have Yarn).
2. Run `npm run build`/`yarn run build`. The `css` and `js` directories in `web`
   should now contain some files.

### Setting up the backend

1. Run `composer install`. You should be prompted for database credentials, mail
   sending stuff, and a secret token. You can leave the default values for the
   mail stuff, but you must supply valid database credentials.

   If this step fails, you can remove or edit `app/config/parameters.yml` and
   run `composer install` to try again.
2. Run `bin/symfony_requirements` to check that your environment meets the
   requirements needed to run the software. Fix any errors that arise.
3. Run `bin/console doctrine:migrations:migrate` to load the database schema.
4. Run `bin/console doctrine:fixtures:load` to load example data to play around
   with.
5. Run `bin/console server:run` to start the application.
6. Navigate to <http://localhost:8000/>. Log in with `emma`/`goodshit`.

## Contributions

Before contributing new features, please open an issue so we can discuss the
direction in which to take the project and avoid hurt feelings. Bug fixes are 
always welcome, however.

## License

The software is released under the zlib license. See the `LICENSE` file for 
details.
