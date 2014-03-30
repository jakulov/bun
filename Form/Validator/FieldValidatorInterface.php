<?php
namespace Bun\Form\Validator;

use Bun\Form\Field\FormFieldInterface;

/**
 * Interface ValidatorInterface
 *
 * @package Bun\Form\Validator
 */
interface FieldValidatorInterface
{
    public function validateField(FormFieldInterface $field);

    public function getValidationErrors();
}