<?php

namespace App\Http\Controllers\Wheel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use GamePlayLogic;

class GameController extends Controller
{

    /**
     * Tính tọa độ quà sẽ trúng gửi cho client
     *
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function degrees(Request $request): JsonResponse
    {
        return GamePlayLogic::calculateDegrees($request);
    }
}
