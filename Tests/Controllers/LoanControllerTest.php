<?php

namespace Tests\Controllers;

use App\Controllers\LoanController;
use App\Repositories\LoanRepository;
use App\Services\LoanService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Tests\BaseTestCase;

class LoanControllerTest extends BaseTestCase
{
    /** @var LoanService */
    private $loanServiceMock;

    /** @var LoanController */
    private $controller;

    /** @var LoanRepository */
    private $loanRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loanRepositoryMock = $this->createMock(LoanRepository::class);
        $this->loanServiceMock = new LoanService($this->loanRepositoryMock);
        $this->controller = new LoanController($this->loanServiceMock);
    }

    public function testGetLoanSuccess()
    {
        $app = $this->getAppInstance();

        $container = $app->getContainer();

        $loanId = 1;
        $amount = 1000;
        $loanData = ['id' => $loanId, 'amount' => $amount];

        $this->loanRepositoryMock->expects($this->once())
        ->method('getLoanById')
        ->with($loanId)
        ->willReturn($loanData);

        $container = $app->getContainer();
        $container->set(LoanRepository::class, $this->loanRepositoryMock);

        $request = $this->createRequest('GET', "/loans/$loanId");
        $response = $app->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame(json_encode($loanData), (string) $response->getBody());
    }

    public function testGetLoanBadRequest()
    {
        $request = $this->createRequest('GET', '/loans/abc');
        $response = $this->runApp($request);
        $body = (string) $response->getBody();

        $payload = json_decode($body, true);
        $this->assertSame('BAD_REQUEST', $payload['type']);
        $this->assertSame(ID_IS_REQUIRED, $payload['message']);
    }

    public function testGetLoanNotFound()
    {
        $app = $this->getAppInstance();
        $loanId = 100;
        $this->loanRepositoryMock->expects($this->once())
        ->method('getLoanById')
        ->with($loanId)
        ->willReturn(false);

        $container = $app->getContainer();
        $container->set(LoanRepository::class, $this->loanRepositoryMock);

        $request = $this->createRequest('GET', "/loans/$loanId");
        $response = $this->runApp($request);
        $body = (string) $response->getBody();
        $payload = json_decode($body, true);

        $this->assertSame('RESOURCE_NOT_FOUND', $payload['type']);
        $this->assertSame(LOAN_NOT_FOUND, $payload['message']);
    }

    public function testCreateLoanSuccess()
    {
        $amount = 100;
        $app = $this->getAppInstance();

        $this->loanRepositoryMock->expects($this->once())
            ->method('createLoan')
            ->with($amount)
            ->willReturn(true);

        $container = $app->getContainer();
        $container->set(LoanRepository::class, $this->loanRepositoryMock);

        $request = $this->createRequest('POST', '/loans', ['amount' => $amount]);
        $response = $this->runApp($request);
        $responseData = ['message' => 'success'];
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(json_encode($responseData), (string) $response->getBody());
    }

    public function testCreateLoanBadRequest()
    {
        $amount = 'abc';
        $request = $this->createRequest('POST', '/loans', ['amount' => 'abc']);
        $response = $this->runApp($request);
        $body = (string) $response->getBody();

        $payload = json_decode($body, true);
        $this->assertSame('BAD_REQUEST', $payload['type']);
        $this->assertSame(AMOUNT_IS_REQUIRED, $payload['message']);
    }

    public function testUpdateLoanSuccess()
    {
        $app = $this->getAppInstance();
        $loanId = 1;
        $amount = 100;

        $this->loanRepositoryMock->expects($this->once())
            ->method('updateLoanById')
            ->with($loanId, ['amount' => $amount])
            ->willReturn(true);

        $container = $app->getContainer();
        $container->set(LoanRepository::class, $this->loanRepositoryMock);

        $request = $this->createRequest('PUT', "/loans/$loanId", ['amount' => $amount]);
        $response = $this->runApp($request);
        $responseData = ['message' => 'success'];

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(json_encode($responseData), (string) $response->getBody());
    }

    public function testUpdateLoanBadRequestWithId()
    {
        $loanId = 'abc';
        $request = $this->createRequest('PUT', "/loans/$loanId", ['amount' => 100]);
        $response = $this->runApp($request);
        $body = (string) $response->getBody();

        $payload = json_decode($body, true);
        $this->assertSame('BAD_REQUEST', $payload['type']);
        $this->assertSame(ID_IS_REQUIRED, $payload['message']);
    }

    public function testUpdateLoanBadRequestWithAmount()
    {
        $app = $this->getAppInstance();
        $loanId = 1;

        $request = $this->createRequest('PUT', "/loans/$loanId", ['amount' => 'abc']);
        $response = $this->runApp($request);
        $body = (string) $response->getBody();
        $payload = json_decode($body, true);

        $this->assertSame('BAD_REQUEST', $payload['type']);
        $this->assertSame(AMOUNT_IS_REQUIRED, $payload['message']);
    }

    public function testUpdateLoanNotFound()
    {
        $app = $this->getAppInstance();
        $loanId = 100;

        $this->loanRepositoryMock->expects($this->once())
            ->method('updateLoanById')
            ->with($loanId, ['amount' => 100])
            ->willReturn(false);

        $container = $app->getContainer();
        $container->set(LoanRepository::class, $this->loanRepositoryMock);

        $request = $this->createRequest('PUT', "/loans/$loanId", ['amount' => 100]);
        $response = $this->runApp($request);
        $body = (string) $response->getBody();
        $payload = json_decode($body, true);

        $this->assertSame('RESOURCE_NOT_FOUND', $payload['type']);
        $this->assertSame(LOAN_NOT_FOUND, $payload['message']);
    }

    public function testDeleteLoanNotFound()
    {
        $app = $this->getAppInstance();
        $loanId = 100;

        $this->loanRepositoryMock->expects($this->once())
            ->method('deleteLoanById')
            ->with($loanId)
            ->willReturn(false);

        $container = $app->getContainer();
        $container->set(LoanRepository::class, $this->loanRepositoryMock);

        $request = $this->createRequest('DELETE', "/loans/$loanId");
        $response = $this->runApp($request);
        $body = (string) $response->getBody();
        $payload = json_decode($body, true);

        $this->assertSame('RESOURCE_NOT_FOUND', $payload['type']);
        $this->assertSame(LOAN_NOT_FOUND, $payload['message']);
    }

    public function testDeleteLoanSuccess()
    {
        $app = $this->getAppInstance();
        $loanId = 1;

        $this->loanRepositoryMock->expects($this->once())
            ->method('deleteLoanById')
            ->with($loanId)
            ->willReturn(true);

        $container = $app->getContainer();
        $container->set(LoanRepository::class, $this->loanRepositoryMock);

        $request = $this->createRequest('DELETE', "/loans/$loanId");
        $response = $this->runApp($request);
        $responseData = ['message' => 'success'];

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(json_encode($responseData), (string) $response->getBody());
    }

    public function testDeleteLoanBadRequest()
    {
        $loanId = 'abc';
        $request = $this->createRequest('DELETE', "/loans/$loanId");
        $response = $this->runApp($request);
        $body = (string) $response->getBody();

        $payload = json_decode($body, true);
        $this->assertSame('BAD_REQUEST', $payload['type']);
        $this->assertSame(ID_IS_REQUIRED, $payload['message']);
    }
}
