<?php

use R3m\Io\Module\Dir;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Data;
use R3m\Io\Module\File;
use R3m\Io\App;

use R3m\Io\Exception\FileWriteException;

/**
 * @throws Exception
 */
function function_zip_extract(Parse $parse, Data $data){
    $object = $parse->object();
    $source = App::parameter($object, 'extract', 1);
    $target = App::parameter($object, 'extract', 2);
    if(empty($target)){
        $target = getcwd();
    }
    if(!File::exist($source)){
        echo 'Cannot find source file...';
        return;
    }
    if(
        File::exist($target) &&
        !Dir::is($target)
    ){
        echo 'Target exists already...';
        return;
    }
    $zip = new \ZipArchive();
    $zip->open($source);
    $dirList = array();
    $fileList = array();
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $node = new stdClass();
        $node->name = $zip->getNameIndex($i);
        if(substr($node->name, -1) == '/'){
            $node->type = 'dir';
        } else {
            $node->type = 'file';
        }
        $node->index = $i;
        $node->url = $target . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $node->name);
        if($node->type == 'dir'){
            $dirList[] = $node;
        } else {
            $fileList[] = $node;
        }
    }
    foreach($dirList as $dir){
        if(Dir::is($dir->url) === false){
            Dir::create($dir->url);
        }
    }
    foreach($fileList as $node){
        $stats = $zip->statIndex($node->index);
        $dir = Dir::name($node->url);
        $object->logger($object->config('project.log.node'))->info('dir', [ $dir ]);
        if(File::exist($dir) && !Dir::is($dir)){
            File::delete($dir);
            Dir::create($dir);
        }
        if(File::exist($dir) === false){
            $object->logger($object->config('project.log.node'))->info('dir create', [ $dir ]);
            Dir::create($dir);
        }
        if(File::exist($node->url)){
            File::delete($node->url);
        }
        try {
            $object->logger($object->config('project.log.node'))->info('url, index', [ $node ]);
            $data = $zip->getFromIndex($node->index);
            if($data){
                $write = File::write($node->url, $data);
            } else {
                $object->logger($object->config('project.log.node'))->info('cannot get from index', [ $node ]);
                $write = false;
            }
            if($write !== false){
                File::chmod($node->url, File::CHMOD);
                touch($node->url, $stats['mtime']);
            } else {
                throw new Exception('Cannot write file: ' . $node->url);
            }
        } catch (FileWriteException $exception) {
            $zip->close();
            echo $exception->getMessage() . PHP_EOL;
            return;
        }
    }
    $zip->close();
    echo 'Zip archive extracted in: ' . $target;
}
