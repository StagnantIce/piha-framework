<h1> Piha - Easy PHP Framework</h1>

This framework work on PHP 5.3+ or PHP 8.2 and MySQL (Bitrix as optional). As optional you can include this framework as library to your project.

<img src="https://img.shields.io/badge/coverage-70%25-yellowgreen.svg"/>
<img src="https://img.shields.io/badge/php-5.3-blue.svg"/>
<img src="https://img.shields.io/badge/php-7.3-blue.svg"/>
<img src="https://img.shields.io/badge/php-8.2-blue.svg"/>

<h2> Fast start </h2>

1) Clone to directory mysite/piha like install path.
2) Run sudo chown -R www-data:www-data mysite if need, because we need right to create assets directory.

For apache2 ypu can set it in /etc/apache2/envvars

export APACHE_RUN_USER=www-data
export APACHE_RUN_GROUP=www-data

For php sessions:

chown -R www-data:www-data /var/lib/php/sessions

3) Run mysite/piha/demo.sh and open mysite like your site.

<h2> Features </h2>
1) Own fast and simple ORM and SQL builder. With many features.

```php
$q = new CQuery("tableName", ["ID", "NAME", "CODE"]);
$q->all("ID", "CODE"); // group by CODE

class MyModel {
    public $_name = 'tableName';
    public $_columns = [
      "ID" => ["type"=>"pk"],
      "NAME" => ["type"=>"string"],
      "CODE" => ["type" =>"string"]
    ]
}

MyModel::GetCode(1);
MyModel::q()->where(1)->one("CODE");
MyModel::q()->where(["ID" => 1])->select("ID")->one("CODE"); // equal results

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
$form->start(["action" => $this->url()]),
$form->text(["NAME" => "TITLE"]),
$form->date(["NAME" => "DATE_FROM"]),
$form->submit(["value" => "Search"]),
$form->end();
```

