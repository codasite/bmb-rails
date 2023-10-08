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

## Testing

Tests are stored in `plugin/tests` and follow the directory structure of the plugin. For example, the tests for `plugin/includes/domain/class-wp-bracket-builder-bracket-template.php` are in `plugin/tests/includes/domain/test-bracket-template.php`. All tests must inherit from `WPBB_UnitTestCase` in `plugin/tests/unittest-base.php` if they use custom database tables. All test methods should start with `test_`.

Tests should be run via docker-compose. To run the tests:

1. `make wp-up` to start the local wordpress services and install wordpress
2. `make wp-test` to run the tests
