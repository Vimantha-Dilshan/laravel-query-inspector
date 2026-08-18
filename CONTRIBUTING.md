# Contributing to Laravel Query Inspector

Thank you for your interest in contributing! This document explains how to set up the project locally and the standards we follow.

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Git

### Local Setup

```bash
# 1. Fork and clone the repository
git clone https://github.com/Vimantha-Dilshan/laravel-query-inspector.git
cd laravel-query-inspector

# 2. Install dependencies
composer install

# 3. Run the test suite to verify your environment
./vendor/bin/pest
```

## Development Workflow

### Branching

| Branch      | Purpose                                       |
| ----------- | --------------------------------------------- |
| `main`      | Stable, tagged releases                       |
| `develop`   | Active development, base for feature branches |
| `feature/*` | New features                                  |
| `fix/*`     | Bug fixes                                     |
| `docs/*`    | Documentation only                            |

Always branch from `develop` and target `develop` with your pull request.

### Code Style

We use **Laravel Pint** (PSR-12 preset) to enforce consistent formatting:

```bash
# Check style without making changes
./vendor/bin/pint --test

# Auto-fix style violations
./vendor/bin/pint
```

### Static Analysis

We use **PHPStan** (via Larastan) at level 6:

```bash
./vendor/bin/phpstan analyse
```

### Testing

All changes must be accompanied by tests. We use **Pest PHP**.

```bash
# Run the full test suite
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage --min=80

# Run a specific test file
./vendor/bin/pest tests/Unit/SlowQueryDetectorTest.php
```

**Test guidelines:**

- Feature tests live in `tests/Feature/`
- Unit tests live in `tests/Unit/`
- Use descriptive `it('...', fn() => ...)` syntax
- Avoid unnecessary `beforeEach()` — prefer explicit setup inside each test
- Do not assert against generated IDs or timestamps directly

## Pull Request Process

1. Ensure all tests pass and Pint reports no issues.
2. Add or update tests to cover your changes.
3. Update `CHANGELOG.md` under `[Unreleased]`.
4. Update the `README.md` if you're adding new public API or configuration.
5. Open a PR against `develop` using the provided PR template.
6. A maintainer will review your PR. Please be responsive to feedback.

## Reporting Bugs

Use the **Bug Report** issue template on GitHub. Include a minimal reproduction case.

## Suggesting Features

Use the **Feature Request** issue template. Describe the problem before proposing a solution.

## Code of Conduct

Be respectful and constructive. We follow the [Contributor Covenant](https://www.contributor-covenant.org/version/2/1/code_of_conduct/).

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
