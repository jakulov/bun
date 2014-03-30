<?php
namespace Bun\Core\Model;

/**
 * Class ModelArray
 *
 * @package Bun\Core\Model
 */
class ModelArray
{
    /**
     * @param ModelInterface[] $array
     * @param ModelInterface $object
     * @return bool|int|string
     */
    public static function contains($array, ModelInterface $object)
    {
        $foundKey = false;
        /*foreach ($array as $key => $item) {
            if($object->isNewObject() && $item->isNewObject()) {
                if (spl_object_hash($object) === spl_object_hash($item)) {
                    $foundKey = $key;
                    break;
                }
            }
            else {
                if ($item->getId() === $object->getId()) {
                    $foundKey = $key;
                    break;
                }
            }
        }*/
        foreach ($array as $key => $item) {
            if (spl_object_hash($object) === spl_object_hash($item)) {
                $foundKey = $key;
                break;
            }
        }

        return $foundKey;
    }
}