<?php
namespace Bun\Core\Form;

use Bun\Core\Model\User;
use Bun\Form\AbstractForm;
use Bun\Form\Field\InputTextField;
use Bun\Form\Field\SelectField;

/**
 * Class CreateUserForm
 *
 * @package Bun\Form\Form
 */
class CreateUserForm extends AbstractForm
{
    /**
     * Form create user
     */
    public function build()
    {
        $this->action = '/bun/create';
        $this->submitValue = 'Создать пользователя';
        $this->fieldWrap = '<div>%s</div>';

        $fields = array(
            new InputTextField(array(
                'name' => 'name',
                'label' => 'Имя: ',
                'value' => '',
                'placeholder' => 'Имя пользователя',
                'class' => 'required',
                'required' => true,
                'requiredMsg' => 'Введите имя пользователя',
            )),
            new SelectField(array(
                'name' => 'gender',
                'label' => 'Пол',
                'value' => '',
                'source' => User::$genders,
                'class' => 'required',
                'required' => true,
                'requiredMsg' => 'Укажите пол пользователя'
            )),
            new SelectField(array(
                'name' => 'group',
                'label' => 'Группа',
                'value' => '',
                'source_model' => 'Bun\\Core\\Model\\UserGroup',
            )),
        );

        $this->addFields($fields);
    }
}