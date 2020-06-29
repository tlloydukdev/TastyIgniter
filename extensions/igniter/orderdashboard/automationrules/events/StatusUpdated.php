<?php

namespace Igniter\OrderDashboard\AutomationRules\Events;

use Igniter\Automation\Classes\BaseEvent;

class StatusUpdated extends BaseEvent
{
    public function eventDetails()
    {
        return [
            'name' => 'Order Status Updated',
            'description' => 'When an order status is updated through admin',
            'group' => 'order',
        ];
    }

    public static function makeParamsFromEvent(array $args, $eventName = null)
    {
        $order = array_get($args, 0);
        $params = [];

        if(!isset($order['order_id']))
            throw new ApplicationException('Invalid Status Updated event. No order ID.');
        
        $params['order'] = $order;
        
        return $params;
    }
}