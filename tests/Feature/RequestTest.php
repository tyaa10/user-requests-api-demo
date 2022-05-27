<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestTest extends TestCase
{
    /**
     * Post a new request feature test
     *
     * @test
     * @return void
     */
    public function givenRequestData_whenPost_thenCreated()
    {
        $this->postCorrectRequest()
            ->assertStatus(201)
            ->assertJsonStructure([
                'success'
            ]);
    }

    /**
     * Post a new request feature negative test: validation error
     *
     * @test
     * @return void
     */
    public function givenRequestDataWOMessage_whenPost_thenValidationError()
    {
        $this->postWrongRequest()
            ->assertStatus(400);
    }

    /**
     * Find requests by date and state feature test
     *
     * @test
     * @return void
     */
    public function givenDateAndStatus_whenGet_thenCorrectDataReceived()
    {
        $this->postCorrectRequest();
        $this->json(
            'GET',
            '/requests/date/2022-05-27/status/Active', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'email',
                        'author',
                        'status',
                        'message',
                        'comment',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    /**
     * Resolve feature test
     *
     * @test
     * @return void
     */
    public function givenResponseData_whenPut_thenResolved()
    {
        $this->postCorrectRequest();
        $this->putResponse(1, 'Lorem ipsum dolor sit amet')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success'
            ]);
    }

    /**
     * Resolve feature negative test: already resolved
     *
     * @test
     * @return void
     */
    public function givenResponseData_whenPutAgain_thenRefused()
    {
        $this->postCorrectRequest();
        $this->putResponse(1, 'Lorem ipsum dolor sit amet');
        $this->putResponse(1, 'Lorem ipsum dolor sit amet')
            ->assertStatus(299)
            ->assertJsonStructure([
                'warning'
            ]);
    }

    private function postCorrectRequest() {
        return $this->postRequest('John Doe', 'doe@example.com', 'Lorem ipsum dolor sit amet');
    }

    private function postWrongRequest() {
        return $this->postRequest('John Doe', 'doe@example.com', '');
    }

    private function postRequest($name, $email, $message) {
        $userRequestData = [
            'name' => $name,
            'email' => $email,
            'message' => $message
        ];
        return $this->json('POST', '/requests', $userRequestData, ['Accept' => 'application/json']);
    }

    private function putResponse($id, $comment) {
        return $this->json('PUT', '/requests/' . $id, ['comment' => $comment], ['Accept' => 'application/json']);
    }
}
