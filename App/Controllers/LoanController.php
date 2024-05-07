<?php

namespace App\Controllers;

use App\Services\LoanService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class LoanController
{
    /** @var LoanService */
    private $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function getAllLoans(Request $request, Response $response): Response
    {
        $loans = $this->loanService->getAllLoans();
        $response->getBody()->write(json_encode($loans));

        return $response->withHeader('Content-Type', 'application/json');

        return $response->withJson($loans);
    }

    public function createLoan(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $amount = $data['amount'];

        if (!isset($amount) || !is_numeric($amount)) {
            throw new HttpBadRequestException($request, AMOUNT_IS_REQUIRED);
        }

        $this->loanService->createLoan($amount);

        $responseData = ['message' => 'success'];
        $response->getBody()->write(json_encode($responseData));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function getLoan(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        if (!is_numeric($id)) {
            throw new HttpBadRequestException($request, ID_IS_REQUIRED);
        }

        $loan = $this->loanService->getLoanById($id);

        if (!$loan) {
            throw new HttpNotFoundException($request, LOAN_NOT_FOUND);
        }

        $response->getBody()->write(json_encode($loan));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function updateLoan(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        if (!isset($id) || $id === '' || !ctype_digit($id)) {
            throw new HttpBadRequestException($request, ID_IS_REQUIRED);
        }

        $data = $request->getParsedBody();
        $amount = $data['amount'] ?? null;

        if (!isset($amount) || !is_numeric($amount)) {
            throw new HttpBadRequestException($request, AMOUNT_IS_REQUIRED);
        }

        $updatedLoan = $this->loanService->updateLoanById($id, ['amount' => $amount]);

        if (!$updatedLoan) {
            throw new HttpNotFoundException($request, LOAN_NOT_FOUND);
        }

        $response->getBody()->write(json_encode(['message' => 'success']));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function deleteLoan(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        if (!is_numeric($id)) {
            throw new HttpBadRequestException($request, ID_IS_REQUIRED);
        }

        $deletedLoan = $this->loanService->deleteLoanById($id);

        if (!$deletedLoan) {
            throw new HttpNotFoundException($request, LOAN_NOT_FOUND);
        }

        $response->getBody()->write(json_encode(['message' => 'success']));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
