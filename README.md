# wp-bracket-builder

## Dev setup

1. Run prettier on save with your editor: https://prettier.io/docs/en/editors
2. Add file `phpstan.neon` to the root of the project with the following content:

```
includes:
  - phpstan.neon.dist
```

## Basic Installation (Docker)

1. Copy the `.env.example` file to `.env` and fill in the necessary values.
2. Add the following plugins to `docker/wordpress/plugins`:
  - [Sentry](https://wordpress.org/plugins/wp-sentry-integration/)
  - [WooCommerce](https://wordpress.org/plugins/woocommerce/)
  - [Oxygen](https://drive.google.com/file/d/19UxR1oMcq7yU1EkXxhuC2FMrXPVx8hI2/view?usp=sharing)
  - [Oxygen-WooCommerce](https://drive.google.com/file/d/19Ux5P87RLMcGkyF3n9zbqYU8qCMOyNPb/view?usp=sharing)
3. Run `make composer-install` to install php dependencies.
4. Run `make wp-up` to start the local wordpress services.
5. In a new terminal run `make wp-install` to install wordpress and initialize plugins. This also sets up the test installation
6. `make react-install` to install npm dependencies for the react app.
7. `make react-start` or `make react-build` to start or build the react app.
8. Go to `localhost:8008` in your browser to view the site. The wordpress admin is at `localhost:8000/wp-admin` with username `admin` and password `admin`.

## Basic Installation (Local WP)

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

3. Run `make composer-install` to install php dependencies. 
4. Run `make react-install` to install npm dependencies.
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
13. Run `make react-start` to start the react app.

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
| root dir    | `/react-bracket-builder` |     |
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
3. `make wp-test` to run the tests
4. `make wp-cover` to run the tests with code coverage. The coverage report is available at localhost:8080/coverage


Resources:
https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/
https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/

### Tags
    A number of tags are used to organize brackets for public facing pages.
    - 'bmb_official' - Brackets with this tag will be displayed on the official brackets page
    - 'bmb_vip_featured' - Brackets/Plays with this tag will be displayed on the celebrity picks page
    - 'bmb_vip_profile' - Brackets/Plays with this tag will be displayed on the author's profile page
