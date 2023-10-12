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

  public function __construct(string $name = null, int $id = null) {
    $this->id = $id;
    $this->name = $name;
  }

  public static function from_array(array $data): Wpbb_Team {
    $team = new Wpbb_Team();

    foreach ($data as $key => $value) {
      if (property_exists($team, $key)) {
        $team->$key = $value;
      }
    }

    return $team;
  }

  public function to_array(): array {
    return [
      'id' => $this->id,
      'name' => $this->name,
    ];
  }
}
