<?php

namespace moein7tl\autoMigrate;

use Yii;
use yii\console\Controller;

/**
 * Class AutoMigrateController
 * @package moein7tl\autoMigrate
 */
class AutoMigrateController extends Controller {

    private $config =   [];
    
    public function init()
    {
        $this->config   =   require 'config'.DIRECTORY_SEPARATOR.'migrations.php';
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function actionIndex()
    {
        Yii::$app->runAction('auto-migrate/up');
    }

    public function actionUp()
    {
        $migrations =   $this->readAndSortMigrations($this->config['relational']);
        if (count($migrations) > 0) $this->migrateSQL($migrations);
        
        $migrations =   $this->readAndSortMigrations($this->config['mongodb']);
        if (count($migrations) > 0) $this->migrateMongoDB($migrations);
    }

    public function actionDown()
    {
        $migrations =   $this->readAndSortMigrations($this->config['relational'], SORT_DESC);
        if (count($migrations) > 0) $this->rollbackSQL($migrations);
        $migrations =   $this->readAndSortMigrations($this->config['mongodb'], SORT_DESC);
        if (count($migrations) > 0) $this->rollbackMongoDB($migrations);
    }

    public function readAndSortMigrations($directories = [], $sort  =   SORT_ASC)
    {
        $migrations  =   [];
        foreach ($directories as $directory) {
            $migrationsFiles    =   glob($directory.'/*{.php}', GLOB_BRACE);
            foreach ($migrationsFiles as $file){
                $file   =   basename($file);
                $file   =   substr($file, 1, 13);
                $migrations[$file]   =   $directory;
            }
        }
        if ($sort   === SORT_ASC) {
            ksort($migrations);
        } else {
            krsort($migrations);
        }

        return $migrations;
    }

    private function migrateSQL($migrations = [])
    {
        foreach ($migrations as $mark => $path) {
            $this->migrate($path);
        }
    }

    private function rollbackSQL($migrations = [])
    {
        foreach ($migrations as $mark => $path) {
            $this->rollback($mark, $path);
        }
    }

    private function migrateMongoDB($migrations = [])
    {
        foreach ($migrations as $mark => $path) {
            $this->migrate($path, 'mongodb');
        }
    }

    private function rollbackMongoDB($migrations = [])
    {
        foreach ($migrations as $mark => $path) {
            $this->rollback($mark, $path, 'mongodb');
        }
    }

    private function migrate($path, $type = 'sql')
    {
        $app        =   Yii::$app;
        $controller =   ($type === 'sql')?'migrate':'mongodb-migrate';

        new Yii\console\Application([
            'id'            =>  'Migrations',
            'basePath'      =>  '@app',
            'components'    =>  [
                'db'            =>  $app->db,
                'mongodb'       =>  $app->mongodb,
                'authManager'   =>  $app->authManager
            ],
            'controllerMap' => [
                'mongodb-migrate'   =>  'yii\mongodb\console\controllers\MigrateController'
            ]
        ]);

        Yii::$app->runAction("{$controller}/up",[
            1,
            'migrationPath' =>  $path,
            'interactive'   =>  false
        ]);

        Yii::$app   =   $app;
    }

    private function rollback($mark, $path, $type = 'sql')
    {
        $app        =   Yii::$app;
        $controller =   ($type === 'sql')?'migrate':'mongodb-migrate';

        new Yii\console\Application([
            'id'            =>  'Migrations',
            'basePath'      =>  '@app',
            'components'    =>  [
                'db'            =>  $app->db,
                'mongodb'       =>  $app->mongodb,
                'authManager'   =>  $app->authManager
            ],
            'controllerMap' => [
                'mongodb-migrate'   =>  'yii\mongodb\console\controllers\MigrateController'
            ]
        ]);

        Yii::$app->runAction("{$controller}/mark",[
            $mark,
            'migrationPath' =>  $path,
            'interactive'   =>  false
        ]);

        Yii::$app->runAction("{$controller}/down",[
            1,
            'migrationPath' =>  $path,
            'interactive'   =>  false
        ]);

        Yii::$app   =   $app;
    }
}

