<?php
/**
 * @author          Remco van der Velde
 * @since           2020-09-18
 * @copyright       Remco van der Velde
 * @license         MIT
 * @version         1.0
 * @changeLog
 *     -            all
 */

use R3m\Io\Module\Database;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Exception\FileWriteException;

/**
 * @throws ObjectException
 * @throws FileWriteException
 * @throws \Doctrine\ORM\Exception\ORMException
 * @throws \Doctrine\ORM\ORMException
 */
function validate_is_unique_mysql(R3m\Io\App $object, $field='', $argument=''){
    if($object->request('has', 'node.' . $field)){
        $string = strtolower($object->request('node.' . $field));
    }
    elseif($object->request('has', 'node_' . $field)) {
        $string = $object->request('node_' . $field);
    }
    else {
        $string = strtolower($object->request($field));
    }
    $table = false;
    $field = false;
    if(property_exists($argument, 'table')){
        $table = $argument->table;
    }
    if(property_exists($argument, 'field')){
        $field = $argument->field;
    }
    if(
        $table &&
        $field
    ){
        $entityManager = Database::entityManager($object, []);
        $repository = $entityManager->getRepository($table);
        $criteria[$field] = $string;
        $record = $repository->findOneBy($criteria);
        if($record === null){
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }

}
