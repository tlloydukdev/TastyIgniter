<?php namespace Igniter\OrderDashboard\Controllers;

use AdminMenu;
use Admin\Traits\ListExtendable;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\OrderDashboard\Models\Orders_model as OrderDashboardModel;
use Igniter\OrderDashboard\Controllers\Overview as OverviewController;

class GroupedOrders extends \Admin\Classes\AdminController
{

    use ListExtendable;

    protected $primaryAlias = 'groupedlist';

    private $modelConfig = [
        'orders' => 'Igniter\OrderDashboard\Models\Orders_model'
    ];

    public $implement = [
        'Admin\Actions\ListController',
       'Admin\Actions\LocationAwareController',
        'Igniter\OrderDashboard\Actions\GroupedListController'       
    ];

    public $listConfig = [
        'groupedlist' => [
            'model' => 'Igniter\OrderDashboard\Models\Orders_model',
            'title' => 'Grouped Orders',
            'emptyMessage' => 'lang:admin::lang.orders.text_empty',
            'showCheckboxes' => FALSE,
            'defaultSort' => ['order_id', 'DESC'],
            'configFile' => 'grouped_orders_model',
        ],
    ];

    protected $requiredPermissions = ['Admin.Orders', 'Admin.AssignOrders'];

    public function __construct()
    {
        parent::__construct();
        $alias = $this->primaryAlias;

        ///$listConfig = $this->getListConfig();

        // $modelClass = $listConfig['model'];
        // $model = new $modelClass;
        // unset($listConfig['model']);
        //$model = $this->listExtendModel($model, $alias);

        // Prep the list widget config
        // $requiredConfig = ['groupedlist'];
        // $configFile = $listConfig['configFile'];
        // $modelConfig = $this->loadConfig($configFile, $requiredConfig, 'groupedlist');

        // $columnConfig['columns'] = $modelConfig['columns'];
        // $columnConfig['model'] = $model;
        // $columnConfig['alias'] = $alias;


        // $widget = $this->makeWidget('Igniter\OrderDashboard\Widgets\GroupedLists', array_merge($columnConfig, $listConfig));

        AdminMenu::setContext('grouped', 'sales');            
        
    }

    /**
     * Returns the configuration used by this behavior.
     *
     * @param null $alias
     *
     * @return \Admin\Classes\BaseWidget
     */
    public function getListConfig($alias = null)
    {

        if (!$alias) {
            $alias = $this->primaryAlias;
        }

        if (!$listConfig = array_get($this->listConfig, $alias)) {
            $listConfig = $this->listConfig[$alias] = $this->makeConfig($this->listConfig[$alias], $this->requiredConfig);
        }

        return $listConfig;
    }

    // public function print($context, $recordId = null)
    // {
    //     $this->suppressLayout = TRUE;
    //     $data['model'] = $this->formFindModelObject($recordId);        
        
    //     $pdf = PDF::loadView('pdf_view', $data);  
    //     return $pdf->download('order' . $recordId . '.pdf');

        
    // }

    public function index_onLoadPopup() {
        $context = post('context');
        $orderId = (int)post('orderId');
        
        $oc = new OverviewController();

        return ['#previewModalContent2' => $oc->previewModalContent($context, $orderId)];

    }
    // {
    //     $context = post('context');
    //     $orderId = (int)post('orderId');

    //      if (!in_array($context, ['orderPreview']))
    //          throw new ApplicationException('Invalid type specified');

    //      if(!isset($orderId) || !is_int($orderId))
    //         throw new ApplicationException('Invalid or missing OrderId');

    //      $this->vars['context'] = $context;
    //      $this->vars['orderId'] = $orderId;

    //     // $ordersModel = new OrderDashboardModel();
    //     // $data = $ordersModel->where('order_id', '=', $orderId)->first();

    //     $model = $this->formFindModelObject($orderId);

    //     $this->vars['model'] = $model;

    //     return ['#previewModalContent' => $this->makePartial('preview_popup')];
    // }

    // public function invoice($context, $recordId = null)
    // {
    //     $model = $this->formFindModelObject($recordId);

    //     if (!$model->hasInvoice())
    //         throw new ApplicationException('Invoice has not yet been generated');

    //     $this->vars['model'] = $model;

    //     $this->suppressLayout = TRUE;
    // }

    // public function formExtendFieldsBefore($form)
    // {
    //     if (!array_key_exists('invoice_number', $form->tabs['fields']))
    //         return;

    //     if (!$form->model->hasInvoice()) {
    //         array_pull($form->tabs['fields']['invoice_number'], 'addonRight');
    //     }
    //     else {
    //         $form->tabs['fields']['invoice_number']['addonRight']['attributes']['href'] = admin_url('orders/invoice/'.$form->model->getKey());
    //     }
    // }

    // public function formExtendQuery($query)
    // {
    //     $query->with([
    //         'status_history' => function ($q) {
    //             $q->orderBy('date_added', 'desc');
    //         },
    //     ]);
    // }
}