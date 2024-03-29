<!--
id: readme
tags: ''
-->

# {{ book.title }}

![Loading](../../images/loading.jpg)

Use this to allow dynamic JSON schema loading. It works with [Symfony's EventDispatcher Component](https://symfony.com/doc/current/components/event_dispatcher.html) or with callables; see examples below. When you load a schema either by filename or JSON string for the first time, an event is broadcast to allow for programmatic schema changes. The resulting JSON is then cached and returned. Subsequent calls will receive cached JSON with no event broadcast.

_With Symfony's EventDispatcher Component_

```php
$dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher();
$loaded_schema = (new \AKlump\JsonSchema\LoadSchema())('foo.schema.json', $dispatcher);
$cached_schema = (new \AKlump\JsonSchema\LoadSchema())('foo.schema.json', $dispatcher);
// $loaded_schema === $cached_schema
```

The dispatched event is `\AKlump\JsonSchema\Events\LoadSchemaEvent`.

_With Callable_

```php
$callback = function(string $schema_id, array $schema) {
  // Remove all required properties.
  $schema['required'] = [];
  return $schema;
};
$loaded_schema = (new \AKlump\JsonSchema\LoadSchema())('foo.schema.json', $callback);
```
    
## JSON as Schema Argument

In addition to loading by filepath to the JSON schema file, you may also load with a JSON schema string.

```php
$json = '{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "$id": "lorem",
  "title": "Bar"
}';
$dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher();
$loaded_schema = (new \AKlump\JsonSchema\LoadSchema())($json, $dispatcher);
```

## Clear Schema Cache

```php
\AKlump\JsonSchema\LoadSchema::flushCache();
```

_Note: Schemas are cached globally as a static class variable._

## Related Package(s)
    
* https://github.com/aklump/json-schema-validation

{{ composer_install|raw }}
