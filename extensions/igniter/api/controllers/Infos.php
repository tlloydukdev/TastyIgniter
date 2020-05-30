<?php

namespace Igniter\Api\Controllers;

use AdminMenu;

// Local Import
use Igniter\Api\Services\TastyJwt;
use Igniter\Api\Services\TastyJson;


// Libary Import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DateTime;

/**
 * Infos Admin Controller
 */

class Infos extends \Admin\Classes\AdminController {

    private $modelConfig = [
        'user' => 'Admin\Models\Customers_model',
        'location' => 'Admin\Models\Locations_model',
        'locationable' => 'Igniter\Api\Models\Locationable',
        'address' => 'Admin\Models\Addresses_model',
        'category' => 'Admin\Models\Categories_model',
        'menuCategory' => 'Admin\Models\Menu_categories_model',
        'menu' => 'Admin\Models\Menus_model',
        'locationArea' => 'Admin\Models\Location_areas_model',
        'menuOption' => 'Admin\Models\Menu_item_options_model',
        'coupon' => 'Admin\Models\Coupons_model'
    ];

    private $userModel;
    private $locationModel;
    private $locationAreaModel;
    private $addressModel;
    private $categoryModel;
    private $menuCategoryModel;
    private $menuOptionModel;
    private $menuModel;
    private $couponModel;
    private $locationableModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new $this->modelConfig['user'];
        $this->locationModel =  new $this->modelConfig['location'];
        $this->locationAreaModel = new $this->modelConfig['locationArea'];
        $this->addressModel = new $this->modelConfig['address'];
        $this->categoryModel = new $this->modelConfig['category'];
        $this->menuCategoryModel = new $this->modelConfig['menuCategory'];
        $this->menuOptionModel = new $this->modelConfig['menuOption'];
        $this->menuModel = new $this->modelConfig['menu'];
        $this->couponModel = new $this->modelConfig['coupon'];
        $this->locationableModel = new $this->modelConfig['locationable'];
    }

    public function menu(Request $request) {
        if (!TastyJwt::instance()->validateToken($request))
            abort(400, lang('igniter.api::lang.auth.alert_token_expired'));

        try {
            $today = date("Y-m-d");
            $weekDay = date('N', strtotime($today));
            $locationArea = $this->locationAreaModel->where('area_id', $request['user']['areaId'])->first();
            $location = $this->locationModel->where('location_id', $locationArea->location_id)->first();
            if($locationArea->conditions[0]['amount'] == '0.00' && $locationArea->conditions[0]['total'] == '0.00') {
                $delivery = 'Free on all orders';
            } else {
                $delivery = '£' . $locationArea->conditions[0]['amount'] . ' below ' . '£' . $locationArea->conditions[0]['total'];
            }
            if($location->options['hours']['opening']['flexible'][$weekDay]['status'] == '1') {
                $openTime = $location->options['hours']['opening']['open'] . '-' . $location->options['hours']['opening']['close'];
            } else {
                $openTime = $location->options['hours']['opening']['flexible'][$weekDay]['open'] . '-' . $location->options['hours']['opening']['flexible'][$weekDay]['close'];
            }
            $specailsCategoryId = $this->categoryModel->where('permalink_slug', 'specials')->first()->category_id;


            $customSpecials = $this->menuCategoryModel::with('menu')->where('category_id', $specailsCategoryId)->get();
            $specials = array();
            if ($this->locationableModel->where('location_id', $location->location_id)->where('locationable_type', 'categories')->where('locationable_id', $specailsCategoryId)->first()) {
                foreach ($customSpecials as $special) {
                    if ($this->locationableModel->where('locationable_type', 'menus')->where('locationable_id', $special->menu_id)->first()) {
                        array_push($specials, $special);
                    }
                }    
            }

            $categories = $this->categoryModel->where('category_id', '<>', $specailsCategoryId)->orderBy('priority', 'ASC')->get();
            $categoryDetails = $this->categoryModel::with('menus')->where('category_id', '<>', $specailsCategoryId)->orderBy('priority', 'ASC')->get();
            $allCoupons = $this->couponModel->get();
            $coupons = array();
            foreach ($allCoupons as $value) {
                $coupon = [
                    'code' => $value->code,
                    'type' => $value->type,
                    'discount' => $value->discount,
                ];
                array_push($coupons, $coupon);
            }

            $response = [
                'locationId' => $location->location_id,
                'locationName' => $location->location_name,
                'delivery' => $delivery ,
                'openTime' => $openTime,
                'specials' => $specials,
                'categories' => $categories,
                'categoryDetails' => $categoryDetails,
                'deliveryAmount' => $locationArea->conditions[0]['amount'],
                'deliveryTotal' => $locationArea->conditions[0]['total'],
                'coupons' => $coupons
            ];
        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function menuDetail(Request $request) {
        if (!TastyJwt::instance()->validateToken($request))
            abort(400, lang('igniter.api::lang.auth.alert_token_expired'));

        try {
            $response['menu'] = $this->menuModel->where('menu_id', $request->id)->first();
            $response['options'] = $this->menuOptionModel::with('option_values')->with('option')->where('menu_id', $request->id)->get();
        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }
}
