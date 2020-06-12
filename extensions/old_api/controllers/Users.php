<?php

namespace Igniter\Api\Controllers;

use AdminMenu;

// Local Import
use Igniter\Api\Services\TastyJwt;


// Libary Import
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;
use ApplicationException;
use Exception;
use Geocoder;
use Location;
use DateTime;

/**
 * Users Controller
 */
class Users extends \Admin\Classes\AdminController {

    private $modelConfig = [
        'user' => 'Admin\Models\Customers_model',
        'location' => 'Admin\Models\Locations_model',
        'customerGroup' => 'Admin\Models\Customer_groups_model',
    ];

    private $userModel;
    private $locationModel;
    private $customerGroupModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new $this->modelConfig['user'];
        $this->locationModel =  new $this->modelConfig['location'];
        $this->customerGroupModel = new $this->modelConfig['customerGroup'];
        if ($this->customerGroupModel->where('group_name', 'App User')->get()->count() == 0) {
            $this->customerGroupModel->insertOrIgnore([
                'group_name' => 'App User',
                'description' => '',
                'approval' => 0
            ]);
        }
    }

    public function makeUserResponse($user) {
        $response['token'] = $user->remember_token;
        $response['user'] = [
            'id' => $user->customer_id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'areaId' => $user->activation_code
        ];
        return $response;
    }

    public function signUp(Request $request) {
        // Encode user password with Hash
        $request['password'] = TastyJwt::instance()->makeHashPassword($request['password']);

        $user = $this->userModel->where('email', $request['email'])->first();
        if ($user) {
            abort(400, lang('igniter.api::lang.auth.alert_user_duplicated'));
        } else {
            $customerGroupId = $this->customerGroupModel->where('group_name', 'App User')->first()->customer_group_id;

            // Convert fieldNmae for database
            $requestUser = [
                'first_name' => $request['firstName'],
                'last_name' => $request['lastName'],
                'telephone' => $request['telephone'],
                'email' => $request['email'],
                'password' => $request['password'],
                'date_added' => new DateTime(),
                'customer_group_id' => $customerGroupId
            ];

            if ($this->userModel->insertOrIgnore($requestUser)) {
                $user = $this->userModel->where('email', $request['email'])->first();

                $token = TastyJwt::instance()->makeToken($user);
                if ($this->userModel->where('email', $request['email'])->update(['remember_token' => $token]))
                {
                    $user = $this->userModel->where('email', $request['email'])->first();
                    return $this->makeUserResponse($user);
                }
                abort(400, lang('igniter.api::lang.auth.alert_signup_failed'));
            }
            abort(400, lang('igniter.api::lang.auth.alert_signup_failed'));
        }
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

    public function validateToken(Request $request) {
        if (TastyJwt::instance()->validateToken($request)) {
            return 'true';
        }
        return 'false';
    }

    public function setLocation(Request $request) {
        if (!TastyJwt::instance()->validateToken($request))
            abort(400, lang('igniter.api::lang.auth.alert_token_expired'));

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

        if($user->addresses)
            $user->addresses()->delete();
        $user->addresses()->insert($address);

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
