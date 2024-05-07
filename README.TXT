# Loan Application

This is a loan application built with Slim 4 framework. It allows you to manage loans including creating, reading, updating, and deleting operations.

## Installation

1. Clone the repository to your local machine:

    ```bash
    git clone git@github.com:NarekDanielyan1993/loan.git
    ```

2. Install dependencies using Composer:

    ```bash
    composer install
    ```

3. Copy the example environment file and configure it with your PostgreSQL database details:

    ```bash
    cp .env.example .env
    ```
    Update the `.env` file with your PostgreSQL database credentials.

4. Create LOAN table with id, amount columns in your database:

5. Start the application using the built-in PHP server:

    composer start

    The application will be available at `http://localhost:8080`.

## Usage

- To get a list of all loans, send a GET request to `/loans`.
- To get a specific loan by ID, send a GET request to `/loans/{id}`.
- To create a new loan, send a POST request to `/loans` with the loan amount in the request body.
- To update a loan by ID, send a PUT request to `/loans/{id}` with the updated loan data in the request body.
- To delete a loan by ID, send a DELETE request to `/loans/{id}`.

## Dependencies

- Slim 4: PHP micro-framework for building APIs and web applications.
- PostgreSQL: Relational database used for storing loan data.
- Composer: Dependency manager for PHP.