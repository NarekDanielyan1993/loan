<?php

use App\Controllers\LoanController;
use Slim\App;

return function (App $app) {
    $app->post(CREATE_LOAN, LoanController::class . ':createLoan');
    $app->get(GET_LOAN, LoanController::class . ':getLoan');
    $app->put(UPDATE_LOAN, LoanController::class . ':updateLoan');
    $app->delete(DELETE_LOAN, LoanController::class . ':deleteLoan');
    $app->get(GET_LOANS, LoanController::class . ':getAllLoans');
};
