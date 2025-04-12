# Flame for CodeIgniter 4

Flame is a library that makes it easy to implement the Active Record design pattern in CodeIgniter 4 models.

## Requirements

- PHP 7.3+
- CodeIgniter 4.x
- Composer

## Installation

### Using Composer

```bash
composer require wilburhimself/flame
```

### Manual Installation

1. Clone this repository
2. Run `composer install`

## Usage with CodeIgniter 4

The library leverages CodeIgniter 4's model system, making it easy to add Active Record pattern functionality to your models:

```php
<?php

namespace App\Models;

use WilburHimself\Flame\Flame;

class PersonModel extends Flame
{
    protected $table = 'people';
    protected $primaryKey = 'id';
    
    public function __construct()
    {
        parent::__construct();
        
        // Define relationships
        $this->belongs_to('department');
        $this->has_many('skills');
    }
}
```

### Using Your Models

#### Loading and Using the Model in CodeIgniter 4

```php
<?php

namespace App\Controllers;

use App\Models\PersonModel;

class People extends BaseController
{
    protected $personModel;
    
    public function __construct()
    {
        $this->personModel = new PersonModel();
    }
    
    public function index()
    {
        // Get all people
        $data['people'] = $this->personModel->get_list();
        
        return view('people/index', $data);
    }
    
    public function view($id)
    {
        // Get a single person with related data
        $data['person'] = $this->personModel->get($id);
        
        return view('people/view', $data);
    }
    
    public function create()
    {
        // Process form submission
        if ($this->request->getMethod() === 'post') {
            $data = $this->personModel->generate_from_post();
            $id = $this->personModel->add($data);
            
            return redirect()->to('/people/view/' . $id);
        }
        
        return view('people/create');
    }
}
```

## Key Features

Flame for CodeIgniter 4 takes advantage of CodeIgniter 4's modern features:

1. **Namespaces**: All models are properly namespaced
2. **Model System**: Extends CI4's robust Model class
3. **Services**: Uses CI4's service system
4. **Method Names**: Aligned with CI4 conventions
5. **Return Types**: Enhanced return types (including better null handling)

## Working with Relationships

Flame makes it easy to define and work with model relationships:

### Defining Relationships

```php
// In your model constructor
$this->belongs_to('department');  // One-to-one relationship
$this->has_many('skills');        // One-to-many relationship
$this->has_and_belongs_to_many('projects'); // Many-to-many relationship
```

### Working with Related Models

When you define relationships, Flame will automatically load related records:

```php
// Get a person with their department and skills
$person = $this->personModel->get($id);

// Access related data
$department = $person->department;
$skills = $person->skills;
```

## Core Methods

### Retrieving Data

```php
// Get single record by ID
$person = $personModel->get($id);

// Get all records
$people = $personModel->get_list();

// Get with conditions
$managers = $personModel->get_list(['role' => 'manager']);

// Find by a specific field
$johns = $personModel->find_by_name('John');
```

### Creating, Updating and Deleting

```php
// Create a new record
$personData = [
    'name' => 'John Doe',
    'email' => 'john@example.com'
];
$id = $personModel->add($personData);

// Update a record
$personData['email'] = 'johndoe@example.com';
$personModel->update($id, $personData);

// Delete a record
$personModel->delete($id);
```

## License

MIT License
