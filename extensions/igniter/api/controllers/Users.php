<?php

namespace Igniter\Api\Controllers;

use AdminMenu;

// Local Import
use Igniter\Api\Services\TastyJwt;


// Libary Import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use ApplicationException;
use Exception;
use Geocoder;
use Location;
use DateTime;

require_once(__DIR__ . '/../vendor/stripe-php-7.36.1/init.php');
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
    ];

    private $userModel;
    private $locationModel;
    private $customerGroupModel;
    private $locationAreaModel;
    private $stripeConfig;
    private $stripe;

    public function __construct() {
        parent::__construct();
        $this->userModel = new $this->modelConfig['user'];
        $this->locationModel =  new $this->modelConfig['location'];
        $this->locationAreaModel = new $this->modelConfig['locationArea'];
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
            $deliveryAddress = $user->addresses[0]->address_1 . ', ' . $user->addresses[0]->postcode;
        $locationArea = $this->locationAreaModel->where('area_id', $user->activation_code)->first();
        $locationId = '';
        if($locationArea)
            $locationId = $locationArea->location_id;
        $response['user'] = [
            'id' => $user->customer_id,
            'email' => $user->email,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'telephone' => $user->telephone,
            'areaId' => $user->activation_code,
            'locationId' => $locationId,
            'deliveryAddress' => $deliveryAddress,
            'stripeCustomerId' => $user->security_answer,
            'isFacebook' => ($user->isFacebook === true) ? true : false,
        ];
        return $response;
    }

    public function signUp(Request $request) {
        // Encode user password with Hash
        $request['password'] = TastyJwt::instance()->makeHashPassword($request['password']);
        
        $customerGroupId = $this->customerGroupModel->where('group_name', 'App User')->first()->customer_group_id;
        if ($request['userId'] != '') {
            $user = $this->userModel->where('customer_id', $request['userId'])->first();
        } else {
            $user = $this->userModel->where('email', $request['email'])->first();
        }

        if($request['isFacebook'] !== true) {
            if ($user) {
                if ($request['userId'] !== '') {
                    $token = TastyJwt::instance()->makeToken($user);
                    $requestUser = [
                        'first_name' => $request['firstName'],
                        'last_name' => $request['lastName'],
                        'telephone' => $request['telephone'],
                        'email' => $request['email'],
                        'password' => $request['password'],
                        'date_added' => new DateTime(),
                        'remember_token' => $token,
                        'status' => 1,
                    ];
                    if ($this->userModel->where('customer_id', $request['userId'])->update($requestUser))
                    {
                        $user = $this->userModel->where('customer_id', $request['userId'])->first();
                        return $this->makeUserResponse($user);
                    }
                }
                abort(400, lang('igniter.api::lang.auth.alert_user_duplicated'));
            } else {
                $stripe_customer = $this->stripe->customers->create([
                    'name' => $request['firstName'] . ' ' . $request['lastName'],
                    'email' => $request['email'],
                ]);
                // Convert fieldNmae for database
                $requestUser = [
                    'first_name' => $request['firstName'],
                    'last_name' => $request['lastName'],
                    'telephone' => $request['telephone'],
                    'email' => $request['email'],
                    'password' => $request['password'],
                    'date_added' => new DateTime(),
                    'customer_group_id' => $customerGroupId,
                    'security_answer' => $stripe_customer->id,
                    'status' => 1,
                ];
            }
        } else {
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
                'security_answer' => $stripe_customer->id,
                'status' => 1,
            ];
        }
        if (!$user) {
            if ($this->userModel->insertOrIgnore($requestUser)) {
                $user = $this->userModel->where('email', $request['email'])->first();
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
            $userLocation = $this->geocodeSearchQuery($request->address['address1']);
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

            if (count($nearByLocation->delivery_areas) > 0) {
                $areaId = $nearByLocation->delivery_areas[0]['area_id'];
            }
        } catch (Exception $ex) {
            abort(400, lang('igniter.api::lang.location.alert_invalid_search_query'));
        }
        $this->userModel->where('customer_id', $request->user['id'])->update(['activation_code' => $areaId]);
        
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

        $user->addresses()->update($address);

        $response = $this->makeUserResponse($user);
        return $response;
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
