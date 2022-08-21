# General Ledger and Journal Package/API for Laravel

Tests: ![php 8.0](https://github.com/abivia/ledger/actions/workflows/php80.yml/badge.svg) ![php 8.1](https://github.com/abivia/ledger/actions/workflows/php81.yml/badge.svg)

Ledger is a full-featured implementation of the core of any good accounting system, a double-entry
journal and general ledger. It is not a complete accounting solution, but rather the minimum for
keeping track of financial transactions. Ledger features a JSON API that provides access to all
functions.

That's the only minimal part. Ledger features:

- Full double-entry accounting system with audit trail capability.
- Multi-currency support.
- Support for multiple business units.
- Sub-journal support.
- Multilingual.
- Integrates via direct controller access or through JSON API.
- Atomic transactions with concurrent update blocking.
- Reference system supports soft linking to other ERP components.
- Designed for Laravel from the ground up.

## Documentation

Full documentation is available [here](https://ledger.abivia.com/).

## Quick start

### Installation and Configuration

Install Ledger with composer:

`composer require abivia/ledger`

Publish configuration:

`php artisan vendor:publish --provider="Abivia\Ledger\LedgerServiceProvider"`

Create database tables

`php artisan migrate`

### Configuration

The configuration file is installed as `config\ledger.php`. You can enable/disable the 
JSON API, set middleware, and a path prefix to the API.

## Donations welcome

If you're getting something out of Ledger, you can sponsor us in any amount you wish using Liberapay
[![Liberapay](https://liberapay.com/assets/widgets/donate.svg)](https://liberapay.com/abivia/donate).
Liberapay is itself run on donations and charges no fees beyond bank charges.
