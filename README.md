# wp-bracket-builder

## Installation

### MAC
1. Install MAMP and WordPress according to [these instructions](https://codex.wordpress.org/Installing_WordPress_Locally_on_Your_Mac_With_MAMP)
2. Clone this repository in the `/wordpress/wp-content/plugins` directory
  - Optional: clone the repo anywhere on your system and create a symlink: `ln -s path/to/plugins path/to/repo`
3. From the plugin root `wp-bracket-builder`, install composer dependencies `composer install`
4. In `wp-bracket-builder/includes/react-bracket-builder` run `npm install`
5. Install and activate Oxygen Builder 4.5
6. Install and activate the Woocommerce Oxygen integration
7. Install and activate the WooCommerce Plugin
7. Activate WP Bracket Builder
8. Create the following _Pages_ from the wordpress admin dashboard and add the corresponding shortcode
- "Dashboard" [wpbb-dashboard]
- "Official Tournaments" [wpbb-official-tournaments]
- "Celebrity Picks" [wpbb-celebrity-picks]
- "Bracket Template Builder" [wpbb-template-builder]
9. Create Oxygen templates for the following post types and add the shortcodes
- "bracket_template" posts: [wpbb-bracket-template]
- "bracket_tournament" posts: [wpbb-bracket-tournament]
- "bracket_player" posts: [wpbb-bracket-play]


## Testing Setup
Testing is performed using PHPUnit and the Wordpress Testing Library. Testing infrastructure is set up according to instructions from [codetab.org](https://www.codetab.org/tutorial/wordpress-plugin-development/unit-test/plugin-unit-testing/).

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

9. Run test from the plugin root directory using `vendor/bin/phpunit`.

Note: Before going into production, testing should be configured to use a seperate wordpress installation. _"if wp-tests-config.php is missing from tests/phpunit directory, then WordPress Test Library will use wp-config.php of the WordPress folder and drops the WP tables defined by it."_
