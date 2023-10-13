# Associate publishing with Eloquent models

[![Latest Version](https://img.shields.io/github/release/leko/laravel-published.svg?style=flat-square)](https://github.com/leko/laravel-published/releases)

This package can associate published date with Eloquent models. It provides a
simple API to work with.

## Installation

The library can be installed via Composer:

```bash
composer require leko-team/laravel-published
```

## Configuration

To be able publishing eloquent entities you need:

* Add migration with column `published_at` or assign `static::PUBLISHED_AT` constant with the name of the column you want to use.

```php
php artisan make:migration add_published_at_column_in_`your-table`_table
```

```php
Schema::table('your-table', function (Blueprint $table) {
    $table->timestamp('published_at')->nullable()->index();
});
```

If you have existing records in your table you maybe want to update them with current date.
Or perhaps you want to add current date as default value.

* Add trait `PublishedTrait` to your model.

```php
use PublishedTrait;
```

## Examples

### Base

To publish entity:
```php
$review = Review::first();
$review->publish();
```

Publish by specific datetime:
```php
$review = Review::first();
$review->publish(pass here carbon entity);
```

To unpublish entity:
```php
$review = Review::first();
$review->unpublish();
```

### Scopes

By default from published entity return only published records.
You can change this by applying scope to your Eloquent model.

* notPublished
```php
$review = Review::notPublished()->get();
```
Returns all not published record with null in `published_at` include records that must be published in the future.

* withUnpublished
```php
$review = Review::withUnpublished()->get();
```
Returns all records.

* withoutPublished
```php
$review = Review::withoutPublished()->get();
```
Returns all unpublished record with null in `published_at`.
## Credits

A big thank you to [Laravel Package](https://www.laravelpackage.com/) for helping out build package with step by step guide.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
