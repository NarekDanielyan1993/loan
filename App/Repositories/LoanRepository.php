<?php

namespace App\Repositories;

use PDO;
use PDOException;
use Slim\Exception\HttpInternalServerErrorException;

class LoanRepository
{
    /** @var PDO */
    private $db;

    public function __construct(PDO $database)
    {
        $this->db = $database;
    }

    public function getAllLoans(): array
    {
        try {
            $statement = $this->db->query('SELECT * FROM loan');

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException(GET_LOANS_ERROR);
        }
    }

    public function getLoanById(int $id): array|bool
    {
        try {
            $statement = $this->db->prepare('SELECT * FROM loan WHERE id = :id');
            $statement->execute(['id' => $id]);
            $loan = $statement->fetch(PDO::FETCH_ASSOC);

            return $loan;
        } catch (PDOException $e) {
            throw new PDOException(GET_LOAN_ERROR);
        }
    }

    public function createLoan(float $amount): bool
    {
        try {
            $statement = $this->db->prepare('INSERT INTO loan (amount, date_created) VALUES (:amount, :dateCreated)');

            return $statement->execute([
                ':amount' => $amount,
                ':dateCreated' => date('c'),
            ]);
        } catch (PDOException $e) {
            throw new PDOException(CREATE_LOAN_ERROR);
        }
    }

    public function updateLoanById(int $id, array $data): bool
    {
        try {
            $statement = $this->db->prepare('UPDATE loan SET amount = :amount WHERE id = :id');
            $result = $statement->execute(['id' => $id, 'amount' => $data['amount']]);

            return !!($result && $statement->rowCount() > 0);
        } catch (PDOException $e) {
            throw new PDOException(UPDATE_LOAN_ERROR);
        }
    }

    public function deleteLoanById(int $id): bool
    {
        try {
            $statement = $this->db->prepare('DELETE FROM loan WHERE id = :id');
            $result = $statement->execute(['id' => $id]);

            return !!($result && $statement->rowCount() > 0);
        } catch (PDOException $e) {
            throw new PDOException(DELETE_LOAN_ERROR);
        }
    }
}
