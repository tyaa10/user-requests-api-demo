<?php

namespace App\Repositories\Interfaces;
use App\Models\Request as UserRequest;

/**
 * User Requests Data Access Object Interface
 * */
interface IRequestsDao
{
    /**
     * Find requests / by date / by status / by date and status
     * @param $date - the date of request - string in the format yyyy-MM-dd
     * @param $status - request status - string 'Active' or 'Resolved'
     * @param $itemsPerPage - number of results per page
     * */
    public function findRequestsByDateAndStatus($date, $status, $itemsPerPage);
    /**
     * Store a new user request
     * @param UserRequest $request - new user request data
     * */
    public function storeRequest(UserRequest $request);
    /**
     * Resolve user request
     * @param $id - user request ID
     * @param $comment - response text
     * */
    public function resolveRequest($id, $comment);
}
