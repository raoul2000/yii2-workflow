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

To run a single test :
```
../vendor/bin/codecept run codeception/unit/workflow/helpers/WorkflowHelperTest.php:testGetNextStatus
../vendor/bin/codecept run codeception/unit/workflow/helpers/WorkflowHelperTest.php
```


## Memento

### Output to Console during Tests

To output to console from a codeception test use :
```
\Codeception\Util\Debug::debug($someVariable);
```

With debug mode enabled :
```
../vendor/bin/codecept run --debug codeception/unit/workflow/helpers/WorkflowHelperTest.php
```

### Enable XDebug With Codeception

Check XDebug is installed :
- run `php -i > info.txt`
- copy/paste `info.txt` into [this form](https://xdebug.org/wizard.php) and check the result
- if needed, follow *Installation instructions*.
