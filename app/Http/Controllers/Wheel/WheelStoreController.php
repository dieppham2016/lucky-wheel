<?php

namespace App\Http\Controllers\Wheel;

use App\Models\WheelStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use WheelStoreLogic;

class WheelStoreController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 * @return Response
	 */
    public function index(Request $request): AnonymousResourceCollection {
        return WheelStoreLogic::index($request);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param WheelStore $wheelStore
     * @return Response
     */
    public function show(WheelStore $wheelStore)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param WheelStore $wheelStore
     * @return Response
     */
    public function edit(WheelStore $wheelStore)
    {
        //
    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param $id
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function update($id, Request $request): JsonResponse {
        return WheelStoreLogic::singleRecordUpdate($id, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param WheelStore $wheelStore
     * @return Response
     */
    public function destroy(WheelStore $wheelStore)
    {
        //
    }
}
