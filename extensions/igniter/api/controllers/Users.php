<?php

namespace Igniter\Api\Controllers;

use AdminMenu;

// Local Import
use Igniter\Api\Services\TastyJwt;


// Libary Import
use Illuminate\Http\Request;
use ApplicationException;
use Exception;
use Geocoder;
use Location;
use DateTime;

require_once(__DIR__ . '/../vendor/stripe/init.php');
/**
 * Users Controller
 */
class Users extends \Admin\Classes\AdminController {

    private $modelConfig = [
        'user' => 'Admin\Models\Customers_model',
        'location' => 'Admin\Models\Locations_model',
        'address' => 'Admin\Models\Addresses_model',
        'customerGroup' => 'Admin\Models\Customer_groups_model',
        'locationArea' => 'Admin\Models\Location_areas_model',
        'customerSetting' => 'Igniter\Api\Models\CustomerSetting',
    ];

    private $userModel;
    private $locationModel;
    private $customerGroupModel;
    private $locationAreaModel;
    private $customerSettingModel;
    private $stripeConfig;
    private $stripe;

    public function __construct() {
        parent::__construct();
        $this->userModel = new $this->modelConfig['user'];
        $this->locationModel =  new $this->modelConfig['location'];
        $this->locationAreaModel = new $this->modelConfig['locationArea'];
        $this->customerSettingModel = new $this->modelConfig['customerSetting'];
        $this->customerGroupModel = new $this->modelConfig['customerGroup'];
        if ($this->customerGroupModel->where('group_name', 'App User')->get()->count() == 0) {
            $this->customerGroupModel->insertOrIgnore([
                'group_name' => 'App User',
                'description' => '',
                'approval' => 0
            ]);
        }
        $this->stripe = new \Stripe\StripeClient(config('api.stripe_key_test_secret'));
    }

    public function makeUserResponse($user) {
        $response['token'] = $user->remember_token;
        $deliveryAddress = '';
        if (count($user->addresses) > 0)
            $deliveryAddress = $user->addresses[0]->address_1 . ' ' . $user->addresses[0]->address_2 . ', ' . $user->addresses[0]->postcode;
        $customerSetting = $this->customerSettingModel->where('customer_id', $user->customer_id)->first();
        $areaId = $customerSetting ? $customerSetting->area_id : '';
        $stripeCustomerId = $customerSetting ? $customerSetting->stripeCustomerId : '';

        $locationArea = $this->locationAreaModel->where('area_id', $areaId)->first();
        $locationId = $locationArea ? $locationArea->location_id : '';
        
        $response['user'] = [
            'id' => $user->customer_id,
            'email' => $user->email,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'telephone' => $user->telephone,
            'areaId' => $areaId,
            'locationId' => $locationId,
            'deliveryAddress' => $deliveryAddress,
            'stripeCustomerId' => $stripeCustomerId,
            'isFacebook' => ($user->isFacebook == true) ? true : false,
        ];
        return $response;
    }

    public function signUp(Request $request) {
        // Encode user password with Hash
        $request['password'] = TastyJwt::instance()->makeHashPassword($request['password']);
        
        $customerGroupId = $this->customerGroupModel->where('group_name', 'App User')->first()->customer_group_id;
        if ($request['userId']) {
            $user = $this->userModel->where('customer_id', $request['userId'])->first();
            $token = TastyJwt::instance()->makeToken($user);
            $requestUser = [
                'first_name' => $request['firstName'],
                'last_name' => $request['lastName'],
                'telephone' => $request['telephone'],
                'email' => $request['email'],
                'remember_token' => $token,
                'password' => $request['password'],
                'date_added' => new DateTime(),
                'status' => 1,
            ];
            if ($this->userModel->where('customer_id', $request['userId'])->update($requestUser))
            {
                $user = $this->userModel->where('customer_id', $request['userId'])->first();
                return $this->makeUserResponse($user);
            }
        }

        $user = $this->userModel->where('email', $request['email'])->first();
        if($request['isFacebook']) {
            $stripe_customer = $this->stripe->customers->create([
                'name' => $request['firstName'],
                'email' => $request['email'],
            ]);
            $requestUser = [
                'first_name' => $request['firstName'],
                'last_name' => null,
                'telephone' => null,
                'email' => $request['email'],
                'date_added' => new DateTime(),
                'customer_group_id' => $customerGroupId,
                'status' => 1,
            ];
        } else {
            $stripe_customer = $this->stripe->customers->create([
                'name' => $request['firstName'] . ' ' . $request['lastName'],
                'email' => $request['email'],
            ]);
            $requestUser = [
                'first_name' => $request['firstName'],
                'last_name' => $request['lastName'],
                'telephone' => $request['telephone'],
                'email' => $request['email'],
                'password' => $request['password'],
                'date_added' => new DateTime(),
                'customer_group_id' => $customerGroupId,
                'status' => 1,
            ];
        }
        if (!$user) {
            if ($this->userModel->insertOrIgnore($requestUser)) {
                $user = $this->userModel->where('email', $request['email'])->first();
                $setting = [
                    'customer_id' => $user->customer_id,
                    'stripe_customer_id' => $stripe_customer->id,
                    'area_id' => null,
                    'push_status' => 0
                ];
                $this->customerSettingModel->insertOrIgnore($setting);
            }
        }
        $token = TastyJwt::instance()->makeToken($user);
        if ($this->userModel->where('email', $request['email'])->update(['remember_token' => $token]))
        {
            $user = $this->userModel->where('email', $request['email'])->first();
            if($request['isFacebook'] === true)
                $user['isFacebook'] = true;
            return $this->makeUserResponse($user);
        }
        abort(400, lang('igniter.api::lang.auth.alert_signup_failed'));
    }

    public function signIn(Request $request) {
        $user = $this->userModel->where('email', $request['email'])->first();
        if ($user) {
            if (TastyJwt::instance()->validatePasswrod($user, $request['password'])) {
                $newToken = TastyJwt::instance()->makeToken($user);
                if ($this->userModel->where('email', $request['email'])->update(['remember_token' => $newToken])) {
                    $user = $this->userModel->where('email', $request['email'])->first();
                    if ($user->status == 0) {
                        abort(400, lang('igniter.api::lang.auth.alert_status_disabled'));
                    }
                    return $this->makeUserResponse($user);
                }
                abort(400, lang('igniter.api::lang.auth.alert_user_not_exist'));
            } else {
                abort(400, lang('igniter.api::lang.auth.alert_user_not_exist'));
            }
        } else {
            abort(400, lang('igniter.api::lang.auth.alert_user_not_exist'));
        }
    }

    public function forgotPassword(Request $request) {

    }

    public function validateToken(Request $request) {
        return TastyJwt::instance()->validateToken($request);
    }

    public function setLocation(Request $request) {
        try {
            $userLocation = $this->geocodeSearchQuery($request->address['postcode']);
            $areaId = "";
            $nearByLocation = Location::searchByCoordinates($userLocation->getCoordinates())->first(function ($location) use ($userLocation) {
                if ($area = $location->searchDeliveryArea($userLocation->getCoordinates())) {
                    Location::updateNearbyArea($area);
                    return $area;
                }
            });
            if (!$nearByLocation) {
                abort(400, lang('igniter.api::lang.location.alert_not_correct_location'));
            }
            if ($nearByLocation->searchDeliveryArea($userLocation->getCoordinates())) {
                $areaId = $nearByLocation->searchDeliveryArea($userLocation->getCoordinates())->area_id;
            }
            if($this->customerSettingModel->where('customer_id', $request->user['id'])->first()) {
                $this->customerSettingModel->where('customer_id', $request->user['id'])->update(['area_id' => $areaId]);
            }
            
            $user = $this->userModel->where('customer_id', $request->user['id'])->first();
            // Convert fieldNmae for database
            $address = [
                'customer_id' => $request->user['id'],
                'address_1' => $request->address['address1'],
                'address_2' => $request->address['address2'],
                'city' => $request->address['city'],
                'country_id' => $request->address['countryId'],
                'postcode' => $request->address['postcode'],
                'state' => $request->address['state'],
            ];

            if (count($user->addresses) == 0) {
                $user->addresses()->insertOrIgnore($address);
            } else {
                $user->addresses()->update($address);
            }

            $user = $this->userModel->where('customer_id', $request->user['id'])->first();

            $response = $this->makeUserResponse($user);
            return $response;
    
        } catch (Exception $ex) {
            abort(400, lang('igniter.api::lang.location.alert_invalid_search_query'));
        }
    }

    public function geocodeSearchQuery($searchQuery)
    {
        $collection = Geocoder::geocode($searchQuery);

        if (!$collection OR $collection->isEmpty()) {
            throw new ApplicationException(lang('igniter.api::lang.location.alert_invalid_search_query'));
        }

        $userLocation = $collection->first();
        if (!$userLocation->hasCoordinates())
            throw new ApplicationException(lang('igniter.api::lang.location.alert_invalid_search_query'));

        Location::updateUserPosition($userLocation);
        return $userLocation;
    }
}
