# Migrations

## Running migrations

1. Ensure the Drupal migrate modules (migrate, migrate_plus, migrate_tools) 
are enabled.
2. Enable the require NIDirect migration modules.
3. Import the NIDirect Drupal 7 database into the Lando drupal7db container database.
4. Add the NIDirect Drupal 7 files to /imports/files/sites/default/files/
5. Use Lando mist to display the migration status
6. Use Lando miip --group=<group name> or Lando miip <individual migration>

## Running tests
<TBC>