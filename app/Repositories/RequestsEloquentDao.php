<?php

namespace App\Repositories;

use App\Models\Request as UserRequest;
use App\Http\Resources\UserRequestResource as RequestResource;
use App\Repositories\Interfaces\IRequestsDao;

class RequestsEloquentDao implements IRequestsDao
{

    /**
     * @inheritDoc
     */
    public function findRequestsByDateAndStatus($date, $status, $itemsPerPage)
    {
        return RequestResource::collection(
            UserRequest::when($date, function ($query, $date) {
                $query->whereDate('created_at', '=', $date);
            })->when($status, function ($query, $status) {
                $query->where('status', '=', $status);
            })->paginate($itemsPerPage));
    }

    /**
     * @inheritDoc
     */
    public function storeRequest(UserRequest $request)
    {
        return $request->save();
    }

    /**
     * @inheritDoc
     */
    public function resolveRequest($id, $comment)
    {
        $request = UserRequest::find($id);
        if (!$request) {
            throw new \InvalidArgumentException('request not found');
        }
        if ($request->status === 'Resolved') {
            throw new \DomainException('The request has already been resolved');
        }
        $request->status = 'Resolved';
        $request->comment = $comment;
        return $request->save() ? $request : false;
    }
}
