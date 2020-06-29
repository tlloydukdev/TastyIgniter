<?php namespace Igniter\PushOrder;

use System\Classes\BaseExtension;
class Extension extends BaseExtension
{

    public function registerAutomationRules()
    {
        return [
            'actions' => [
                \Igniter\PushOrder\AutomationRules\Actions\SendPush::class,
            ]
        ];
    }

}