<?php
namespace Bun\Form\Field;

use Bun\Form\FormInterface;
use Bun\Form\Validator\FieldValidatorInterface;

/**
 * Interface FormFieldInterface
 *
 * @package Bun\Form\Field
 */
interface FormFieldInterface
{
    public function getValue();

    public function setOptions($options);

    public function getOption($name);

    public function setOption($name, $value);

    public function getName();

    public function isRequired();

    public function setData($data);

    public function setValue($value);

    public function render($wrap);

    public function setForm(FormInterface $form);

    public function addValidator(FieldValidatorInterface $validator);

    public function validate();

    public function getValidationErrors();
}