# JSON Schema Loading

![Loading](images/loading.jpg)

Use this to allow dynamic JSON schema loading. It works with [Symfony's The EventDispatcher Component](https://symfony.com/doc/current/components/event_dispatcher.html) or with callables; see examples below. When you load a schema either by filename or JSON string for the first time, an event is broadcast to allow for programmatic schema changes. The resulting JSON is then cached and returned. Subsequent calls will receive cached JSON with no event broadcast.

```text
.
└── json_schema
    └── foo.schema.json
```

_With Symfony's EventDispatcher Component_

```php
$dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher();
$loaded_schema = (new \AKlump\JsonSchema\LoadSchema())('json_schema/foo.schema.json', $dispatcher);
$cached_schema = (new \AKlump\JsonSchema\LoadSchema())('json_schema/foo.schema.json', $dispatcher);
// $loaded_schema === $cached_schema
```

The dispatched event is `\AKlump\JsonSchema\Events\LoadSchemaEvent`.

_With Callable_

```php
$callback = function(string $schema_id, array $schema) {
  // Remove all required propreties
  $schema['required'] = [];
  return $schema;
};
$loaded_schema = (new \AKlump\JsonSchema\LoadSchema())('json_schema/foo.schema.json', $callback);
```

## Clear Schema Cache

```php
\AKlump\JsonSchema\LoadSchema::flushCache();
```

_Note: Schemas are cached globally as a static class variable._

## Related Package(s)

* https://github.com/aklump/json-schema-validation

## Install with Composer

1. Because this is an unpublished package, you must define it's repository in your project's _composer.json_ file. Add the following to _composer.json_:

    ```json
    "repositories": [
        {
            "type": "github",
            "url": "https://github.com/aklump/json-schema-loading"
        }
    ]
    ```

1. Then `composer require aklump/json-schema-loading:@dev`
