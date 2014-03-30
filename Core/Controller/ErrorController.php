<?php
namespace Bun\Core\Controller;

use Bun\Core\Exception\Exception;
use Bun\Core\Http\Response;
use Bun\Logger\LoggerInterface;

/**
 * Class ErrorController
 *
 * @package Bun\Core\Controller
 */
class ErrorController extends AbstractController
{
    // TODO: error headers
    protected $errorHeaders = array(
        '404' => '404 Not-found',
        '500' => '500 Internal server error',
    );

    /**
     * @param Exception $exception
     * @return Response
     */
    protected function indexAction(Exception $exception)
    {
        $errorCode = (string)$exception->getCode();
        $header = isset($this->errorHeaders[$errorCode]) ?
            $this->errorHeaders[$errorCode] :
            $this->errorHeaders['500'];

        $content = '<p>' . $exception->getMessage() . '</p>';
        $content .= '<p>' . nl2br($exception->getTraceAsString()) . '</p>';

        /** @var LoggerInterface $logger */
        $logger = $this->container->get('bun.logger');
        $logger->log(
            $exception->getMessage() .': '. $exception->getTraceAsString(),
            LoggerInterface::LOG_LEVEL_ERROR
        );

        return new Response($content, array($errorCode => $header));
    }
}