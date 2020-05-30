<?php namespace Igniter\OrderDashboard\Controllers;

use AdminMenu;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\OrderDashboard\Models\Orders_model as OrderDashboardModel;

class Overview extends \Admin\Classes\AdminController
{
    private $modelConfig = [
        'orders' => 'Igniter\OrderDashboard\Models\Orders_model'
    ];

    public $implement = [
        'Admin\Actions\ListController',
        'Admin\Actions\FormController',
        'Admin\Actions\LocationAwareController',
        'Admin\Actions\AssigneeController',
    ];

    public $listConfig = [
        'list' => [
            'model' => 'Igniter\OrderDashboard\Models\Orders_model',
            'title' => 'lang:admin::lang.orders.text_title',
            'emptyMessage' => 'lang:admin::lang.orders.text_empty',
            'defaultSort' => ['order_id', 'DESC'],
            'configFile' => 'orders_model',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:admin::lang.orders.text_form_name',
        'model' => 'Igniter\OrderDashboard\Models\Orders_model',
        'request' => 'Admin\Requests\Order',
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'orders/edit/{order_id}',
            'redirectClose' => 'orders',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'orders',
        ],
        'delete' => [
            'redirect' => 'orders',
        ],
        'configFile' => 'orders_model',
    ];

    protected $requiredPermissions = ['Admin.Orders', 'Admin.AssignOrders'];

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('overview', 'sales');            
        
    }

    public function index_onLoadPopup()
    {
        $context = post('context');
        $orderId = (int)post('orderId');

         if (!in_array($context, ['orderPreview']))
             throw new ApplicationException('Invalid type specified');

         if(!isset($orderId) || !is_int($orderId))
            throw new ApplicationException('Invalid or missing OrderId');

         $this->vars['context'] = $context;
         $this->vars['orderId'] = $orderId;

        // $ordersModel = new OrderDashboardModel();
        // $data = $ordersModel->where('order_id', '=', $orderId)->first();

        $model = $this->formFindModelObject($orderId);

        $this->vars['model'] = $model;

        return ['#previewModalContent' => $this->makePartial('preview_popup')];
    }

    public function invoice($context, $recordId = null)
    {
        $model = $this->formFindModelObject($recordId);

        if (!$model->hasInvoice())
            throw new ApplicationException('Invoice has not yet been generated');

        $this->vars['model'] = $model;

        $this->suppressLayout = TRUE;
    }

    public function formExtendFieldsBefore($form)
    {
        if (!array_key_exists('invoice_number', $form->tabs['fields']))
            return;

        if (!$form->model->hasInvoice()) {
            array_pull($form->tabs['fields']['invoice_number'], 'addonRight');
        }
        else {
            $form->tabs['fields']['invoice_number']['addonRight']['attributes']['href'] = admin_url('orders/invoice/'.$form->model->getKey());
        }
    }

    public function formExtendQuery($query)
    {
        $query->with([
            'status_history' => function ($q) {
                $q->orderBy('date_added', 'desc');
            },
        ]);
    }
}