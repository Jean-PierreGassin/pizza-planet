# Writing Tests

All tests written for this project should be written using PHPUnit and Vitest.

## Methodology

- Write to fail-first, then implement the solution to pass (TDD)
- Use data providers for repetitive test cases

## Methods

- Use camelCase for method names
- Use `test` prefix e.g `testThisDoesThat` without being too verbose
- If method names are too long, consider using a docblock to explain what the test is doing

## Unit Tests

- Test a single method with expected inputs and outputs
- Do not interact with the database

## Feature Tests

- Test a single feature end-to-end
- Test multiple variations of the same feature
- Can interact with the database, events, jobs, etc

## What makes a good test?

- It tests core business logic, not framework-specific details unless specified
- It is readable and easy to understand
