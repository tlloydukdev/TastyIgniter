<?php namespace Igniter\SoundAlert\Controllers;

use \Admin\Models\Orders_model as OrdersModel;
use Igniter\Flame\Exception\ApplicationException;

class SoundAlert extends \Admin\Classes\AdminController {

    public function getLastOrderId() {

        $ordersModel = new OrdersModel;
        $order = $ordersModel->where(['processed' => 1, 'status_id' => 1])->orderBy('order_id', 'DESC')->first();
        if($order !== null && $order->count() > 0) {
            echo $order->order_id;
        } else {
            echo 0;
        }
        $this->suppressLayout = TRUE;

    }

}