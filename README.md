# Yii2 Auto Migrate Package
This package helps you to run migrations from different paths and databases in easier way.
 
## Installation

```
php composer.phar require "moein7tl/yii2-automigrate:dev-master" 
```

or add following line to `composer.json` and run `composer update`

```json
"moein7tl/yii2-automigrate": "dev-master"
```

## Configuration
Add following line to your console configuration file ( `config/console.php` )

```
'controllerMap' => [
    'auto-migrate'      =>  [
        'class'     =>  moein7tl\autoMigrate\AutoMigrateController::className()
    ]
],
```

Create `migrations.php` in `config` folder with following structure:
 
```
return [
    'mongodb'       =>  [
        // MongoDB migrations paths
        Yii::getAlias('@app').DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, ['modules', 'users', 'migrations', 'mongo']),
    ],
    'relational'    =>  [
        // Relational Database migrations paths
        Yii::getAlias('@yii').DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, ['rbac', 'migrations']),
        Yii::getAlias('@app').DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, ['modules', 'users', 'migrations', 'relational'])
    ]
];

```

## Usage

To run and rollback migrations:

```
php yii auto-migrate/up
php yii auto-migrate/down
```
