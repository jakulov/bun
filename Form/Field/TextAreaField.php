<?php
namespace Bun\Form\Field;

/**
 * Class TextAreaField
 *
 * @package Bun\Form\Field
 */
class TextAreaField extends InputTextField
{
    /**
     * @param string $wrap
     * @return string
     */
    public function render($wrap = '%s')
    {
        $readonly = $this->readonly ? 'readonly="true"' : '';
        $required = $this->required ? 'required="true"' : '';
        $placeholder = $this->placeholder ? 'placeholder="' . $this->placeholder . '"' : '';
        $class = $this->class ? 'class="' . $this->class . '"' : '';
        $idVal = $this->id ? $this->id : $this->form->getName() . '_' . $this->name;
        $id = 'id="' . $idVal . '"';
        $disabled = $this->disabled ? 'disabled' : '';

        $field = '<textarea name="' . $this->form->getName() . '[' .
            $this->name . ']" ' .
            $readonly . ' ' .
            $required . ' ' .
            $placeholder . ' ' .
            $id . ' ' .
            $class . ' ' .
            $disabled . ' ' .
            $this->addHtml . '>' . $this->getValue() . '</textarea>';

        if ($this->validationErrors) {
            $field .= '<span>' . join(',', $this->validationErrors) . '</span>';
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