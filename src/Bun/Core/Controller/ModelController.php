<?php
namespace Bun\Core\Controller;

use Bun\Core\File\File;
use Bun\PDO\Generator\ModelGeneratorException;
use Bun\Tool\ConsoleResponse;
use Bun\Tool\Controller\ToolController;
use Bun\PDO\Generator\ModelGenerator;

/**
 * Class ModelController
 *
 * @package Bun\Core\Controller
 */
class ModelController extends ToolController
{
    /**
     * @return ConsoleResponse
     */
    protected function indexAction()
    {
        return $this->helpAction();
    }

    /**
     * @return ConsoleResponse
     */
    protected function helpAction()
    {
        self::out('');
        self::out('Welcome to Bun PDO model code generator');

        $commands = array(
            'bun.core.model:schema' => 'Generates model schema from giving table ' . "\n\t" .
                'arguments:' . "\n\t" .
                '--table=table_name' . "\n\t" .
                '--model=Bun\\\\Core\\\\ModelName' . "\n\t" .
                '[--force - don`t promt override]' . "\n\t" .
                'note: model base class will be Bun\\PDO\\Model\\AbstractPdoMapperModel',
            'bun.core.model:body'   => 'Generates model class body by existing schema' . "\n\t" .
                'arguments:' . "\n\t" .
                '--model=Bun\\\\Core\\\\ModelName' . "\n\t" .
                '[--force - do not promt override]',
        );
        self::outCommandsHelp($commands);

        return new ConsoleResponse('');
    }

    /**
     * @return ConsoleResponse
     */
    protected function schemaAction()
    {
        $args = $this->getConsoleArguments();
        $table = isset($args['table']) ? $args['table'] : null;
        if (!$table) {
            return new ConsoleResponse('Table not specified', ConsoleResponse::RESPONSE_FAIL);
        }
        $model = isset($args['model']) ? $args['model'] : null;
        if (!$model) {
            return new ConsoleResponse('Model not specified', ConsoleResponse::RESPONSE_FAIL);
        }

        /** @var ModelGenerator $generator */
        $generator = $this->getContainer()->get('bun.pdo.model_generator');

        if (!$this->hasConsoleArgument('force') && File::exists($generator->getModelFile($model))) {
            if (!$this->promt('You sure you want rewrite existing model: ')) {
                return new ConsoleResponse('Generation canceled', ConsoleResponse::RESPONSE_CANCELED);
            }
        }

        try {
            $ok = $generator->generateSchema($table, $model);
        }
        catch (ModelGeneratorException $e) {
            return new ConsoleResponse('Generator exception: ' . $e->getMessage(), ConsoleResponse::RESPONSE_FAIL);
        }

        if ($ok) {
            return new ConsoleResponse('Schema generated successfully');
        }

        return new ConsoleResponse('Schema generation failed', ConsoleResponse::RESPONSE_FAIL);
    }

    /**
     * @return ConsoleResponse
     */
    protected function bodyAction()
    {
        $args = $this->getConsoleArguments();

        $model = isset($args['model']) ? $args['model'] : null;
        if (!$model) {
            return new ConsoleResponse('Model not specified', ConsoleResponse::RESPONSE_FAIL);
        }

        /** @var ModelGenerator $generator */
        $generator = $this->getContainer()->get('bun.pdo.model_generator');

        if (!$this->hasConsoleArgument('force') && File::exists($generator->getModelFile($model))) {
            if (!$this->promt('You sure you want rewrite existing model: ')) {
                return new ConsoleResponse('Generation canceled', ConsoleResponse::RESPONSE_CANCELED);
            }
        }

        try {
            $ok = $generator->generateBody($model);
        }
        catch (ModelGeneratorException $e) {
            return new ConsoleResponse('Generator exception: ' . $e->getMessage(), ConsoleResponse::RESPONSE_FAIL);
        }

        if ($ok) {
            return new ConsoleResponse('Model body generated successfully');
        }

        return new ConsoleResponse('Model body generation failed', ConsoleResponse::RESPONSE_FAIL);
    }
}