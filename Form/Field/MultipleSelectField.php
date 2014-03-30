<?php
namespace Bun\Form\Field;

/**
 * Class MultipleSelectField
 *
 * @package Bun\Form\Field
 */
class MultipleSelectField extends SelectField
{
    /**
     * @param string $wrap
     * @return mixed|string
     */
    public function render($wrap = '%s')
    {
        $field = parent::render($wrap);

        return str_replace('<select name=', '<select multiple="true" name=', $field);
    }
}