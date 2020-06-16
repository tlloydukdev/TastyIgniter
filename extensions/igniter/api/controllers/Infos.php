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

require_once(__DIR__ . '/../vendor/stripe-php-7.36.1/init.php');
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
        'coupon' => 'Admin\Models\Coupons_model',
        'order' => 'Admin\Models\Orders_model',
        'status' => 'Admin\Models\Statuses_model',
        'favorite' => 'Igniter\Api\Models\Favourite',
        'page' => 'Igniter\Api\Models\Page',
        'customerSetting' => 'Igniter\Api\Models\CustomerSetting',
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
    private $orderModel;
    private $statusModel;
    private $favoriteModel;
    private $pageModel;
    private $customerSettingModel;

    private $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    private $stripe;

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
        $this->orderModel = new $this->modelConfig['order'];
        $this->statusModel = new $this->modelConfig['status'];
        $this->favoriteModel = new $this->modelConfig['favorite'];
        $this->pageModel = new $this->modelConfig['page'];
        $this->customerSettingModel = new $this->modelConfig['customerSetting'];

        $this->stripe = new \Stripe\StripeClient(config('api.stripe_key_test_secret'));
    }

    public function menu(Request $request) {
        try {
            $currentWeekDay = ((int)date('w') + 7 - 1) % 7;
            $locationArea = $this->locationAreaModel->where('area_id', $request['user']['areaId'])->first();
            $location = $this->locationModel->where('location_id', $request['user']['locationId'])->first();
            if($locationArea->conditions[0]['amount'] == '0.00' && $locationArea->conditions[0]['total'] == '0.00') {
                $delivery = 'Free on all orders';
            } else {
                if($locationArea->conditions[0]['total'] == '0.00') {
                    $delivery = '£' . $locationArea->conditions[0]['amount'] . ' on all orders';
                } else {
                    $delivery = '£' . $locationArea->conditions[0]['amount'] . ' below ' . '£' . $locationArea->conditions[0]['total'];
                }
            }

            $openingTimes = $location['options']['hours']['opening']['flexible'];
            for ($i = $currentWeekDay; $i < $currentWeekDay + count($openingTimes); $i++) {
                if($openingTimes[$i % 7]['status'] != 0) {
                    if ($i == $currentWeekDay) {
                        $hour = (int)explode(':', $openingTimes[$i % 7]['open'])[0];
                        $minute = (int)explode(':', $openingTimes[$i % 7]['open'])[1];
                        $titleOpenTime = "Opening Today " . (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : $hour) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : '');
                        $hour = (int)explode(':', $openingTimes[$i % 7]['open'])[0];
                        $minute = (int)explode(':', $openingTimes[$i % 7]['open'])[1];
                        $openTime = (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : $hour) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : '') . ' - ';
                        $hour = (int)explode(':', $openingTimes[$i % 7]['close'])[0];
                        $minute = (int)explode(':', $openingTimes[$i % 7]['close'])[1];
                        $openTime = $openTime . (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : $hour) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : '');
                        break;
                    }

                    $hour = (int)explode(':', $openingTimes[$i % 7]['open'])[0];
                    $minute = (int)explode(':', $openingTimes[$i % 7]['open'])[1];
                    $titleOpenTime = "Opening " . $this->weekDays[$i % 7] . " " . (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : $hour) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : '');
                    $hour = (int)explode(':', $openingTimes[$i % 7]['open'])[0];
                    $minute = (int)explode(':', $openingTimes[$i % 7]['open'])[1];
                    $openTime = (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : $hour) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : '') . ' - ';
                    $hour = (int)explode(':', $openingTimes[$i % 7]['close'])[0];
                    $minute = (int)explode(':', $openingTimes[$i % 7]['close'])[1];
                    $openTime = $openTime . (($hour > 12) ? (($hour - 12 < 10) ? ('0' . ($hour - 12)) : $hour) : $hour) . ':' . (($minute < 10) ? ('0' . $minute) : $minute) . (($hour > 12) ? 'PM' : '');
                    break;
                }
            }

            $specailsCategoryId = $this->categoryModel->where('permalink_slug', 'specials')->first()->category_id;


            $customSpecials = $this->menuCategoryModel::with('menu')->where('category_id', $specailsCategoryId)->get();
            $specials = array();
            foreach ($customSpecials as $special) {
                if ($this->locationableModel->where('locationable_type', 'menus')->where('locationable_id', $special->menu_id)->where('location_id', $request['user']['locationId'])->first()) {
                    array_push($specials, $special);
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
                'titleOpenTime' => $titleOpenTime,
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
        try {
            $response['menu'] = $this->menuModel->where('menu_id', $request->id)->first();
            $favorite = $this->favoriteModel->where('customer_id', $request['userId'])->where('menu_id', $request['id'])->first();
            if ($favorite) {
                $response['menu']['isFavorite'] = true;
            } else {
                $response['menu']['isFavorite'] = false;
            }
            $response['options'] = $this->menuOptionModel::with('option_values')->with('option')->where('menu_id', $request->id)->get();
        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function getCheckOutTime(Request $request) {
        try {
            $stripe_customer_id = $this->customerSettingModel->where('customer_id', $request['user']['id'])->first()->stripe_customer_id;
            \Stripe\Stripe::setApiKey(config('api.stripe_key_test_secret'));
            $response['savedCards'] = \Stripe\PaymentMethod::all([
              'customer' => $stripe_customer_id,
              'type' => 'card',
            ]);
            $intent = $this->stripe->setupIntents->create([
              'customer' => $stripe_customer_id,
            ]);
            $locationArea = $this->locationAreaModel->where('area_id', $request['user']['areaId'])->first();
            $location = $this->locationModel->where('location_id', $locationArea->location_id)->first();

            $currentDate = date('Y-m-d');
            $currentTime = date('H:i');
            $currentWeekDay = ((int)date('w') + 7 - 1) % 7;

            $response['clientSecret'] = $intent->client_secret;
            $response['delivery'] = array();
            $response['pickup'] = array();
            $deliveryTimes = $location['options']['hours']['delivery']['flexible'];
            $pickUpTimes = $location['options']['hours']['collection']['flexible'];
            $currentHour = (float)explode(':', $currentTime)[0];
            $currentMinute = (float)explode(':', $currentTime)[1];
            for ($i = $currentWeekDay; $i < $currentWeekDay + count($deliveryTimes); $i++) {
                if($deliveryTimes[$i % 7]['status'] != 0) {
                    $date = [
                        'id' => count($response['delivery']),
                        'date' => date('Y-m-d', strtotime('+' . (($i - $currentWeekDay) % 7) .'days')),
                        'day' => date('d', strtotime('+' . (($i - $currentWeekDay) % 7) .'days')),
                        'weekDay' => $this->weekDays[$i % 7],
                        'times' => array()
                    ];
                   
                    $openHour = (float)explode(':', $deliveryTimes[$i % 7]['open'])[0];
                    $openMinute = (float)explode(':', $deliveryTimes[$i % 7]['open'])[1];

                    $closeHour = (float)explode(':', $deliveryTimes[$i % 7]['close'])[0];
                    $closeMinute = (float)explode(':', $deliveryTimes[$i % 7]['close'])[1];

                    if ($i == $currentWeekDay) {
                        if ($currentTime <= $deliveryTimes[$i % 7]['open']) {
                            $currentHour = $openHour;
                            $currentMinute = $openMinute;
                        }
                    } else {
                        $currentHour = $openHour;
                        $currentMinute = $openMinute;
                    }
                        
                    if ($currentMinute <= 15) {
                        $currentHour += 0;
                    } else if($currentMinute > 15 && $currentMinute < 45) {
                        $currentHour += 0.5;
                    } else {
                        $currentHour += 1;
                    }

                    if ($closeMinute <= 15) {
                        $closeHour += 0;
                    } else if($closeMinute > 15 && $closeMinute < 45) {
                        $closeHour += 0.5;
                    } else {
                        $closeHour += 1;
                    }
                    for ($j = $currentHour; $j < $closeHour; $j += 0.5) {
                        $delta = (int)(($j - (int)$j) * 60);
                        if ( $delta == 0) {
                            $temp = [
                                'orderTime' => (int)$j . ':00:00',
                                'showTime' => (int)$j . ':00-' . (int)$j . ':30',
                            ];
                        } else {
                            $temp = [
                                'orderTime' => (int)$j . ':30:00',
                                'showTime' => (int)$j . ':30-' . ((int)$j + 1) . ':00',
                            ];
                        }
                        array_push($date['times'], $temp);
                    }

                    if(count($date['times']) > 0)
                        array_push($response['delivery'], $date);
                }
            }

            $currentHour = (float)explode(':', $currentTime)[0];
            $currentMinute = (float)explode(':', $currentTime)[1];
            for ($i = $currentWeekDay; $i < $currentWeekDay + count($pickUpTimes); $i++) {
                if($pickUpTimes[$i % 7]['status'] != 0) {
                    $date = [
                        'id' => count($response['pickup']),
                        'date' => date('Y-m-d', strtotime('+' . (($i - $currentWeekDay) % 7) .'days')),
                        'day' => date('d', strtotime('+' . (($i - $currentWeekDay) % 7) .'days')),
                        'weekDay' => $this->weekDays[$i % 7],
                        'times' => array()
                    ];
                   
                    $openHour = (float)explode(':', $pickUpTimes[$i % 7]['open'])[0];
                    $openMinute = (float)explode(':', $pickUpTimes[$i % 7]['open'])[1];

                    $closeHour = (float)explode(':', $pickUpTimes[$i % 7]['close'])[0];
                    $closeMinute = (float)explode(':', $pickUpTimes[$i % 7]['close'])[1];

                    if ($i == $currentWeekDay) {
                        if ($currentTime <= $pickUpTimes[$i % 7]['open']) {
                            $currentHour = $openHour;
                            $currentMinute = $openMinute;
                        }
                    } else {
                        $currentHour = $openHour;
                        $currentMinute = $openMinute;
                    }
                    if ($currentMinute <= 7) {
                        $currentHour += 0;
                    } else if($currentMinute > 7 && $currentMinute <= 22) {
                        $currentHour += 0.25;
                    } else if($currentMinute > 22 && $currentMinute <= 37) {
                        $currentHour += 0.5;
                    } else if($currentMinute > 37 && $currentMinute <= 52) {
                        $currentHour += 0.75;
                    } else {
                        $currentHour += 1;
                    }

                    if ($closeMinute <= 7) {
                        $closeHour += 0;
                    } else if($closeMinute > 7 && $closeMinute <= 22) {
                        $closeHour += 0.25;
                    } else if($closeMinute > 22 && $closeMinute <= 37) {
                        $closeHour += 0.5;
                    } else if($closeMinute > 37 && $closeMinute <= 52) {
                        $closeHour += 0.75;
                    } else {
                        $closeHour += 1;
                    }
                    

                    for ($j = $currentHour; $j < $closeHour; $j += 0.25) {
                        $delta = (int)(($j - (int)$j) * 60);
                        if ( $delta == 0) {
                            $temp = [
                                'orderTime' => (int)$j . ':00:00',
                                'showTime' => (int)$j . ':00-' . (int)$j . ':15',
                            ];
                        } else if ( $delta == 15) {
                            $temp = [
                                'orderTime' => (int)$j . ':15:00',
                                'showTime' => (int)$j . ':15-' . (int)$j . ':30',
                            ];
                        } else if ( $delta == 30) {
                            $temp = [
                                'orderTime' => (int)$j . ':30:00',
                                'showTime' => (int)$j . ':30-' . (int)$j . ':45',
                            ];
                        } else {
                            $temp = [
                                'orderTime' => (int)$j . ':45:00',
                                'showTime' => (int)$j . ':45-' . ((int)$j + 1) . ':00',
                            ];
                        }
                        array_push($date['times'], $temp);
                    }

                    if(count($date['times']) > 0)
                        array_push($response['pickup'], $date);
                }
            }
        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function getSavedCard(Request $request) {
        try {
            $stripe_customer_id = $this->customerSettingModel->where('customer_id', $request['user']['id'])->first()->stripe_customer_id;
            \Stripe\Stripe::setApiKey(config('api.stripe_key_test_secret'));
            $response = \Stripe\PaymentMethod::all([
              'customer' => $stripe_customer_id,
              'type' => 'card',
            ]);

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function deleteCard(Request $request) {
        try {
            $this->stripe->paymentMethods->detach(
              $request['paymentMethodId'],
              []
            );
            $stripe_customer_id = $this->customerSettingModel->where('customer_id', $request['user']['id'])->first()->stripe_customer_id;
            \Stripe\Stripe::setApiKey(config('api.stripe_key_test_secret'));
            $response = \Stripe\PaymentMethod::all([
              'customer' => $stripe_customer_id,
              'type' => 'card',
            ]);

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function makePaymentIntent(Request $request) {
        try {
            $payment_intent = null;
            $stripe_customer_id = $this->customerSettingModel->where('customer_id', $request['user']['id'])->first()->stripe_customer_id;
            \Stripe\Stripe::setApiKey(config('api.stripe_key_test_secret'));
            \Stripe\PaymentIntent::create([
                'amount' => $request['amount'],
                'currency' => 'gbp',
                'customer' => $stripe_customer_id,
                'payment_method' => $request['paymentMethodId'],
                'off_session' => true,
                'confirm' => true,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            // Error code will be authentication_required if authentication is needed
            $payment_intent_id = $e->getError()->payment_intent->id;
            $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
        }
        return $payment_intent;
    }

    public function verifyPayment(Request $request) {
        $user = $this->userModel->where('customer_id', $request['customer_id'])->first();
        $customerSetting = $this->customerSettingModel->where('customer_id', $user->customer_id)->first();
        $areaId = $customerSetting ? $customerSetting->area_id : '';
        $locationArea = $this->locationAreaModel->where('area_id', $areaId)->first();
        $locationId = $locationArea ? $locationArea->location_id : '';
        $order = [
            'customer_id' => $request->customer_id,
            'total_items' => $request->total_items,
            'payment' => $request->payment,
            'comment' => $request->comment,
            'order_type' => $request->order_type,
            'status_id' => $request->status_id,
            'order_time' => $request->order_time,
            'order_date' => $request->order_date,
            'order_total' => $request->order_total,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'telephone' => $user->telephone,
            'email' => $user->email,
            'address_id' => $user->addresses[0]->address_id,
            'location_id' => $locationId,
            'date_added' => new DateTime(),
            'date_modified' => new DateTime(),
        ];
        if ($this->orderModel->insertOrIgnore($order)) {
            return 'true';
        }
        return 'false';
    }

    public function getOrders(Request $request) {
        try {
            $orders = $this->orderModel->where('customer_id', $request['user']['id'])->orderBy('date_added', 'DESC')->limit(5)->get();
            foreach ($orders as $order) {
                $order['status_name'] = $this->statusModel->where('status_id', $order->status_id)->first()->status_name;
                $order['date'] = date('m/d/Y', strtotime($order->date_added));
            }
            $response['orders'] = $orders;

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return $response;
    }

    public function addFavorites(Request $request) {
        try {
            $favorite = $this->favoriteModel->where('customer_id', $request['userId'])->where('menu_id', $request['id'])->first();
            if (!$favorite) {
                $requestFavorite = [
                    'customer_id' => $request['userId'],
                    'menu_id' => $request['id'],
                ];
                if ($this->favoriteModel->insertOrIgnore($requestFavorite)) {
                    return 'true';
                }
            } else {
                if ($this->favoriteModel->where('customer_id', $request['userId'])->where('menu_id', $request['id'])->delete()) {
                    return 'false';
                }
            }

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return 'false';
    }

    public function getFavorites(Request $request) {
        try {
            $favoriteIds = $this->favoriteModel->where('customer_id', $request['user']['id'])->get();
            $favorites = array();
            foreach ($favoriteIds as $favorite) {
                $menu = $this->menuModel->where('menu_id', $favorite->menu_id)->first();
                $menu['isFavorite'] = true;
                array_push($favorites, $menu);
            }
            return $favorites;

        } catch (Exception $ex) {
            abort(500, lang('igniter.api::lang.server.internal_error'));
        }
        return array();
    }

    public function getPolicy(Request $Request) {
        $response['content'] = $this->pageModel->where('permalink_slug', 'policy')->first()->content;
        return $response;
    }

    public function getTerms(Request $Request) {
        $response['content'] = $this->pageModel->where('permalink_slug', 'terms-and-conditions')->first()->content;
        return $response;
    }
}
 