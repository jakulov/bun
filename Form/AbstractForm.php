<?php
namespace Bun\Form;

use Bun\Core\Http\RequestAwareInterface;
use Bun\Core\ObjectMapper\ObjectMapperInterface;
use Bun\Core\Repository\RepositoryManager;
use Bun\Form\Field\FileField;
use Bun\Form\Field\FormFieldInterface;
use Bun\Core\Http\RequestInterface;
use Bun\Core\Model\ModelInterface;
use Bun\Form\Validator\FormValidatorInterface;
use Bun\Session\SessionAwareInterface;
use Bun\Session\SessionInterface;
use Bun\Core\File\UploadedFile;

/**
 * TODO: CSRF protection!!
 * Class AbstractForm
 *
 * @package Bun\Form
 */
abstract class AbstractForm implements FormInterface, SessionAwareInterface, RequestAwareInterface
{
    const SALT = 'Bu2NFa3rme4rS5Ab9';
    /** @var string */
    protected $name = 'form';
    /** @var string */
    protected $method = 'POST';
    /** @var ObjectMapperInterface */
    protected $objectMapper;
    /** @var RepositoryManager */
    protected $repositoryManager;
    /** @var string */
    protected $encType = '';
    /** @var RequestInterface */
    protected $request;
    /** @var ModelInterface */
    protected $model;
    /** @var SessionInterface */
    protected $session;
    /** @var FormFieldInterface[] */
    protected $fields = array();
    /** @var array */
    protected $renderedFields = array();
    /** @var bool */
    protected $hasFields = false;
    /** @var string */
    protected $formClass = '';
    /** @var string */
    protected $fieldWrap = '%s';
    /** @var string */
    protected $submitWrap = '%s';
    /** @var string */
    protected $submitClass = '';
    /** @var string */
    protected $action = '';
    /** @var string */
    protected $formId = '';
    /** @var string */
    protected $onSubmit = '';
    /** @var bool */
    protected $csrfProtect = false;
    /** @var FormValidatorInterface[] */
    protected $validators = array();
    /** @var array */
    protected $validationErrors = array();
    /** @var string */
    protected $submitValue = 'Submit';
    /** @var string */
    protected $submitedToken;

    public function __construct()
    {

    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        $this->bindRequestData();
    }

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param ObjectMapperInterface $objectMapper
     */
    public function setObjectMapper(ObjectMapperInterface $objectMapper)
    {
        $this->objectMapper = $objectMapper;
    }

    /**
     * @return ObjectMapperInterface
     */
    public function getObjectMapper()
    {
        return $this->objectMapper;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $option
     * @param $value
     */
    public function setOption($option, $value)
    {
        if (property_exists(get_class($this), $option)) {
            $this->$option = $value;
        }
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    abstract public function build($params = array());

    /**
     * @param ModelInterface $model
     * @return ModelInterface
     * @throws FormException
     */
    public function bindModel(ModelInterface &$model)
    {
        if ($this->hasFields) {
            $this->model = $this->bindModelData($model);

            return $this->model;
        }

        throw new FormException('Trying to bind model on empty form');
    }

    /**
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return bool
     */
    public function isFormSubmitted()
    {
        if ($this->request instanceof RequestInterface) {
            $getter = $this->requestGetter();

            if ($this->request->method() === $this->method) {
                $formData = $this->request->$getter($this->name);
                if (isset($formData['__submit'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param ModelInterface $model
     * @return ModelInterface
     */
    protected function bindModelData(ModelInterface &$model)
    {
        if (!$this->isFormSubmitted()) {
            foreach ($this->fields as $fieldName => $field) {
                $getter = 'get' . ucfirst($fieldName);
                if (method_exists($model, $getter)) {
                    $value = $model->$getter();
                    $field->setValue($value);
                }
            }
        }
        else {
            $data = $this->getData();
            foreach ($this->fields as $fieldName => $field) {
                $setter = 'set' . ucfirst($fieldName);
                //$isModel = $field->getOption('source_model') !== null;
                if (method_exists($model, $setter) /*&& (!$isModel || $data[$fieldName])*/) {
                    call_user_func_array(array($model, $setter), array($data[$fieldName]));
                }
            }
        }

        return $model;
    }

    /**
     * Bind Request data to fields
     */
    protected function bindRequestData()
    {
        if ($this->isFormSubmitted()) {
            $getter = $this->requestGetter();
            $formData = call_user_func_array(array($this->request, $getter), array($this->getName()));
            foreach ($this->fields as $field) {
                $field->setData(isset($formData[$field->getName()]) ? $formData[$field->getName()] : null);
            }
            if($this->csrfProtect) {
                $this->submitedToken = isset($formData['__csrf']) ? $formData['__csrf'] : null;
            }
        }
    }

    /**
     * @param null $field
     * @return mixed|null
     */
    public function getData($field = null)
    {
        if ($field === null) {
            $data = array();
            foreach ($this->fields as $field) {
                $data[$field->getName()] = $field->getValue();
            }

            return $data;
        }

        if (isset($this->fields[$field])) {
            return $this->fields[$field]->getValue();
        }

        return null;
    }

    /**
     * @param string $addHtml
     * @param null $formClass
     * @param null $formId
     * @return string
     */
    public function render($addHtml = '', $formClass = null, $formId = null)
    {
        if ($formClass !== null) {
            $this->formClass .= ' ' . $formClass;
        }
        if ($formId !== null) {
            $this->formId = $formId;
        }

        $form = $this->renderRest();

        return $form;
    }

    /**
     * @param string $addHtml
     * @return string
     */
    public function renderRest($addHtml = '')
    {
        $form = '';
        if (!in_array('__form_start', $this->renderedFields)) {
            $form .= $this->renderStart($addHtml);
        }

        foreach ($this->fields as $fieldName => $field) {
            $form .= $this->renderField($fieldName);
        }

        if (!in_array('__form_end', $this->renderedFields)) {
            $form .= $this->renderEnd();
        }

        return $form;
    }

    /**
     * @param $fieldName
     * @return null
     */
    public function renderField($fieldName)
    {
        if (isset($this->fields[$fieldName])) {
            $field = $this->fields[$fieldName];
            if (!in_array($field->getName(), $this->renderedFields)) {
                $this->renderedFields[] = $field->getName();

                return $field->render($this->fieldWrap);
            }
        }

        return null;
    }

    /**
     * @param string $addHtml
     * @return string
     */
    public function renderStart($addHtml = '')
    {
        $this->renderedFields[] = '__form_start';

        if (!$this->formId) {
            $this->formId = 'form-' . $this->name;
        }

        $errors = $this->getValidationErrors();
        $formErrors = isset($errors['__form']) ?
            $errors['__form'] :
            array();
        $outErrors = '';
        if($formErrors) {
            $outErrors = '<div class="alert alert-danger">'. join('<br>', $formErrors) .'</div>';
        }

        return '<form ' .
        'class="' . $this->formClass .
        '" action="' . $this->action .
        '" method="' . $this->getMethod() .
        '" name="' . $this->getName() .
        '" id="' . $this->formId .
        '" enctype="' . $this->encType .
        '" ' . $addHtml .
        '>' . $outErrors;
    }

    /**
     * @return string
     */
    public function renderEnd()
    {
        $tokenField = $this->csrfProtect ? $this->renderCsrf() : '';
        $this->renderedFields[] = '__form_end';
        $submit = ($this->submitValue !== null) ?
            '<input class="' . $this->submitClass . '" type="submit" name="' .
            $this->getName() . '[__submit]" value="' .
            $this->submitValue . '">' :
            '';

        return sprintf($this->submitWrap, $submit) . $tokenField . '</form>';
    }

    /**
     * @return string
     */
    protected function renderCsrf()
    {
        return '<input type="hidden" name="' . $this->getName() . '[__csrf]" value="' . $this->getCsrfToken() . '">';
    }

    /**
     * @return string
     */
    protected function getCsrfToken()
    {
        $token = $this->session->get('__csrf_token');
        if (!$token) {
            $token = $this->generateCsrfToken();
            $this->session->set('__csrf_token', $token);
        }

        return $token;
    }

    /**
     * @return string
     */
    protected function generateCsrfToken()
    {
        return md5(md5(time() . self::SALT));
    }

    /**
     * @param FormFieldInterface $field
     */
    public function addField(FormFieldInterface $field)
    {
        $field->setForm($this);
        $this->fields[$field->getName()] = $field;
        $this->hasFields = true;
    }

    /**
     * @param FormFieldInterface[] $fields
     */
    public function addFields($fields)
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    /**
     * @param $fieldName
     * @return FormFieldInterface|null
     */
    public function getField($fieldName)
    {
        if (isset($this->fields[$fieldName])) {
            return $this->fields[$fieldName];
        }

        return null;
    }

    /**
     * @param $fieldName
     */
    public function removeField($fieldName)
    {
        if (isset($this->fields[$fieldName])) {
            unset($this->fields[$fieldName]);
        }

        if (count($this->fields) === 0) {
            $this->hasFields = false;
        }
    }

    /**
     * @return string
     */
    protected function requestGetter()
    {
        if ($this->method === RequestInterface::METHOD_POST) {
            return 'request';
        }

        return 'query';
    }

    /**
     * @param FormValidatorInterface $validator
     */
    public function addValidator(FormValidatorInterface $validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        $isInvalid = false;
        $this->validationErrors['__form'] = array();
        foreach ($this->validators as $validator) {
            if (!$validator->validateForm($this)) {
                $isInvalid = true;
                $this->validationErrors['__form'][] = $validator->getValidationErrors();
            }
        }
        foreach ($this->fields as $field) {
            if (!$field->validate()) {
                $isInvalid = true;
                $this->validationErrors[$field->getName()] = $field->getValidationErrors();
            }
        }

        if($this->csrfProtect) {
            if($this->submitedToken !== $this->getCsrfToken()) {
                $this->validationErrors['__form'][] = 'Invalid csrf token submited';
                $isInvalid = true;
            }
        }

        return !$isInvalid;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * @param RepositoryManager $repositoryManager
     */
    public function setRepositoryManager(RepositoryManager $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;
    }

    /**
     * @return RepositoryManager
     */
    public function getRepositoryManager()
    {
        return $this->repositoryManager;
    }

    /**
     * @return UploadedFile[]
     */
    public function getUploadedFiles()
    {
        $files = array();
        foreach ($this->fields as $field) {
            if ($field instanceof FileField) {
                $files[$field->getName()] = $field->getUploadedFile();
            }
        }

        return $files;
    }

    /**
     * @param $array
     * @return array
     */
    protected function valuesToKeys($array)
    {
        $newArray = array();
        foreach ($array as $val) {
            $newArray[$val] = $val;
        }

        return $newArray;
    }
}