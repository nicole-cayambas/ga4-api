<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoogleAnalyticsQueryRequest;
use App\Services\GAResponseService;
use Illuminate\Http\Response;

class GoogleAnalyticsFetchController extends Controller
{
    public function fetch(GoogleAnalyticsQueryRequest $request)
    {
        $validated = $request->validateRequest();
        if(!$validated['success']) return $validated['error'];

        $gaService = new GAResponseService();
        
        $result = $gaService->fetchFromDataApi($request);
        if(!$result['success']) {
            return response()->json([
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $result['message']
            ]);
        }

        $response = $gaService->convertResponseToJson($result['response']);

        return response()->json([
            'code' => Response::HTTP_OK,
            'data' => $response
        ]);
    }

    
}
