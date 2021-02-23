<?php

namespace App\Http\Controllers\Wheel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use WheelStoreLogic;

class DegreesController extends Controller
{

	public function degrees(): AnonymousResourceCollection {
		return WheelStoreLogic::degrees();
	}
}
