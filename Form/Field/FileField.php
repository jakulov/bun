<?php
namespace Bun\Form\Field;

use Bun\Core\File\UploadedFile;

/**
 * Class FileField
 *
 * @package Bun\Form\Field
 */
class FileField extends AbstractFormField
{
    /** @var UploadedFile */
    protected $file;
    protected $uploadedFileName;

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @param string $wrap
     * @return string
     */
    public function render($wrap = '%s')
    {
        $class = $this->class ? 'class="' . $this->class . '"' : '';
        $idVal = $this->id ? $this->id : $this->form->getName() . '_' . $this->name;
        $id = 'id="' . $idVal . '"';
        $disabled = $this->disabled ? 'disabled' : '';

        $field = '<input type="file" name="__file_' . $this->name . '" ' .
            $id . ' ' .
            $class . ' ' .
            $disabled . ' ' .
            $this->addHtml . '>';

        $field .= '<input type="hidden" name="' . $this->form->getName() . '[' .
            $this->name . ']" ' .
            'value="' . $this->getValue() . '"' . '>';
        if ($this->value) {
            $field .= '<p>' . $this->uploadedFileName . '</p>';
        }

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

    /**
     * @param $data
     */
    public function setData($data)
    {
        if (isset($_FILES['__file_' . $this->name])) {
            $fileInfo = $_FILES['__file_' . $this->name];
            if ($fileInfo['error'] === UPLOAD_ERR_OK) {
                $this->file = new UploadedFile($fileInfo['tmp_name'], false, $fileInfo['name']);
                $this->uploadedFileName = $this->file->getName();
                $this->value = $this->file->getFullName();
            }
        }
        elseif ($data) {
            $this->file = new UploadedFile($data);
            $this->value = $this->file->getFullName();
            $this->uploadedFileName = $this->file->getName();
        }
    }

    /**
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->file;
    }
}