<?php
namespace Bun\Form\Field;

use Bun\Core\Exception\NotFoundException;
use Bun\Form\FormInterface;
use Bun\Core\Model\ModelInterface;

/**
 * Class SelectField
 *
 * @package Bun\Form\Field
 */
class SelectField extends AbstractFormField
{
    protected $source_data = array();

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @param $data
     * @throws \Bun\Core\Exception\NotFoundException
     */
    public function setData($data)
    {
        if ($this->source_model) {
            $repository = $this->form->getRepositoryManager()->getRepository($this->source_model);
            $value = $repository->find($data);
            if ($value) {
                $this->setValue($value);
            }
            else {
                //throw new NotFoundException('Request params ' . $this->getName() . '=' . $data . ' not found in repository');
                $this->setValue(0);
            }
        }
        else {
            $this->setValue($data);
        }
    }

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form)
    {
        parent::setForm($form);
        $this->source_data = array();
        if ($this->source_model) {
            if ($this->source_default) {
                $this->source_data = $this->source_default;
            }
            $where = $this->source_where ? $this->source_where : array();
            $orderBy = $this->source_order ? $this->source_order : array();

            $repository = $form->getRepositoryManager()->getRepository($this->source_model);
            if ($this->source_find) {
                $data = call_user_func_array(array($repository, $this->source_find), array($where, $orderBy));
            }
            else {
                $data = $repository->findBy($where, $orderBy);
            }
            $name = $this->source_name ? $this->source_name : 'name';
            $value = $this->source_value ? $this->source_value : 'id';
            $nameGetter = 'get' . ucfirst($name);
            $valueGetter = 'get' . ucfirst($value);
            /** @var $object ModelInterface */
            foreach ($data as $object) {
                $this->source_data[$object->$valueGetter()] = $object->$nameGetter();
            }
        }
        elseif ($this->source) {
            $this->source_data = $this->source;
        }
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
        $id = $this->id ? 'id="' . $this->id . '"' : 'id="' . $this->form->getName() . '_' . $this->name . '"';
        $disabled = $this->disabled ? 'disabled' : '';

        $field = '<select name="' . $this->form->getName() . '[' .
            $this->name . ']" ' .
            $readonly .
            $class .
            $required .
            $id .
            $disabled .
            $this->addHtml .
            '>';


        //if($this->isAssoc($this->source_data)) {
        foreach ($this->source_data as $value => $name) {
            if($this->source_model) {
                $valueField = $this->source_value ? $this->source_value : 'id';
                $valueGetter = 'get' . ucfirst($valueField);
                if($this->value instanceof ModelInterface) {
                    $selected = $this->value->$valueGetter() === $value;
                }
                else {
                    $selected = false;
                }
            }
            else {
                $selected = $value === $this->getValue();
            }
            $field .= '<option value="' . $value . '" ' . ($selected ? 'selected' : '') . '>' .
                $name . '</option>';
        }
        /*}
        else {
            foreach ($this->source_data as $name) {
                $field .= '<option '. ($name === $this->getValue() ? 'selected' : '') . '>' .
                    $name . '</option>';
            }
        }*/

        $field .= '</select>';

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
            $field = '<label ' . $class . ' for="' . $id . '">' . $this->label . '</label>' . $field;
        }

        return sprintf($wrap, $field);
    }

    /**
     * @param $arr
     * @return bool
     */
    protected function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}