<?php namespace Igniter\SoundAlert;

use System\Classes\BaseExtension;
use Event;

class Extension extends BaseExtension
{
    public function boot() {
        Event::listen('admin.controller.beforeResponse', function ($controller, $action, $params) {
            $controller->addJs('$/igniter/soundalert/assets/js/soundalert.js', 'soundalert-js');
        });
    }

}
