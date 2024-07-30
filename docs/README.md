# Introduction
Welcome to `php-scryfall-bulk-sdk`, a PHP library designed to simplify working with Scryfall's bulk data.

## Domain Splitting

This library is structured into four domains: Config, Download and Storage, Local Data Access, and API Wrappers.
In this README, I'll provide an overview of each domain and the features they provide.

### Domain 0: Config
This domain provides a simple, easy-to-use configuration mechanism that allows users to set sane defaults and manage their own configurations.

Some possible features for Domain 0 include:

* A `Config` class or interface that provides methods for getting and setting config values
* Support for multiple config sources (e.g., environment variables, JSON files, PHP arrays)
* Ability to load default config values from a file or hardcoded values
* Methods for merging config data from different sources
* Optional support for caching config values to improve performance

By having a dedicated Config domain, we can simplify user configuration, decouple config management from other domains, and provide a single source of truth.

### Domain 1: Download and Storage
This domain focuses on downloading the Scryfall bulk data file, storing it in a temporary location (e.g., a cache directory), and handling updates.

Some possible features for Domain 1 include:

* Ability to specify a target directory for storing the bulk data
* Option to overwrite existing files or store them separately based on their last modified date
* Handling of errors and exceptions during download and storage
* Mechanism to check if the stored file is outdated (e.g., by comparing its modification date with Scryfall's latest update)


### Domain 2: Local Data Access
This domain provides classes and methods for querying the local bulk data file.

Some possible features for Domain 2 include:

* Classes representing different types of Scryfall cards (e.g., Card, Token, Land)
* Methods for searching and filtering card data based on various criteria (e.g., name, mana cost, power/toughness)
* Support for retrieving related data, such as card sets or card effects
* Optional caching mechanism to improve performance

### Domain 3: API Wrappers
This domain provides APIs similar to the Scryfall web API.

Some possible features for Domain 3 include:

* Classes representing Scryfall's API endpoints (e.g., CardSearch, CardGet)
* Methods for making requests to the local bulk data file using these APIs
* Support for handling errors and exceptions in a way similar to the Scryfall web API
* Optional support for caching or memoization to reduce the number of queries made to the local data
