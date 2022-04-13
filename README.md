# Create multi-users Authentication Laravel App.

This project is a showcase of authentication system for multi-users use. The application will have Teacher and Student, which will be authenticated with the same Register and Login form.
The Teacher and Student can have more custom data such as name, degree, school, speciality...

## Clone this project?

1 - Clone

```shell
$ git clone https://github.com/mehdijai/laravel-multi-user-auth.git
```

```shell
cd laravel-multi-user-auth
```

2 - Install Composer packages

```shell
$ composer install
```

3 - Install NPM packages

```shell
$ npm install
```

4 - Create .env and copy .env.example content.

5 - Setup Database and update .env

6 - Migrate Database changes

```shell
$ php artisan migrate
```

7 - Create pre-defined roles

```shell
$ php artisan db:seed
```

8 - Optimize and clear cache

```shell
$ php artisan optimize:clear
```

9 - Serve application

```shell
$ php artisan serve
```

## Setup Laravel Project

### Create Laravel project

1 - Install Laravel globally with composer if you didn’t already.

```shell
$ composer install laravel
```

2 - Create a new Laravel project with the command bellow:

```shell
$ laravel new multiusers
```

### Setup Breeze

To handle authentication

1 - Install Breeze via composer

```shell
$ composer require laravel/breeze --dev
```

2 - Publish Breeze assets

```shell
$ php artisan breeze:install
```

3 - Install and compile NPM packages

```shell
$ npm install && npm run dev
```

4 - Migrate database

```shell
$ php artisan migrate
```

## Add custom users and roles

### Setup Role

1 - Create “Role” model with migration and controller

```shell
$ php artisan make:model Role -mc
```

2 - Add these line to `Models/Role.php`

```php
class Role extends Model
{
    ...
    protected $table = "roles";
    protected $fillable = ['name'];
}
```

4 - Add "name" field to migration

```php
$table->string("name");
```

### Update User

5 - Add 'role_id' to `$fillable` in `Model/User.php`

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role_id'
];
```

6 - Create migration to add "role_id" field

```shell
$ php artisan make:migration add_role_id --table=users
```

7 - Update migration file:

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->foreignId('role_id')->constrained('roles');
    });
}
...
public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role_id');
    });
}
```

### Create pre-defined roles with Seeders

1 - Update `DatabaseSeeder.php`

```php
use App\Models\Role;
...
Role::create([
    'id' => 1 ,
    'name' => 'teacher'
]);

Role::create([
    'id' => 2 ,
    'name' => 'student'
]);
```

2 - Seed database

```shell
$ php artisan db:seed
```

## Create Custom Users table; Teacher & Student

### Teacher

1 - Create Teacher model

```shell
$ php artisan make:model Teacher -mc
```

2 - Add fillable and relationship to user

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
...
protected $table = 'teachers';
protected $fillable = ['name', 'user_id'];

public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id', 'id');
}
...
```

3 - Add "name" and "user_id" field to migration

```php
$table->foreignId('user_id')->constrained('users');
$table->string("name");
```

### Student

1 - Create Student model

```shell
$ php artisan make:model Student -mc
```

2 - Add fillable and relationship to user

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
...
protected $table = 'students';
protected $fillable = ['name', 'user_id'];

public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id', 'id');
}
...
```

3 - Add "name" and "user_id" field to migration

```php
$table->foreignId('user_id')->constrained('users');
$table->string("name");
```

## Create middleware

1 - Initialize middleware

```shell
$ php artisan make:middleware checkRole
```

2 - Set handle function
This middleware will redirect the user according to its role

```php
public function handle(Request $request, Closure $next, string $role)
{
    if ($role == 'teacher' && auth()->user()->role_id != 1) {
        return redirect()->route('dashboard');
    }

    if ($role == 'student' && auth()->user()->role_id != 2) {
        return redirect()->route('teacher.index');
    }

    return $next($request);
}
```

3 - Register middleware

Update `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
    ...
    'role' => \App\Http\Middleware\checkRole::class
];
```

## Update Register process

### Update Register frontend

We will add an input to enable users to choose their roles

Add the code bellow to `resources/views/auth/register.blade.php` under Email input (Or wherever you want)

```html
<!-- Role -->
<div class="mt-4">
    <x-label for="role" :value="__('Role')" />
    <select class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" name="role" id="role" required :value="old('role')">
        <option value="1">Teacher</option>
        <option value="2">Student</option>
    </select>
</div>
```

You can update the form based on the role. e.g Degree, Seciality, School...

### Update Register logic

Move to `App\Http\Controllers\Auth\RegisterUserController.php`

Update "store" function

```php
use App\Models\Student;
use App\Models\Teacher;
...
$request->validate([
    ...
    'role' => ['required', 'numeric', 'exists:roles,id'],
]);

$user = User::create([
    ...
    'role_id' => $request->role,
]);

if($request->role == 1){
    Teacher::create([
        'name' => $request->name,
        'user_id' => $user->id
    ]);
}else if($request->role == 2){
    Student::create([
        'name' => $request->name,
        'user_id' => $user->id
    ]);
}
```

## Update HOME constant

This is optional!
I changed HOME constant from "/dashboard" to "/student" in `App\Providers\RouteServiceProvider.php`

```php
public const HOME = '/student';
```

NOTE: this will be the default route after authenticating. Be aware of that when you change Routes inside `web.php`

## Create routes system

### Update web.php

Add routes to `web.php`.
The HOME constant will redirect the user to Student route by default. if the user is teacher it will be redirected to teacher router (Role middleware).
So, if you didn't change HOME, you should make one of the bellow prefixes "dashboard" to prevent "Route not found" Errors.

```php
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
...
Route::prefix('teacher')->name('teacher.')->middleware(['auth:sanctum', 'verified', 'role:teacher'])->group(function () {

    Route::get('/', [TeacherController::class, 'index'])->name('index');

});

Route::prefix('student')->middleware(['auth:sanctum', 'verified', 'role:student'])->group(function () {

    Route::get('/', [StudentController::class, 'index'])->name('dashboard');

});
```

### Setup controllers

1 - TeacherController

```php
public function index()
{
    return view("teacher.index");
}
```

2 - StudentController

```php
public function index()
{
    return view("student.index");
}
```

### Create views

Create two folders inside views. One for teachers named `teacher`, the other for students named `student`.
Inside each one create blade file named index; `index.blade.php`.
for testing purposes I will add just the below code. Change `'as Student'` based on the file:

```html
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    You're logged in as Student!
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```
