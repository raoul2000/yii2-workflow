# Running Unit tests

- Navigate to the **yii2-workflow** installation folder
- Install composer dependencies

```
composer self-update
composer global require "fxp/composer-asset-plugin:1.0.0-beta4"
composer install --prefer-dist --dev
```
- Create Database `yii2_workflow_test`
- Apply DB migrations

```
cd tests
php ./codeception/bin/yii  migrate/up --interactive=0
```

- Build and start Codeception tests

```
../vendor/bin/codecept build
../vendor/bin/codecept run unit
```

To produce the code coverage report, run :

```
../vendor/bin/codecept run unit --coverage-html
```