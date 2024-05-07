<?php

namespace App\Services;

use App\Repositories\LoanRepository;

class LoanService
{
    /** @var LoanRepository */
    private $loanRepository;

    public function __construct (LoanRepository $loanRepository)
    {
        $this->loanRepository = $loanRepository;
    }

    public function getAllLoans(): array
    {
        return $this->loanRepository->getAllLoans();
    }

    public function getLoanById(int $id): array|bool
    {
        return $this->loanRepository->getLoanById($id);
    }

    public function createLoan(float $amount): bool
    {
        return $this->loanRepository->createLoan($amount);
    }

    public function updateLoanById(int $id, array $data): bool
    {
        return $this->loanRepository->updateLoanById($id, $data);
    }

    public function deleteLoanById(int $id): bool
    {
        return $this->loanRepository->deleteLoanById($id);
    }
}
