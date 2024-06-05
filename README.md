# Test task
Calculates commissions on the transactions

# How to run
1. Install symfony and all dependencies 
```bash
composer install
````
2. Creates database by running the next command:
```bash
php bin/console doctrine:database:create
```
3. Makes migration by running the next command:
```bash
php bin/console make:migration
```
4. Runs the server
```bash
symfony server:start -d
```
5. Go to the next link http://localhost:8000/commissions (port can differ)

# How to run tests
```bash
php vendor/bin/phpunit tests/Service/CommissionsTest.php
```
