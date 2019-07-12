<?php

namespace App\Http\Controllers\Api\Reward;

use App\Model\Reward\Point;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;

class PointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $query = Point::latest()
            ->with('rewardable');

        $points = pagination(
            $query,
            $request->limit
        );

        return new ApiCollection($points);
    }
}

