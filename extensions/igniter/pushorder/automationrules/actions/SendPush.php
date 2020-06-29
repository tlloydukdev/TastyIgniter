<?php

namespace Igniter\PushOrder\AutomationRules\Actions;

use \Admin\Models\Orders_model as OrdersModel;
use \Admin\Models\Statuses_model as StatusModel;
use Igniter\Api\Models\CustomerPush as PushModel;
use Igniter\Automation\Classes\BaseAction;
use Igniter\Flame\Exception\ApplicationException;
use GuzzleHttp\Client;
use Log;

class SendPush extends BaseAction
{
    const FCM_API_URL = 'https://fcm.googleapis.com/fcm/send';
    const SECRET_KEY = 'AAAAXQvio0c:APA91bFZEADgSVw0foVAudUyZUDKLXiN7odomXff2eyrz2f4e0bsazD3hb64s95x_bNSd4lHVv8Ebco_6v73GUKbUNKD4dhH0KtbMvy-2m4et3GwUpN9Z0ZakaiqwNd9y_-iff1mNFgZ';

    public function actionDetails()
    {
        return [
            'name' => 'Send Push Notification',
            'description' => 'Send a push notification to the customer who placed the order',
        ];
    }

    public function defineFormFields()
    {
        // return [
        //     'fields' => [
        //         'staff_group_id' => [
        //             'label' => 'lang:igniter.automation::default.label_assign_to_staff_group',
        //             'type' => 'select',
        //             'options' => ['Admin\Models\Staff_groups_model', 'getDropdownOptions'],
        //         ],
        //     ],
        // ];
    }

    public function triggerAction($params)
    {
        //Log::error("Sending push notification", $params);
        if (!isset($params['order']['order_id']))
             throw new ApplicationException('Missing order ID. Unable to send push.');
        
        if (!isset($params['order']['status_id']))
             throw new ApplicationException('Missing status ID. Unable to send push.');
        
        $order_id = $params['order']['order_id'];
        $status_id = $params['order']['order_id'];
        $status_comment = "";

        if(isset($params['order']['comment']) && strlen($params['order']['comment']) > 0) {
            $status_comment = $params['order']['comment'];
        } else {
            $statusModel = new StatusModel;
            $status = $statusModel->find($status_id);
            if($status) {
                $status_comment = $status->status_comment;
            }
        }

        $ordersModel = new OrdersModel;
        $order = $ordersModel->find($order_id);

        if($order) {
            //Log::error($order);
            
            // TODO: get this from Push Action UI
            $notification = new \stdClass();
            $notification->title = setting('site_name', 'Your Site');
            $notification->body = $status_comment;
            $notification->sound = "default";
            $notification->click_action = "FCM_PLUGIN_ACTIVITY";
            $notification->icon = "fcm_push_icon";
            $data = new \stdClass();
            $data->title = $notification->title;
            $data->body = $notification->body;
                        
            $pushModel = new PushModel;
            $pushSettings = $pushModel->where('customer_id', $order->customer_id)->get();
            if($pushSettings->count()) {

                $httpClient = new Client();

                foreach($pushSettings as $device) {
                    $payload = new \stdClass();
                    $payload->priority = "high";
                    $payload->restricted_package_name = "";
                    $payload->to = $device->device_token;
                    $payload->notification = $notification;
                    $payload->data = $data;
                    //Log::error("Sending push to: ", ['payload' => json_encode($payload), 'token' => $device->device_token, 'status_comment', $status_comment, 'url' => static::FCM_API_URL]);
                    $httpResponse = $httpClient->post(static::FCM_API_URL, [
                        'headers' => [
                            'content-type' => 'application/json',
                            'Authorization' => 'Bearer ' . static::SECRET_KEY                            
                        ],
                        'json' => $payload
                    ]);
                    $statusCode = $httpResponse->getStatusCode();
                    if($statusCode == 200) {
                        // successful request
                        $responseData = json_decode($httpResponse->getBody(), true);
                        if($responseData['success']) {
                            Log::error("Sent push notification for order", ['order_id' => $order_id]);
                        } else {
                            $errorMsg = "Unknown Error";
                            if(isset($responseData['results'][0]['error'])) {
                                $errorMsg = $responseData['results'][0]['error'];
                            }
                            Log::error("Failed to send push notification, device error", ['order_id' => $order_id, 'error' => $errorMsg]);
                        }
                    } else {
                        Log::error("Failed to send push notification, HTTP error", ['order_id' => $order_id, 'response' => $statusCode, 'token' => $device->device_token]);
                    }
                }
            }
        }
         
    }
}