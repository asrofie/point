<?php

namespace App\Http\Controllers\Api\Sales\DeliveryNote;

use Illuminate\Http\Request;
use App\Model\Master\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Sales\DeliveryNote\DeliveryNote;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $deliveryNote = DeliveryNote::eloquentFilter($request);

        $deliveryNote = pagination($deliveryNote, $request->get('limit'));

        return new ApiCollection($deliveryNote);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $deliveryNote = DeliveryNote::create($request->all());
            $deliveryNote
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($deliveryNote);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $deliveryNote = DeliveryNote::eloquentFilter($request)
            ->with('form')
            ->with('deliveryOrder.form')
            ->with('warehouse')
            ->with('customer')
            ->with('items.item')
            ->with('items.allocation')
            ->findOrFail($id);

        return new ApiResource($deliveryNote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int  $id
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        // TODO prevent delete if referenced by sales invoice
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $salesInvoice = SalesInvoice::findOrFail($id);

            $newSalesInvoice = $salesInvoice->edit($request->all());

            return new ApiResource($newSalesInvoice);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);

        $deliveryNote->delete();

        return response()->json([], 204);
    }
}
