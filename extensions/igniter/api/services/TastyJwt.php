<?php

namespace Igniter\Api\Services;

// Libary Import
use Illuminate\Support\Facades\Hash;
use Igniter\Flame\Traits\Singleton;

/**
 * TastyJwt -- customized JWT plugin Service
 */
class TastyJwt {
    use Singleton;

    public function makeJwtToken() {

    }

    public function makeHashPassword($password) {
        return Hash::make($password);
    }

    public function makeToken($user) {
        $userString = $user->customer_id . $user->email;
        return Hash::make($userString);
    }

    public function validatePasswrod($user, $password) {
        return Hash::check($password, $user->password);
    }

    public function validateToken($request) {
        if ($request->bearerToken()) {
            $userModelClass = 'Admin\Models\Customers_model';
            $userModel = new $userModelClass;
            $user = $userModel->where('remember_token', $request->bearerToken())->first();
            if ($user) {
                return 1;
            }
            return 0;
        } else {
            return 0;
        }
    }
}