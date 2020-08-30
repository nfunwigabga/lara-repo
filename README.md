# LaraRepo
Larevel repository generator for Laravel version >=6.0.

## Installation
Require this package with composer:
```
composer require Nfunwigabga/lara-repo
```

Laravel uses package auto-discovery, so this package does not require you to manually register the service provider.

### Not using Auto-Discovery
If you do not use Auto-Discovery, then you need to add the following service provider to your `config/app.php` file inside the Providers array:

```
Nfunwigabga\LaraRepo\ServiceProvider::class
```

### Publish the configuration
The package comes with a configuration file, which you can publish using:
```
php artisan vendor:publish --provider="Nfunwigabga\LaraRepo\ServiceProvider"
```
The configuration defines the directory structure for your repositories and models.

## Usage
The package comes with 2 commands that allow you to generate repositories and criteria.

### Generate a repository
Each repository is based on a model class. So when you generate the repository, you have to to provide the model class name (without the subdirectory)
```
php artisan make:repo {model name}
```
eg If you want to create a repository for the User model, you can do:
```
php artisan make:repo User
```
If the model does not already exist, you will be prompted whether you'd like to create it at the same time.

The above command will generate the following files:
```
// This is the concrete implementation of the repository
app/Repositories/UserRepository.php 

// This is the interface that the repository class binds to.
app/Repositories/Contracts/IUser.php 

// The model class if a User model did not exist before
app/User.php 

// If you chose to generate a migration
database/migrations/xx_xx_xx_create_users_table.php 
```

### Using the repository in your controller
Once a repository is generated, you get a number of base methods out-of-the-box that you can call directly. In a `UserController` class for instance, you can inject the repository contract like this and call the methods therein:
```php
<?php
namespace App\Http\Controllers;

use App\Repositories\Contracts\IUser;

class UserController extends Controller
{

    public $userRepo;

    /**
     * Inject the repository interface inside the constructor
     **/
    public function __construct(IUser $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function index()
    {
        $users = $this->userRepo->all();
    }

    public function show($id)
    {
        $user = $this->userRepo->find($id);
    }
}
```

The package comes with many base methods which you can directly access (as we have done above); These methods can be found in the [BaseRepository class](#).

However, you can create more methods in the individual repository classes, especially methods that are particular to that model class. eg:
```php
<?php
namespace App\Repositories;

use App\User;
use App\Repositories\Contracts\IUser;
use Nfunwigabga\LaraRepo\Base\BaseRepository;

class UserRepository extends BaseRepository implements IUser
{
    public function model()
    {
        return User::class; 
    }


    public function subscriptToNewsletter($userId)
    {
        // We can use the find method in the Base repository here to get the user
        $user = $this->find($userId);

        // my logic to subscribe the user to a newsletter
    }
}

```

If you add a new method in the repository, you must also add the method in the corresponding interface:

```php
<?php
namespace App\Repositories\Contracts;

interface IUser
{
    public function subscribeToNewsletter(int $userId);
}
```
Once this method is defined, you can then call it in the controller just like we did before.

## Criteria
The package uses Criteria classes to add more filters to queries (making the queries more flexible). The package comes with some pre-created Criteria classes that you can use, but you can create more if needed. The following base criteria are available:

- `EagerLoad`
- `ForUser`
- `LatestFirst`
- `WithTrashed`

This is how you use the criteria in your queries:

```php
<?php
namespace App\Http\Controllers;

use Nfunwigabga\LaraRepo\Eloquent\Criteria\EagerLoad; // import the namespaces
use Nfunwigabga\LaraRepo\Eloquent\Criteria\LatestFirst;
use App\Repositories\Contracts\IUser;

class UserController extends Controller
{

    public $userRepo;

    /**
     * Inject the repository interface inside the constructor
     **/
    public function __construct(IUser $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function index()
    {
        // Get all users and eagerload their related posts and comments
        // Don't forget to import the namespaces at the top
        $users = $this->userRepo->withCriteria(
            new EagerLoad(['posts', 'post.comments']), // this is the Criteria class to eagerliad
            new LatestFirst() // order by latest first
        )->all();
    }
}
```
### Generate new criteria
You can also generate new criteria using the command:
```
php artisan make:criteria {Criteria name}
```
For instance:
```
php artisan make:criteria IsLive
```

This will generate a class:
```
app/Repositories/Eloquent\Criteria/IsLive.php
```

A criteria class has only one method: the `apply` method. In the example above, it can be:

```php
<?php 
namespace App\Repositories\Criteria;

use Nfunwigabga\LaraRepo\Base\ICriterion;

class IsLive implements ICriterion
{

    public function __construct()
    {
        
    }

    public function apply($model)
    {
        return $model->whereNotNull('published_at');
    }
    
}
```

This criteria class is then available for use in your controllers, just like we did above.