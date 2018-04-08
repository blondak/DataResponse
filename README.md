# CsvResponse2
CSV response for [Nette Framework](https://github.com/nette/nette)

* gzip encoding
* dynamic datasource

Download package
```console
composer require blondak/dataresponse
````

Sample datasource
```php
<?php

namespace App\Model\Service\Feed;

Presenter
```php
public function actionExportCsv(int $id): void
{
	$response = new CsvResponse($this->datasource, sprintf('export-%d.csv', $id));
	$this->sendResponse($response);
}
```