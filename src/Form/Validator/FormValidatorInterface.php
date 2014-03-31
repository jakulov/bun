<?php
namespace Bun\Form\Validator;

use Bun\Form\FormInterface;

/**
 * Interface FormValidatorInterface
 *
 * @package Bun\Form\Validator
 */
interface FormValidatorInterface
{
    public function getValidationErrors();

    public function validateForm(FormInterface $form);
}