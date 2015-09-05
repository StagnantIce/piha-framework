<?php

class CCustomModule extends AModule {

    public function getRoot() {
        return __DIR__;
    }

    public function getPaths() {
        return array('classes');
    }

    public static function GetAdminMenu() {
        return array();
    }
}

return CCustomModule::Register();