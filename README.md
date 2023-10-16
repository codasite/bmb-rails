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
- "Official Brackets" [wpbb-official-brackets]
- "Celebrity Picks" [wpbb-celebrity-picks]
- "Bracket Builder" [wpbb-bracket-builder]

9. Create Oxygen templates for the following post types and add the shortcodes. Under `Where does this template apply?` Select the corresponding post type.

- "bracket" posts: [wpbb-bracket-page]
- "bracket_play" posts: [wpbb-bracket-play]

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

#### What it does

Serves an express api that image-generator can query to generate the screenshot. In production mode, the react app is pulled from a shared volume with react-client that contains the bundled JavaScript code. In dev mode, acts as a proxy server to react-client’s development port

### react-client

|             |                                          |     |
| ----------- | ---------------------------------------- | --- |
| root dir    | `/plugin/includes/react-bracket-builder` |     |
| port        | :8080                                    |     |
| dev script  | `npm run dev:standalone`                 |     |
| prod script | `npm run build:standalone`               |     |

#### What it does

This container serves just the react part of the plugin outside the context of Wordpress. To do this, there is a separate webpack config file with no dependencies on a global ‘wp’ object. In production, the source code simply gets compiled to a ‘dist’ folder and served statically by react-server. In development, the webpack server is used and exposed on port 8080. In this case, the app can be accessed from either react-server on port 3001 or react-client on port 8080.

## Testing

Tests are stored in `plugin/tests` and follow the directory structure of the plugin. For example, the tests for `plugin/includes/domain/class-wpbb-bracket-template.php` are in `plugin/tests/includes/domain/test-bracket-template.php`. All tests must inherit from `WPBB_UnitTestCase` in `plugin/tests/unittest-base.php` if they use custom database tables. All test methods should start with `test_`.

Tests should be run via docker-compose. To run the tests:

1. `make wp-up` to start the local wordpress services
2. `make wp-install` installs wordpress and the test directory
2. `make wp-test` to run the tests

Resources:
https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/
https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/

## Docker live site

Docker can be used to run a local clone of an existing site. This is useful for testing with "live" data.

1. Pull the latest site backup (requires ssh access to the server):
    
    ```
    make wp-pull
    ```

2. Start the local site:

    ```
    make wp-up
    ```

3. Add the test installation and create the admin user (existing credentials can be used as well):
    
    ```
    make wp-init
    ```

## Docker local development

For development it's faster to use a slimmed down version of the site.

1. Delete existing volumes
    
    ```
    make wp-down
    ```

2. Reload containers, intitializing the database from the latest dump

    ```
    make wp-up
    ```

3. Install wordpress plugins
    
    ```
    make wp-install
    ```