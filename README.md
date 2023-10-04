# wp-bracket-builder

## Dev setup

1. Run prettier on save with your editor: https://prettier.io/docs/en/editors

## Installation

1. Install Wordpress locally

- Install the Local dev tool: https://localwp.com/
- Create a new site in Local: https://wpengine.com/resources/local-wordpress-development-environment-how-to/
  - Use the latest php version and mysql version

2. Clone the repo anywhere on your system and create a symlink to the `plugin` folder:

```sh
cd <path to wordpress plugins>
ln -s <path to repo>/plugin wp-bracket-builder
```

For example

```sh
cd /Users/karl/Local Sites/bracket-builder/app/public/wp-content/plugins
ln -s /Users/karl/Documents/repos/wp-bracket-builder/plugin wp-bracket-builder
```

3. From the plugin root `wp-bracket-builder/plugin`, install composer dependencies `composer install`
4. In `wp-bracket-builder/plugin/includes/react-bracket-builder` run `npm install`
5. Install and activate Oxygen Builder 4.5
6. Install and activate the WooCommerce Plugin
7. Activate the WP Bracket Builder Plugin
8. Create the following _Pages_ from the wordpress admin dashboard and add the corresponding shortcode

- "Dashboard" [wpbb-dashboard]
- "Official Tournaments" [wpbb-official-tournaments]
- "Celebrity Picks" [wpbb-celebrity-picks]
- "Bracket Template Builder" [wpbb-template-builder]
- "Print Page" (slug: print) [wpbb-print-page] CAN BE PRIVATE

9. Create Oxygen templates for the following post types and add the shortcodes. Under `Where does this template apply?` Select the corresponding post type.

- "bracket_template" posts: [wpbb-bracket-template]
- "bracket_tournament" posts: [wpbb-bracket-tournament]
- "bracket_player" posts: [wpbb-bracket-play]

10. Create a service user to give the image generator node api access to the print page.

- Create a new user with the role "private_reader"
- Add the user's login name to wp-config.php as the value of the constant `WPBB_SERVICE_USER`
- TODO: add the service user token

11. Add mailchimp api keys to `wp-config.php`

```
define('MAILCHIMP_API_KEY','<mailchimpapikey>');
define('MAILCHIMP_FROM_EMAIL','barry@wstrategies.co');
```

Make sure you add it before these lines:

```
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}
```

12. Enable debugging in `wp-config.php` https://wordpress.org/documentation/article/debugging-in-wordpress/
13. Run `npm start` in `plugin/includes/react-bracket-builder` to start the react app.

## Image Generation

Image generation is handled by a series of docker containers managed by docker compose

### TL;DR

- Production: `docker-compose -f docker-compose.prod.yml up -d --build`
- Development: `docker-compose up --build`

### image-generator

|             |                    |                                           |
| ----------- | ------------------ | ----------------------------------------- |
| root dir    | `/image-generator` |                                           |
| port        | :3000              |                                           |
| dev script  | `npm run dev`      | launch the express api with hot reloading |
| prod script | `npm run start`    | launch the express api                    |

#### What it does

The purpose of the image-generator service is to provide the localhost endpoint that Wordpress calls to generate bracket images and pdfs. This is the only endpoint that Wordpress calls directly. Internally, image-generator depends on the react-server service to provide a url that puppeteer can take a screenshot of.

### react-server

|             |                 |                                |
| ----------- | --------------- | ------------------------------ |
| root dir    | `/react-server` |                                |
| port        | :3001           |                                |
| dev script  | `npm run dev`   | express api with hot reloading |
| prod script | `npm run start` | express api no reloading       |

#### what it does

Serves an express api that image-generator can query to generate the screenshot. In production mode, the react app is pulled from a shared volume with react-client that contains the bundled JavaScript code. In dev mode, acts as a proxy server to react-client’s development port

### react-client

|             |                                          |     |
| ----------- | ---------------------------------------- | --- |
| root dir    | `/plugin/includes/react-bracket-builder` |     |
| port        | :8080                                    |     |
| dev script  | `npm run dev:standalone`                 |     |
| prod script | `npm run build:standalone`               |     |

#### what it does

This container serves just the react part of the plugin outside the context of Wordpress. To do this, there is a separate webpack config file with no dependencies on a global ‘wp’ object. In production, the source code simply gets compiled to a ‘dist’ folder and served statically by react-server. In development, the webpack server is used and exposed on port 8080. In this case, the app can be accessed from either react-server on port 3001 or react-client on port 8080.

## Testing Setup

Testing is performed using PHPUnit and the Wordpress Testing Library. Testing infrastructure is set up according to instructions
from [codetab.org](https://www.codetab.org/tutorial/wordpress-plugin-development/unit-test/plugin-unit-testing/).

The following subset of instructions from the link above are used to set up the testing infrastructure (I don't think they need to be repeated).

### Instructions

1. Install the Wordpress testing library. From the plugin root directory:

   ```
   mkdir -p tests/phpunit/tests
   cd tests/phpunit
   svn co https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/
   ```

2. Add `.svn` to the `.gitignore` file
3. Create a PHPUnit configuration in the plugin root `phpunit.xml`
   ```
   <phpunit bootstrap="tests/phpunit/bootstrap.php" backupGlobals="false"
     colors="false" convertErrorsToExceptions="true"
     convertNoticesToExceptions="true" convertWarningsToExceptions="true">
     <testsuites>
       <testsuite>
         <file>tests/phpunit/tests/test-wp-bracket-builder.php</file>
       </testsuite>
     </testsuites>
   </phpunit>
   ```
4. Create the wordpress test configuration file at `tests/phpunit/wp-tests-config.php`

   ```
   <?php

   // change the next line to points to your wordpress dir
   define( 'ABSPATH', '/opt/lampp/htdocs/bracket-builder' );

   define( 'WP_DEBUG', false );

   // WARNING WARNING WARNING!
   // tests DROPS ALL TABLES in the database. DO NOT use a production database

   define( 'DB_NAME', 'wptest' );
   define( 'DB_USER', 'wptest' );
   define( 'DB_PASSWORD', 'wptest' );
   define( 'DB_HOST', 'localhost.localdomain' );
   define( 'DB_CHARSET', 'utf8' );
   define( 'DB_COLLATE', '' );

   $table_prefix = 'wptests_'; // Only numbers, letters, and underscores please!

   define( 'WP_TESTS_DOMAIN', 'localhost' );
   define( 'WP_TESTS_EMAIL', 'admin@example.org' );
   define( 'WP_TESTS_TITLE', 'Test Blog' );

   define( 'WP_PHP_BINARY', 'php' );

   define( 'WPLANG', '' );
   ```

5. Create the bootstrap test file at `tests/phpunit/bootstrap.php`

   ```
   <?php

   // path to test lib bootstrap.php
   $test_lib_bootstrap_file = dirname( __FILE__ ) . '/includes/bootstrap.php';

   if ( ! file_exists( $test_lib_bootstrap_file ) ) {
       echo PHP_EOL . "Error : unable to find " . $test_lib_bootstrap_file . PHP_EOL;
       exit( '' . PHP_EOL );
   }

   // set plugin and options for activation
   $GLOBALS[ 'wp_tests_options' ] = array(
           'active_plugins' => array(
                   'wp-bracket-builder/wp-bracket-builder.php',
           ),
           'wpsp_test' => true
   );

   // call test-lib's bootstrap.php
   require_once $test_lib_bootstrap_file;

   $current_user = new WP_User( 1 );
   $current_user->set_role( 'administrator' );

   echo PHP_EOL;
   echo 'Using Wordpress core : ' . ABSPATH . PHP_EOL;
   echo PHP_EOL;
   ```

6. Create the test database, test database users, and grant permissions.
   ```
   cd /opt/lampp
   bin/mysql -u root
   create database wptest;
   GRANT ALL PRIVILEGES ON wptest.* TO wptest@localhost IDENTIFIED BY 'wptest';
   flush privileges;
   exit
   ```
7. Create the test file `tests/phpunit/tests/test-wp-bracket-builder.php`, the test file indicated in the configuration file, and import tests there.

8. Run test from the plugin root directory using `vendor/bin/phpunit`.

Note: Before going into production, testing should be configured to use a seperate wordpress installation. _"if wp-tests-config.php is missing from tests/phpunit directory, then WordPress Test Library will use wp-config.php of the WordPress folder and drops the WP tables defined by it."_
