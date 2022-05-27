<?php

namespace App\Http\Controllers;

use App\Jobs\SendResponseEmailJob;
use Illuminate\Http\Request;
use App\Models\Request as UserRequest;
use App\Http\Resources\RequestResource;
use Illuminate\Validation\ValidationException;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return RequestResource::collection(\App\Models\Request::paginate(25));
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
            $request = \App\Models\Request::find($id);
            if (!$request) {
                return response()->json(['error' => 'request not found'], 404);
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
