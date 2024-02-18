<?php
// SPDX-License-Identifier: BSD-3-Clause
namespace AKlump\JsonSchema\Events;

use Symfony\Contracts\EventDispatcher\Event;

class LoadSchemaEvent extends Event {

  const NAME = 'json_schema.load';

  private array $schema;

  private string $id;

  public function __construct(string $id, array $schema) {
    $this->id = $id;
    $this->setSchema($schema);
  }

  public function getSchema(): array {
    return $this->schema;
  }

  public function setSchema(array $schema): self {
    $this->schema = $schema;

    return $this;
  }

  public function getId(): string {
    return $this->id;
  }

}
