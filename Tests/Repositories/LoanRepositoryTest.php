<?php

namespace Tests\Repositories;

use App\Repositories\LoanRepository;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class LoanRepositoryTest extends TestCase
{
    /** @var LoanRepository */
    private $repository;

    /** @var PDO */
    private $pdoMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdoMock = $this->createMock(PDO::class);

        $this->repository = new LoanRepository($this->pdoMock);
    }

    public function testGetLoanById(): void
    {
        $id = 1;
        $loanData = ['id' => $id, 'amount' => 1000];

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($loanData);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM loan WHERE id = :id')
            ->willReturn($pdoStatementMock);

        $result = $this->repository->getLoanById($id);

        $this->assertEquals($loanData, $result);
    }

    public function testGetLoanByIdNotFound(): void
    {
        $id = 999;

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM loan WHERE id = :id')
            ->willReturn($pdoStatementMock);

        $result = $this->repository->getLoanById($id);

        $this->assertFalse($result);
    }

    public function testCreateLoan(): void
    {
        $amount = 1000;

        $pdoStatementMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO loan (amount, date_created) VALUES (:amount, :dateCreated)')
            ->willReturn($pdoStatementMock);

        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->repository->createLoan($amount);

        $this->assertTrue($result);
    }

    public function testCreateLoanFailure(): void
    {
        $amount = 1000;

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException());

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($pdoStatementMock);

        $this->expectException(PDOException::class);

        $this->repository->createLoan($amount);
    }

    public function testUpdateExistingLoanById(): void
    {
        $existingId = 1;
        $newAmount = 1500;

        $pdoStatementMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
        ->method('prepare')
        ->with('UPDATE loan SET amount = :amount WHERE id = :id')
        ->willReturn($pdoStatementMock);

        $pdoStatementMock->expects($this->once())
        ->method('execute')
        ->with(['id' => $existingId, 'amount' => $newAmount])
        ->willReturn(true);

        $pdoStatementMock->expects($this->once())
        ->method('rowCount')
        ->willReturn(1);
        $result = $this->repository->updateLoanById($existingId, ['amount' => $newAmount]);

        $this->assertTrue($result);
    }

    public function testUpdateNonExistingLoanById(): void
    {
        $nonExistingId = 999;
        $newAmount = 1500;

        $pdoStatementMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('UPDATE loan SET amount = :amount WHERE id = :id')
            ->willReturn($pdoStatementMock);

        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with(['id' => $nonExistingId, 'amount' => $newAmount])
            ->willReturn(true);

        $pdoStatementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(0);

        $result = $this->repository->updateLoanById($nonExistingId, ['amount' => $newAmount]);

        $this->assertFalse($result);
    }

    public function testUpdateLoanByIdFailure(): void
    {
        $id = 1;
        $amount = 1500;
        $data = ['amount' => $amount];

        $pdoStatementMock = $this->createMock(PDOStatement::class);
        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException());

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($pdoStatementMock);

        $this->expectException(PDOException::class);

        $this->repository->updateLoanById($id, $data);
    }

    public function testDeleteExistingLoanById(): void
    {
        $existingId = 1;

        $pdoStatementMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM loan WHERE id = :id')
            ->willReturn($pdoStatementMock);

        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with(['id' => $existingId])
            ->willReturn(true);

        $pdoStatementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $result = $this->repository->deleteLoanById($existingId);

        $this->assertTrue($result);
    }

    public function testDeleteNonExistingLoanById(): void
    {
        $nonExistingId = 999;

        $pdoStatementMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM loan WHERE id = :id')
            ->willReturn($pdoStatementMock);

        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with(['id' => $nonExistingId])
            ->willReturn(true);

        $pdoStatementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(0);

        $result = $this->repository->deleteLoanById($nonExistingId);

        $this->assertFalse($result);
    }

    public function testDeleteLoanByIdFailure(): void
    {
        $id = 1;

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM loan WHERE id = :id')
            ->willThrowException(new PDOException());

        $this->expectException(PDOException::class);

        $this->repository->deleteLoanById($id);
    }
}
