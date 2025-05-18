# DMG Read More

This plugin has two features:

1. A read more block linking to a specific post
2. A WP-CLI search utility for finding a block within posts

## Prerequisites

-   WordPress 6.8+
-   PHP 8.0+

## Release

See the [latest releases](https://github.com/mikkamp/dmg-read-more/releases) for downloading a zip file.

---

## Development

After cloning the repo, install dependencies:

-   `nvm use` to be sure you're using the recommended node version in .nvmrc
-   `npm install` to install JavaScript dependencies
-   `composer install` to gather PHP dependencies

### Build commands

Now you can build the files using one of these commands:

-   `npm run build` : Build a production version
-   `npm run start` : Build a development version, watch files for changes

### Development tools

-   `npm run lint:css` : Lint all CSS files
-   `npm run lint:js` : Lint all JavaScript files
-   `npm run plugin-zip` : Build a release zip file, should be combined with `npm run build`
