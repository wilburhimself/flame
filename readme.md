Flame for Codeigniter
================

Flame for Codeigniter is a library that makes it easy to implement the active record design pattern on Codeigniter's models.


Requirements
------------

1. PHP 5.2+
2. CodeIgniter 1.6.x - 2.0-dev

Usage
-----
When creating a model include the library file and extend your model from the Flame Class:

```
require_once APPPath.'/libraries/flame.php';
class Person extends Flame {
	...
}
```

There are some requirements before actually using this models, in the constructor function you have to:

>Set table name. Should be a plural name, ex. **Person = People**.
>Set the name of the primary key field: $this->pk = 'id';
>Call the parent's constructor function.

```
public function __construct() {
	$this->tablename = 'people';
		$this->pk = 'id';
		parent::__construct();
	}
}
```

After this setup is done you are ready to use the model, for example:

```
$people = $this->Person->get_list();
or
$person = $this->Person->get($id);
```

This is clearly a VERY simple example and more can much more can be done with it. For up-to-date
documentation keep an eye on the following link:

http://retrorock.info/flame/
