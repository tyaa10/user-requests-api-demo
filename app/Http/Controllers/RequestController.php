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
     *
     * @OA\Get (
     *      path="/requests",
     *      operationId="getRequestsList",
     *      tags={"Requests"},
     *      summary="Get list of requests",
     *      description="Returns list of user requests by date and status",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number (25 items per page)",
     *         required=false,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation"
     *       )
     *     )
     *
     * @OA\Get (
     *      path="/requests/date/{date}/status/{status}/",
     *      operationId="getRequestsListByDateAndStatus",
     *      tags={"Requests"},
     *      summary="Find requests by date and status",
     *      description="Returns list of user requests by date and status",
     *      @OA\Parameter(
     *         name="date",
     *         in="path",
     *         description="Date the user request was created",
     *         required=true,
     *      ),
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="User request status: Active or Resolved",
     *         required=true,
     *      ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number (25 items per page)",
     *         required=false,
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation"
     *       )
     *     )
     *
     * @OA\Get (
     *      path="/requests/date/{date}/",
     *      operationId="getRequestsListByDate",
     *      tags={"Requests"},
     *      summary="Find requests by date",
     *      description="Returns list of user requests by date",
     *      @OA\Parameter(
     *         name="date",
     *         in="path",
     *         description="Date the user request was created",
     *         required=true,
     *      ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number (25 items per page)",
     *         required=false,
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation"
     *       )
     *     )
     *
     * @OA\Get (
     *      path="/requests/status/{status}/",
     *      operationId="getRequestsListByStatus",
     *      tags={"Requests"},
     *      summary="Find requests by status",
     *      description="Returns list of user requests by status",
     *      @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="User request status: Active or Resolved",
     *         required=true,
     *      ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number (25 items per page)",
     *         required=false,
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successful operation"
     *       )
     *     )
     *
     * Returns list of requests
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
     *
     * @OA\Post(
     *  path="/requests",
     *  summary="New request",
     *  description="Post a new user request",
     *  operationId="postRequest",
     *  tags={"Requests"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="Pass user request data",
     *    @OA\JsonContent(
     *       required={"name","email", "message"},
     *       @OA\Property(property="name", type="string", format="name", example="John"),
     *       @OA\Property(property="email", type="string", format="email", example="john@test.com"),
     *       @OA\Property(property="message", type="string", example="Lorem ipsum dolor sit amet"),
     *    ),
     *  ),
     *  @OA\Response(
     *    response=201,
     *    description="Request created",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="string", example="request created")
     *    )
     *  ),
     *  @OA\Response(
     *    response=400,
     *    description="Validation errors"
     *  ),
     *  @OA\Response(
     *    response=502,
     *    description="Database error",
     *    @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="database error")
     *    )
     *  )
     * )
     *
     * Store a newly created user request in storage.
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
     *
     * @OA\Put(
     *  path="/requests/{id}",
     *  summary="Resolve request",
     *  description="Resolve specified user request",
     *  operationId="putRequest",
     *  tags={"Requests"},
     *  @OA\Parameter(
     *    name="id",
     *    in="path",
     *    description="User request ID",
     *    required=true,
     *  ),
     *  @OA\RequestBody(
     *    required=true,
     *    description="Comment on the user's request and resolve it",
     *    @OA\JsonContent(
     *       required={"comment"},
     *       @OA\Property(property="comment", type="string", example="Lorem ipsum dolor sit amet"),
     *    ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="Request resolved",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="string", example="request resolved")
     *    )
     *  ),
     *  @OA\Response(
     *    response=400,
     *    description="Validation errors"
     *  ),
     *  @OA\Response(
     *    response=299,
     *    description="Request already resolved warning",
     *    @OA\JsonContent(
     *       @OA\Property(property="warning", type="string", example="The request has already been resolved")
     *    )
     *  ),
     *  @OA\Response(
     *    response=404,
     *    description="Not found error",
     *    @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="request not found")
     *    )
     *  ),
     *  @OA\Response(
     *    response=502,
     *    description="Database error",
     *    @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="database error")
     *    )
     *  )
     * )
     *
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $httpRequest, $id)
    {
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
                return response()->json(['warning' => 'The request has already been resolved'], 299);
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
