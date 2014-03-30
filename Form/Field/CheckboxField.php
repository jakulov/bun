<?php
namespace Bun\Form\Field;

/**
 * Class CheckboxField
 *
 * @package Bun\Form\Field
 */
class CheckboxField extends AbstractFormField
{
    protected $default = '';

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
        $class = $this->class ? 'class="' . $this->class . '"' : '';
        $idVal = $this->id ? $this->id : $this->form->getName() . '_' . $this->name;
        $id = 'id="' . $idVal . '"';
        $disabled = $this->disabled ? 'disabled' : '';
        $checked = $this->value == $this->default ? 'checked' : '';

        $field = '<input type="checkbox" name="' . $this->form->getName() . '[' .
            $this->name . ']" value="' .
            $this->default . '"' .
            $readonly . ' ' .
            $required . ' ' .
            $id . ' ' .
            $class . ' ' .
            $disabled . ' ' .
            $checked . ' ' .
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