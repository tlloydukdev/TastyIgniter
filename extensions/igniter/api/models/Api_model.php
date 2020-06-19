<?php

namespace Igniter\Api\Models;

class Api_model extends \Admin\models\Menus_model
{
    public function getMenuImageUrlAttribute($value) {
        return "BOLLOCKS";

        // $thumb=$this->getMedia('thumb');
        // $firstOnly = true;
        // $menuItemUrl = '#';
        // foreach ($thumb as $item) {
        //     if ($firstOnly) {
        //             $baseUrl = $item->getPublicPath(); // Config::get('system.assets.attachment.path');
        //             $menuItemUrl = $baseUrl . $item->getPartitionDirectory() . '/' . $item->getAttribute('name');
        //             $firstOnly = false;
        //     }
        // }
        // return $menuItemUrl;
    }
}