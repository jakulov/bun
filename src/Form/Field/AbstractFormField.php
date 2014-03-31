<?php
namespace Bun\Form\Field;

use Bun\Form\FormInterface;
use Bun\Form\Validator\FieldValidatorInterface;

/**
 * Class AbstractFormField
 *
 * @package Bun\Form\Field
 */
abstract class AbstractFormField implements FormFieldInterface
{
    protected $requiredMsg = 'Field is required';
    /** @var FormInterface */
    protected $form;
    /** @var FieldValidatorInterface[] */
    protected $validators = array();
    protected $validationErrors = array();

    protected $name = 'field';
    protected $required;
    protected $label;
    protected $value = null;
    protected $source;
    protected $source_name;
    protected $source_value;
    protected $source_where;
    protected $source_order;
    protected $source_model;
    protected $source_find;
    protected $source_default;
    protected $autocomplete;
    protected $id;
    protected $placeholder;
    protected $readonly;
    protected $disabled;
    protected $default;
    protected $addHtml = '';
    protected $class = '';
    protected $bootstrap;
    protected $addon;
    /** @var array */
    protected $options = array(
        'name',
        'value',
        'label',
        'required',
        'requiredMsg',
        'id',
        'autocomplete',
        'placeholder',
        'readonly',
        'disabled',
        'default',
        'addHtml',
        'source',
        'source_where',
        'source_order',
        'source_model',
        'source_name',
        'source_value',
        'source_find',
        'source_default',
        'class',
        'bootstrap',
        'addon',
    );

    abstract public function __construct($options = array());

    /**
     * @param FieldValidatorInterface $validator
     */
    public function addValidator(FieldValidatorInterface $validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * @param $options
     */
    public function setOptions($options)
    {
        $thisClass = get_class($this);
        foreach ($options as $name => $value) {
            if (in_array($name, $this->options)) {
                if (property_exists($thisClass, $name)) {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getOption($name)
    {
        if (in_array($name, $this->options)) {
            if (property_exists(get_class($this), $name)) {
                return $this->$name;
            }
        }

        return null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setOption($name, $value)
    {
        if (isset($this->options[$name])) {
            if (property_exists(get_class($this), $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * @return bool
     */
    public function validate()
    {
        $valid = false;
        if ($this->isRequired() && ($this->getValue() === '' || $this->getValue() === null)) {
            $this->validationErrors[] = $this->requiredMsg;

            return $valid;
        }
        $isInvalid = false;
        if (count($this->validators) > 0) {
            foreach ($this->validators as $validator) {
                $valid = $validator->validateField($this);
                if (!$valid) {
                    $isInvalid = true;
                }
            }
        }

        return !$isInvalid;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        foreach ($this->validators as $validator) {
            $errors = $validator->getValidationErrors();
            foreach ($errors as $err) {
                $this->validationErrors[] = $err;
            }
        }

        return $this->validationErrors;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}