# wp-bracket-builder

## Installation

### MAC
1. Install MAMP and WordPress according to [these instructions](https://codex.wordpress.org/Installing_WordPress_Locally_on_Your_Mac_With_MAMP)
2. Clone this repository in the `/wordpress/wp-content/plugins` directory
  - Ensure branch is `dev/2.0`
  - Optional: clone the repo anywhere on your system and create a symlink: `ln -s path/to/plugins path/to/repo`
3. From the plugin root `wp-bracket-builder`, install composer dependencies `composer install`
4. In `wp-bracket-builder/includes/react-bracket-builder` run `npm install` and `npm run start`
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
