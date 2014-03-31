<?php
namespace Bun\Form;

use Bun\Core\Http\RequestAwareInterface;
use Bun\Core\Http\RequestInterface;
use Bun\Core\ObjectMapper\ObjectManagerAwareInterface;
use Bun\Core\ObjectMapper\ObjectMapperInterface;
use Bun\Core\Repository\RepositoryManager;
use Bun\Core\Repository\RepositoryManagerAwareInterface;
use Bun\Session\SessionAwareInterface;
use Bun\Session\SessionInterface;

/**
 * Class FormBuilder
 *
 * @package Bun\Form
 */
class FormBuilder implements ObjectManagerAwareInterface, SessionAwareInterface, RequestAwareInterface, RepositoryManagerAwareInterface
{
    /** @var ObjectMapperInterface */
    protected $objectMapper;
    /** @var SessionInterface */
    protected $session;
    /** @var RequestInterface */
    protected $request;
    /** @var RepositoryManager */
    protected $repositoryManager;

    /**
     * @param ObjectMapperInterface $objectMapper
     */
    public function setObjectManager(ObjectMapperInterface $objectMapper)
    {
        $this->objectMapper = $objectMapper;
    }

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param $formClass
     * @param null $formName
     * @param array $formParams
     * @return FormInterface
     * @throws FormException
     */
    public function getForm($formClass, $formName = null, $formParams = array())
    {
        if(class_exists($formClass)) {
            $form = new $formClass;
            if ($form instanceof FormInterface) {
                $form->setObjectMapper($this->objectMapper);
                $form->setSession($this->session);
                $form->setRepositoryManager($this->repositoryManager);
                if($formName !== null) {
                    $form->setName($formName);
                }
                $form->build($formParams);
                $form->setRequest($this->request);

                return $form;
            }

            throw new FormException('Form class ' . $formClass . ' should implements FormInterface');
        }

        throw new FormException('Form class '. $formClass .' does not exists');
    }

    /**
     * @param RepositoryManager $repositoryManager
     */
    public function setRepositoryManager(RepositoryManager $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;
    }
}