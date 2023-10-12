<?php

class Wpbb_Team {
  /**
   * @var int
   */
  public $id;

  /**
   * @var string
   */
  public $name;

  public function __construct($args = []) {
    $this->id = $args['id'] ?? null;
    $this->name = $args['name'] ?? null;
  }

  public static function from_array(array $data): Wpbb_Team {
    $team = new Wpbb_Team($data);

    return $team;
  }

  public function to_array(): array {
    return [
      'id' => $this->id,
      'name' => $this->name,
    ];
  }
}
