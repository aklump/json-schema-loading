<?php
// SPDX-License-Identifier: BSD-3-Clause
namespace AKlump\JsonSchema;

use AKlump\JsonSchema\Events\LoadSchemaEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class LoadSchemaEvent
 *
 * This class handles loading and cacheing of  JSON schemas, with optional event
 * dispatching for dynamic schema modifications on first load.
 */
final class LoadSchema {

  private static array $schemas;

  private string $json;

  private string $fallbackId;

  /**
   * Load a JSON schema from a file.
   *
   * This method will load it, broadcast an event that can alter it, and cache
   * the results.  Subsequent calls for the same filepath will bypass all that
   * and returned the earlier cached result.
   *
   * @param string $json_schema Either a JSON string or a filepath to a schema
   * file to load from.
   * @param
   * callable|\Symfony\Component\EventDispatcher\EventDispatcherInterface|null
   * $dispatcher (optional) The dispatcher for dynamic manipulation of schemas
   * during first load.  See documentation for supported dispatchers.  When
   * $dispatcher is a callable, it will receive (string $schema_id, array
   * $schema).
   *
   * @return string The loaded JSON schema in JSON form
   * @throws \InvalidArgumentException if the schema file does not exist.
   *
   */
  public function __invoke(string $json_schema, $dispatcher = NULL): string {
    $this->tryValidateArgument($json_schema);
    $cache_id = $this->getCacheId($json_schema);
    if (isset(self::$schemas[$cache_id])) {
      return self::$schemas[$cache_id];
    }

    $schema = $this->getSchemaAsArray($json_schema);
    if ($dispatcher) {
      $schema_id = $this->getID($schema);
      $this->broadcastEvent($dispatcher, $schema_id, $schema);
    }
    self::$schemas[$cache_id] = json_encode($schema);

    return self::$schemas[$cache_id];
  }

  private function getId(array $schema): string {
    return $schema['$id'] ?? $this->fallbackId;
  }

  /**
   * Broadcasts an event using the given dispatcher.
   *
   * @param mixed $dispatcher The event dispatcher. It can be an instance of EventDispatcherInterface or a callable.
   * @param string $schema_id The ID of the schema.
   * @param array $schema The schema to be modified.
   *
   * @throws \InvalidArgumentException When the dispatcher is not an instance of EventDispatcherInterface and not callable.
   */
  private function broadcastEvent($dispatcher, string $schema_id, array &$schema) {
    if ($dispatcher instanceof EventDispatcherInterface) {
      $event = new LoadSchemaEvent($schema_id, $schema);
      $dispatcher->dispatch($event, LoadSchemaEvent::NAME);
      $schema = $event->getSchema();

      return;
    }
    elseif (is_callable($dispatcher)) {
      $schema = $dispatcher($schema_id, $schema);

      return;
    }

    $more_info = '';
    if (is_object($dispatcher)) {
      $more_info = ': ' . get_class($dispatcher);
    }
    throw new \InvalidArgumentException(sprintf('Unknown dispatcher%s.', $more_info));
  }

  /**
   * Clears the cached JSON schemas.
   *
   */
  public static function flushCache(): void {
    self::$schemas = [];
  }

  /**
   * Check if a string is a valid JSON schema.
   *
   * This method checks if a given string contains the opening curly brace '{',
   * which is a characteristic of JSON schema syntax.
   *
   * @param string $subject The string to be checked for JSON schema syntax.
   *
   * @return bool `True` if the string is a valid JSON schema, `false` otherwise.
   */
  private function isStringSchemaJSON(string $subject): bool {
    return strstr($subject, '{');
  }

  /**
   * Tries to validate the given JSON schema argument.
   *
   * @param string $json_schema The argument to validate; this should be a path
   * to an existing schema file, or valid JSON representing a schema.
   *
   * @throws \InvalidArgumentException If the JSON schema argument is not valid.
   */
  private function tryValidateArgument(string $json_schema) {
    $is_file = file_exists($json_schema) || !$this->isStringSchemaJSON($json_schema);
    if ($is_file) {
      unset($this->json);
      $this->fallbackId = basename($json_schema);
    }
    else {
      $decoded = json_decode($json_schema, TRUE);
      if (!is_array($decoded)) {
        throw new \InvalidArgumentException(sprintf('%s', $json_schema));
      }
      $this->json = $json_schema;
    }
  }

  private function getSchemaAsArray(string $json_schema): array {
    if (!isset($this->json)) {
      $this->json = file_get_contents($json_schema);
    }

    return json_decode($this->json, TRUE);
  }

  private function getCacheId(string $json_schema) {
    return hash('sha256', $json_schema);
  }

}
