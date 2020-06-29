<?php

namespace Igniter\PushOrder\AutomationRules\Actions;

use Admin\Models\Staff_groups_model;
use Admin\Traits\Assignable;
use Igniter\Automation\Classes\BaseAction;
use Igniter\Flame\Exception\ApplicationException;
use Log;

class SendPush extends BaseAction
{
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
        Log::error("WHAT IS GOING ON");
        
       
        // if (!$groupId = $this->model->staff_group_id)
        //     throw new ApplicationException('Missing valid staff group to assign to.');

        // $assignable = array_get($params, 'order', array_get($params, 'reservation'));

        // if (!in_array(Assignable::class, class_uses_recursive(get_class($assignable))))
        //     return;

        // if (!$assigneeGroup = Staff_groups_model::find($groupId))
        //     throw new ApplicationException('Invalid staff group to assign to.');

        // $assignable->assignTo($assigneeGroup);
    }
}