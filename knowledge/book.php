<?php

$json = '{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "$id": "lorem",
  "title": "Bar"
}';
$loaded_schema = (new \AKlump\JsonSchema\LoadSchema())($json);
