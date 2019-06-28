# Migrations

## Running migrations

1. Ensure the Drupal migrate modules (`migrate, migrate_plus, migrate_tools`)
are enabled.
2. Enable the require NIDirect migration modules.
3. Import the NIDirect Drupal 7 database into the Lando `drupal7db` container database.
4. Add the NIDirect Drupal 7 files to `/imports/files/sites/default/files/`
5. Use `lando mist` to display the migration status
6. Use `lando miip --group=<group name>` or `lando miip <individual migration>`

## Running tests

Automated testing is broken into different categories: static analysis, unit and functional tests.

### Static analysis

The project uses PHPCS to validate all custom code against [Drupal.org coding standards](https://www.drupal.org/docs/develop/standards/coding-standards); including migrate modules and custom themes.

For convenience, it can be invoked for local development using Lando: `lando phpcs`

### Unit tests

Any custom code that implements unit tests will be checked when using PHPUnit.

For local work, invoke it with `lando phpunit`. This acts as a local tool wrapper for the `drupal8/phpcs.sh` script in order to simplify usage.

### Functional tests

Migrations and key user activities and journeys are tested using a headless Chromedriver browser running in a local container. Drupal Core has adopted nightwatch.js as their functional testing tool. We use the same configuration file as Drupal Core, and can run any nightwatch.js tests (core, contrib or custom) by specifying the test suite via a tags parameter, eg:

`lando nightwatch --skiptags core`: run all tests except those tagged with `core`.
`lando nightwatch --tag nidirect-migrate`: run all tests tagged with `nidirect-migrate`.
`lando nightwatch /path/to/your/test/file.js`: run a specific set of tests in a single file.

## Environmental variables

Some tests use [environmental variables](https://en.wikipedia.org/wiki/Environment_variable) to prevent setting potentially sensitive values directly into test code. How these are set will vary from one environment to another, but in the case of local development using Lando you will find them under:

`/config/drupal.env`

If you need to change this file, you will also need to rebuild your local appserver service: `lando rebuild -s appserver`

## NightWatchJS tests ##

Import the included 'D7_Migrate_View' View into existing Drupal 7 NI Direct site and update the TEST_D7_URL env var to the URL of the site. 
This view contains XML data export displays for most of the migrated entities with paths specified as /migrate/<entity> (see 'before' hook in each test for full path).
Each display will return a random entity but this can be overridden in the test to return a specific node by appending an ID to the end of the URL e.g. /migrate/recipe/5012


