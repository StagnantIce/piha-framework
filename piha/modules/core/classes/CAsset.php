<?php
/**
* CAsset
* класс для работы со статикой
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/

namespace piha\modules\core\classes;
use piha\CFile;
use piha\modules\core\CCoreModule;
use piha\CAlias;
use piha\CException;
use piha\AClass;

class CAsset extends AClass {

    public function getAssetPath() {
        $assetPath = CAlias::GetPath(CCoreModule::Config('assetPath', '@webroot/assets'));
        if (!file_exists($assetPath)) {
            CFile::MkDir($assetPath);
        }
        return $assetPath;
    }

    public function getPath($name, $file) {
        $path = CAlias::GetPath(array($this->getAssetPath(), $name));
        if (!file_exists($path)) {
            CFile::MkDir($path);
        }
        $path = CAlias::GetPath(array($this->getAssetPath(), $name, $this->id($file)));
        if (!file_exists($path)) {
            CFile::MkDir($path);
        }
        return $path;
    }

    public function getOriginalFile($file) {
        $file = CAlias::GetPath($file);
        if (!file_exists($file)) {
            throw new CException("Asset file {$file} not found");
        }
        return $file;
    }

    public function id($str) {
        return substr(md5($str),0,12);
    }

    public function getAssetFile($file, $ext, $path) {
        $originalFile = $this->getOriginalFile($file);
        $pathName = $this->getPath($path, $originalFile);
        $name = $this->id($file . filemtime($file)) . '.'.$ext;
        $file = CAlias::file($name, $pathName);
        if (!file_exists($file)) {
            if (file_exists($pathName)) {
                array_map('unlink', glob("$pathName/*.*"));
                CFile::Delete($pathName);
                CFile::MkDir($pathName);
            }
            $f = fopen($file, 'w+');
            fputs($f, file_get_contents($originalFile));
            fclose($f);
        }
        if (!file_exists($file)) {
            throw new CException("Error create asset file");
        }
        return $file;
    }

    public function js($file) {
        $file = $this->getAssetFile($file, 'js', 'js');
        $file = str_replace(CAlias::GetPath('@webroot'), '', $file);
        CHtml::create()->script(array('src' => $file, 'type' => 'text/javascript'))->render();
    }

    public function css($file) {
        $file = $this->getAssetFile($file, 'css', 'css');
        $file = str_replace(CAlias::GetPath('@webroot'), '', $file);
        CHtml::create()->link(array('href' => $file, 'type' => 'text/css', 'rel' => 'stylesheet'))->render();
    }
}