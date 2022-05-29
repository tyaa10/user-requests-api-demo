<?php

namespace Tests\Unit;

use App\Http\Controllers\RequestController;
use App\Repositories\Interfaces\IRequestsDao;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Mockery;
use PHPUnit\Framework\TestCase;

class RequestControllerUnitTest extends TestCase
{
    protected $requestsDaoMock;

    public function setUp(): void
    {
        parent::setup();
        $this->requestsDaoMock = Mockery::mock(IRequestsDao::class);
        app()->instance(IRequestsDao::class, $this->requestsDaoMock);
    }

    /**
     * RequestController index action call ->
     *  RequestsDao findRequestsByDateAndStatus method call
     *
     * @test
     * @return void
     */
    public function givenHttpRequest_whenCallIndex_thenRequestsDaoFindCalled()
    {
        $request = Request::create('/requests/date/' . date('Y-m-d') . '/status/Active');
        $collectionStub =
            $this->createMock(AnonymousResourceCollection::class)
                ->method('count')
                ->willReturn(0);

        $date = date('Y-m-d');
        $status = 'Active';
        $itemsPerPage = 2;

        $this->requestsDaoMock->shouldReceive(
            'findRequestsByDateAndStatus'
        )->once()
            ->withArgs([$date, $status, $itemsPerPage])
            ->andReturn($collectionStub);

        $requestController = new RequestController($this->requestsDaoMock);
        $response = $requestController->index($request);
        $this->assertNotNull($response);
    }
}
