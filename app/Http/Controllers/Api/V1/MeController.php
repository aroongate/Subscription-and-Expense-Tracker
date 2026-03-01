<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class MeController extends ApiController
{
    public function __invoke(Request $request)
    {
        $user = $request->user()->load('organizations');

        return $this->success(UserResource::make($user));
    }
}
