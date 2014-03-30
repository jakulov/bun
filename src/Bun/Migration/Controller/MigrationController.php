<?php
namespace Bun\Migration\Controller;

use Bun\Migration\Exception\MigrationException;
use Bun\Tool\ConsoleResponse;
use Bun\Tool\Controller\ToolController;
use Bun\Migration\MigrationManager;

/**
 * Class MigrationController
 *
 * @package Bun\Migration\Controller
 */
class MigrationController extends ToolController
{
    /** @var MigrationManager */
    protected $migrationManager;

    /**
     * @return ConsoleResponse|void
     */
    protected function indexAction()
    {
        return $this->helpAction();
    }

    /**
     * @return \Bun\Tool\ConsoleResponse|void
     */
    protected function helpAction()
    {
        self::out('');
        self::out('Welcome to Bun Migration Manager');

        $commands = array(
            'bun.migration:migrate' => 'Runs existing migrations for all applications' . "\n\t" .
                'arguments' . "\n\t" .
                '[--name=migration_name] - run only specified migration' . "\n\t" .
                '[--verbose] - show all migrations list',
        );

        self::outCommandsHelp($commands);

        return new ConsoleResponse('');
    }

    /**
     * @return ConsoleResponse
     */
    protected function migrateAction()
    {
        self::out('');
        self::out('loading migrations...', "\n", 'light_gray');
        $verbose = $this->hasConsoleArgument('verbose');
        $name = null;
        if ($this->hasConsoleArgument('name')) {
            $name = $this->getConsoleArgumentValue('name');
        }

        try {
            $migrations = $this->getMigrationManager()->loadApplicationMigrations($this->application);
        }
        catch (MigrationException $e) {
            self::out('ERROR: ' . $e->getMessage(), "\n", 'light_red');

            return new ConsoleResponse('', ConsoleResponse::RESPONSE_FAIL);
        }

        $isFailed = false;
        foreach ($migrations as $mTime => $migration) {
            $done = null;
            if ($name === null) {
                $done = $this->getMigrationManager()->executeMigration($migration);
            }
            elseif ($name === $migration->getName()) {
                $done = $this->getMigrationManager()->executeMigration($migration);
            }
            elseif ($verbose) {
                self::out('migration: ' . $migration->getName() . ' ignored', "\n", 'light_gray');
            }

            if ($done !== MigrationManager::MIGRATION_IGNORED) {
                if ($done === MigrationManager::MIGRATION_DONE) {
                    self::out('migration: ' . $migration->getName() . ' done', "\n", 'light_green');
                }
                elseif ($done === MigrationManager::MIGRATION_FAILED) {
                    $isFailed = true;
                    self::out('migration: ' . $migration->getName() . ' failed', "\n", 'light_red');
                }
            }
            elseif ($verbose) {
                self::out('migration: ' . $migration->getName() . ' ignored', "\n", 'light_gray');
            }
        }

        self::out('migrating done');

        return new ConsoleResponse('', $isFailed ? ConsoleResponse::RESPONSE_FAIL : ConsoleResponse::RESPONSE_OK);
    }

    /**
     * @return MigrationManager
     */
    public function getMigrationManager()
    {
        if ($this->migrationManager === null) {
            $this->migrationManager = $this->container->get('bun.migration.manager');
        }

        return $this->migrationManager;
    }
}