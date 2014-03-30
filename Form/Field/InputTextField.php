<?php
namespace Bun\Form\Field;

/**
 * Class InputTextField
 *
 * @package Bun\Form\Field
 */
class InputTextField extends AbstractFormField
{
    protected $options = array(
        'name',
        'label',
        'class',
        'value',
        'required',
        'requiredMsg',
        'id',
        'autocomplete',
        'placeholder',
        'readonly',
        'disabled',
        'default',
        'addHtml',
        'bootstrap',
        'addon',
    );

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @param $data
     * @throws \Bun\Form\FormException
     */
    public function setData($data)
    {
        $this->value = $data;
    }

    /**
     * @param string $wrap
     * @return string
     */
    public function render($wrap = '%s')
    {
        $readonly = $this->readonly ? 'readonly="true"' : '';
        $required = $this->required ? 'required="true"' : '';
        $placeholder = $this->placeholder ? 'placeholder="' . $this->placeholder . '"' : '';
        $autocomplete = $this->autocomplete ? 'autocomplete="' . $this->autocomplete . '"' : '';
        $class = $this->class ? 'class="' . $this->class . '"' : '';
        $idVal = $this->id ? $this->id : $this->form->getName() . '_' . $this->name;
        $id = 'id="' . $idVal . '"';
        $disabled = $this->disabled ? 'disabled' : '';

        $field = '<input type="text" name="' . $this->form->getName() . '[' .
            $this->name . ']" value="' .
            $this->getValue() . '"' .
            $readonly . ' ' .
            $required . ' ' .
            $placeholder . ' ' .
            $autocomplete . ' ' .
            $id . ' ' .
            $class . ' ' .
            $disabled . ' ' .
            $this->addHtml . '>';

        if($this->addon) {
            $field .= $this->addon;
        }
        if ($this->validationErrors) {
            $field .= '<span class="alert alert-danger">' . join(',', $this->validationErrors) . '</span>';
        }
        if ($this->bootstrap) {
            $field = sprintf($this->bootstrap['wrap'], $field);
        }
        if ($this->label) {
            $class = '';
            if ($this->bootstrap) {
                $class = 'class="' . $this->bootstrap['label'] . '"';
            }
            $field = '<label ' . $class . ' for="' . $idVal . '">' . $this->label . '</label>' . $field;
        }

        return sprintf($wrap, $field);
    }
}