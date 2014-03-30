<?php
namespace Bun\Core\Controller;

use Bun\Core\ApplicationInterface;
use Bun\Core\Http\Response;

/**
 * Class BunController
 *
 * @package Bun\Core\Controller
 */
class BunController extends AbstractController
{
    /**
     * @return Response
     */
    protected function faviconAction()
    {
        $favicon = file_get_contents(__DIR__ . '/../Asset/img/favicon.ico');

        return new Response(
            $favicon,
            array(
                'Content-type: image/x-icon'
            )
        );
    }

    /**
     * @return Response
     */
    protected function phpinfoAction()
    {
        if(ENV === ApplicationInterface::APPLICATION_ENV_DEV) {
            phpinfo();
        }
        return new Response('');
    }
}