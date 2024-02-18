<?php

namespace AKlump\JsonSchema\Tests\Unit;

use AKlump\JsonSchema\Events\LoadSchemaEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\JsonSchema\Events\LoadSchemaEvent
 */
final class LoadSchemaEventTest extends TestCase {

  use \AKlump\JsonSchema\Tests\Unit\TestingTraits\TestWithFilesTrait;

  public function testSetSchema() {
    $schema = json_decode(file_get_contents($this->getTestFileFilepath('bar.schema.json')), TRUE);
    $event = new LoadSchemaEvent('bar', []);
    $this->assertSame('bar', $event->getId());
    $this->assertNotSame($schema, $event->getSchema());
    $event->setSchema($schema);
    $this->assertSame($schema, $event->getSchema());
  }

  public function testConstruct() {
    $schema = json_decode(file_get_contents($this->getTestFileFilepath('bar.schema.json')), TRUE);
    $event = new LoadSchemaEvent('bar', $schema);
    $this->assertSame('bar', $event->getId());
    $this->assertSame($schema, $event->getSchema());
  }
}
