<?php

namespace AKlump\JsonSchema\Tests\Unit;

use AKlump\JsonSchema\Events\LoadSchemaEvent;
use AKlump\JsonSchema\LoadSchema;
use AKlump\JsonSchema\Tests\Unit\TestingTraits\TestWithFilesTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @covers \AKlump\JsonSchema\LoadSchema
 * @uses \AKlump\JsonSchema\Events\LoadSchemaEvent
 */
class LoadSchemaTest extends TestCase {

  use TestWithFilesTrait;

  public function testSymfonyDispatcherWorksAsExpected() {
    $dispatcher = new EventDispatcher();
    $dispatcher->addListener(LoadSchemaEvent::NAME, function ($event) {
      $this->assertInstanceOf(LoadSchemaEvent::class, $event);
      $id = $event->getId();
      $schema = $event->getSchema();
      $this->assertSame('lorem', $id);
      $foo_schema = json_decode(file_get_contents($this->getTestFileFilepath('foo.schema.json')), TRUE);
      $this->assertSame($foo_schema, $schema);
      $schema['title'] = 'Pine';
      $event->setSchema($schema);

      return $schema;
    });
    $loaded = (new LoadSchema())($this->getTestFileFilepath('foo.schema.json'), $dispatcher);
    $this->assertSame('Pine', json_decode($loaded, TRUE)['title']);
  }

  public function testUnknownDispatcherClassThrows() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#stdClass#');
    (new LoadSchema())($this->getTestFileFilepath('bar.schema.json'), new \stdClass());
  }

  public function testUnknownDispatcherThrows() {
    $this->expectException(\InvalidArgumentException::class);
    (new LoadSchema())($this->getTestFileFilepath('bar.schema.json'), 'unknown');
  }

  public function testDispatcherAsCallbackWorksAsExpected() {
    $loaded = (new LoadSchema())($this->getTestFileFilepath('bar.schema.json'), function (string $id, array $schema) {
      $this->assertSame('lorem', $id);
      $bar_schema = json_decode(file_get_contents($this->getTestFileFilepath('bar.schema.json')), TRUE);
      $this->assertSame($bar_schema, $schema);
      $schema['title'] = 'Evergreen';

      return $schema;
    });
    $this->assertSame('Evergreen', json_decode($loaded, TRUE)['title']);
  }

  public function testInvalidJsonThrows() {
    $this->expectException(\InvalidArgumentException::class);
    (new LoadSchema())('{"invalid JSON');
  }

  public function testFlushCacheWorksAsExpected() {
    // We're going to create a temp schema path and alter the contents.  The
    // cache is based on the filepath, so changing the contents will not break
    // the cache.  Using this strategy we can tell if FlushCache works to return
    // the expected JSON.

    // Setup the test schema file to reflect foo.schema.json
    $this->deleteTestFile('temp.schema.json');
    $temp_schema_path = $this->getTestFileFilepath('test.schema.json');
    $foo_schema = (new LoadSchema())($this->getTestFileFilepath('foo.schema.json'));
    file_put_contents($temp_schema_path, $foo_schema);

    $loaded = (new LoadSchema())($temp_schema_path);
    $this->assertJsonStringEqualsJsonString($foo_schema, $loaded);

    // Change the file contents of tests.schema.json, this will not be picked up
    // right away; not until we manually flush caches.
    $bar_schema = (new LoadSchema())($this->getTestFileFilepath('bar.schema.json'));
    file_put_contents($temp_schema_path, $bar_schema);
    $loaded = (new LoadSchema())($temp_schema_path);
    $this->assertJsonStringNotEqualsJsonString($bar_schema, $loaded);

    // Now the change of contents in test.schema.json will be picked up.
    LoadSchema::flushCache();
    $loaded = (new LoadSchema())($temp_schema_path);
    $this->assertJsonStringEqualsJsonString($bar_schema, $loaded);

    $this->deleteTestFile('temp.schema.json');
  }

  public function testCanInvokeWithJsonString() {
    $schema_path = $this->getTestFileFilepath('foo.schema.json');
    $schema = file_get_contents($schema_path);
    $loaded = (new LoadSchema())($schema);
    $this->assertJsonStringEqualsJsonString($schema, $loaded);
  }

  public function testCanInvokeWithFilepath() {
    $schema = $this->getTestFileFilepath('foo.schema.json');
    $loaded = (new LoadSchema())($schema);
    $this->assertJsonStringEqualsJsonFile($schema, $loaded);
  }

  protected function setUp(): void {
    parent::setUp();
    LoadSchema::flushCache();
  }


}
