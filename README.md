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
- "bracket_template" posts: [wpbb-play-template]
- "bracket_tournament" posts: [wpbb-bracket-tournament]
- TODO: add template for bracket play

## Testing
### Setting up testing infrastructure
Refer to the official documentation [here](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/)

1. Install WP-CLI using [these instructions](https://make.wordpress.org/cli/handbook/guides/installing/)
    - Download wp-cli.phar:
        ```
        curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
        ```
    - Check if it works:
        ```
        php wp-cli.phar --info
        ```
    - Make it executable and put it somewhere on your path to run using `wp`:
        ```
        chmod +x wp-cli.phar
        sudo mv wp-cli.phar /usr/local/bin/wp
        ```

2. Include PHPUnit as a dev dependency in composer.json:
    ```
    {
        ...
        "require-dev": {
            ...
            "phpunit/phpunit": "^9.5"
        }
        ...
    }
    ```
3. Run `composer update`
4. Generate plugin test files:
    ```
    bash
    wp scaffold plugin-tests my-plugin
    ```
5. Configure PHPUnit. Make sure the following code is in `phpunit.xml`, in the root of the plugin, making sure to swap out values for `DB_HOST`, `DB_NAME`, `DB_USERNAME`, and `DB_PASSWORD`, with your own values.
    ```
    <?xml version="1.0" encoding="UTF-8"?>
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" bootstrap="vendor/autoload.php" colors="true" stopOnFailure="false" xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.3/phpunit.xsd" cacheDirectory=".phpunit.cache">
      <php>
        <server name="DB_HOST" value="localhost"/>
        <server name="DB_NAME" value="bracket-builder"/>
        <server name="DB_USERNAME" value="root"/>
        <server name="DB_PASSWORD" value=""/>
      </php>
      <testsuites>
        <testsuite name="My Test Suite">
          <directory>tests</directory>
        </testsuite>
      </testsuites>
    </phpunit>
    ```
6. Run tests using
    ```
    ./vendor/bin/phpunit
    ```