<?php namespace Igniter\OrderDashboard;

use Event;
use System\Classes\BaseExtension;

/**
 * OrderDashboard Extension Information File
 */
class Extension extends BaseExtension
{
  
    /**
     * Register method, called when the extension is first registered.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
       
    }

    /**
     * Registers any front-end components implemented in this extension.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Igniter\OrderDashboard\Components\PreviewOrder' => [
                'code' => 'previewOrder',
                'name' => 'Order Preview',
                'description' => 'Order preview component'
            ]
        ];

    }

    /**
     * Registers any admin permissions used by this extension.
     *
     * @return array
     */
    public function registerPermissions()
    {
// Remove this line and uncomment block to activate
        return [
//            'Igniter.OrderDashboard.SomePermission' => [
//                'description' => 'Some permission',
//                'group' => 'module',
//            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'sales' => [
                'child' => [
                    'overview' => [
                        'priority' => 5,
                        'href' => admin_url('igniter/orderdashboard/overview'),
                        'class' => 'overview',
                        'title' => 'Overview',
                        'permission' => 'Igniter.OrderDashboard',
                    ]
                ],
            ],
        ];
    }

}
