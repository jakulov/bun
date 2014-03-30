<?php
namespace Bun\Form;

use Bun\Core\File\UploadedFile;
use Bun\Core\Http\RequestInterface;
use Bun\Core\Model\ModelInterface;
use Bun\Core\ObjectMapper\ObjectMapperInterface;
use Bun\Core\Repository\RepositoryManager;
use Bun\Form\Field\FormFieldInterface;
use Bun\Form\Validator\FormValidatorInterface;
use Bun\Session\SessionInterface;

/**
 * Interface FormInterface
 *
 * @package Bun\Form
 */
interface FormInterface
{
    const ENC_TYPE_MULTIPART = 'multipart/form-data';

    public function setRequest(RequestInterface $request);

    public function bindModel(ModelInterface &$model);

    /**
     * @return ModelInterface
     */
    public function getModel();

    public function setOption($option, $value);

    public function build($params = array());

    public function render();

    public function renderStart();

    public function renderField($name);

    public function renderRest();

    public function addField(FormFieldInterface $field);

    public function addFields($fields);

    public function removeField($fieldName);

    public function getData($fieldName = null);

    public function getField($fieldName);

    public function addValidator(FormValidatorInterface $validator);

    public function getMethod();

    public function getName();

    public function setName($name);

    public function setObjectMapper(ObjectMapperInterface $objectMapper);

    public function setRepositoryManager(RepositoryManager $repositoryManager);

    /**
     * @return RepositoryManager
     */
    public function getRepositoryManager();
    /**
     * @return ObjectMapperInterface
     */
    public function getObjectMapper();

    public function setSession(SessionInterface $session);

    public function isFormSubmitted();

    /**
     * @return bool
     */
    public function validate();

    public function getValidationErrors();

    /**
     * @return UploadedFile[]
     */
    public function getUploadedFiles();
}