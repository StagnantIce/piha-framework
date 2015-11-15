<h1> Piha - Easy PHP Framework</h1>

This framework work on PHP 5.3+ and MySQL (Bitrix as optional). As optional you can include this framework as library to your project.

<img src="https://img.shields.io/badge/coverage-64%25-yellowgreen.svg"/>
<img src="https://img.shields.io/badge/php-5.3-blue.svg"/>

<h2> Features </h2>
1) Own fast and simple ORM and SQL builder. With many features.
```php

$q = new CQuery("tableName", array("ID", "NAME", "CODE"));
$q->all("ID", "CODE"); // group by CODE

class MyModel {
    public $_name = 'tableName';
    public $_columns = array(
      "ID" => array("type"=>"pk"),
      "NAME" => array("type"=>"string"),
      "CODE" => array("type" =>"string")
    )
}

MyModel::GetCode(1);
MyModel::q()->where(1)->one("CODE");
MyModel::q()->where(array("ID" => 1))->select("ID")->one("CODE"); // equal results

// Simple access
MyModel::q(); //CQuery
MyModel::schema(); // CMigration
MyModel::m(); // CModel

```
2) Simple forms and model forms with autocomplete and html5 support
```php
// in controller
$form = CForm::post('MyForm');
if ($form->isSubmit()) {
    echo $form->getValue("DATE_FROM");
}

// in view
echo
$form->start(array("action" => $this->url())),
$form->text(array("NAME" => "TITLE")),
$form->date(array("NAME" => "DATE_FROM")),
$form->submit(array("value" => "Search")),
$form->end();
```


