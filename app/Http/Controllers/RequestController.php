<?php

namespace App\Http\Controllers;

use App\Jobs\SendResponseEmailJob;
use Illuminate\Http\Request;
use App\Models\Request as UserRequest;
use App\Http\Resources\RequestResource;
// use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $requestSegments = $request->segments();
        $date = null;
        $status = null;
        if (array_key_exists(1, $requestSegments) && $requestSegments[1] === 'date') {
            $date = $requestSegments[2];
            if (array_key_exists(3, $requestSegments) && $requestSegments[3] === 'status')
                $status = $requestSegments[4];
        } else if (array_key_exists(1, $requestSegments) && $requestSegments[1] === 'status')
            $status = $requestSegments[2];
        return RequestResource::collection(
            UserRequest::when($date, function ($query, $date) {
                    $query->whereDate('created_at', '=', $date);
                })->when($status, function ($query, $status) {
                    $query->where('status', '=', $status);
                })
                ->paginate(25)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $httpRequest)
    {
        try {
            $httpRequest->validate([
                'name' => 'required|max:50',
                'email' => 'required|email|max:50',
                'message' => 'required|max:5000'
            ]);
        } catch (ValidationException $ex) {
            return response()->json(['error' => $ex->errors()], 400);
        }
        $request = new UserRequest();
        $request->name = $httpRequest->name;
        $request->email = $httpRequest->email;
        $request->message = $httpRequest->message;
        if ($request->save()) {
            return response()->json(['success' => 'request created'], 201);
        } else {
            return response()->json(['error' => 'database error'], 502);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $httpRequest, $id)
    {
        if ($id <= 0) {
            return response()->json(['error' => "Invalid ID"], 400);
        }
        try {
            $httpRequest->validate([
                'comment' => 'required|max:5000'
            ]);
        } catch (ValidationException $ex) {
            return response()->json(['error' => $ex->errors()], 400);
        }
        try {
            $request = UserRequest::find($id);
            if (!$request) {
                return response()->json(['error' => 'request not found'], 404);
            }
            if ($request->status === 'Resolved') {
                return response()->json(['warning' => 'The request has already been resolved'], 200);
            }
            $request->status = 'Resolved';
            $request->comment = $httpRequest->comment;
            if ($request->save()) {
                SendResponseEmailJob::dispatch([
                    'name' => $request->name,
                    'emailTo' => $request->email,
                    'request' => $request->message,
                    'response' => $request->comment
                ]);
                return response()->json(['success' => 'request resolved'], 200);
            } else {
                return response()->json(['error' => 'database error'], 502);
            }
        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data - {$exception->getMessage}");
        }
    }
}
