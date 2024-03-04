<?php
namespace WStrategies\BMB\tests\integration\factory;
use WP_Error;
use WP_UnitTest_Factory_For_Thing;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\PlayRepo;

/**
 * Class WPBB_UnitTest_Factory_For_Play
 *
 * This class is used to create template objects for unit testing
 */
class PlayTestFactory extends WP_UnitTest_Factory_For_Thing {
  private $play_repo;

  function __construct($factory = null) {
    parent::__construct($factory);
    $this->play_repo = new PlayRepo();

    $this->default_generation_definitions = ['author' => 1];
  }

  function create_object($args): Play|WP_Error|int|null {
    $play = new Play($args);
    $play = $this->play_repo->add($play);
    return $play;
  }

  function update_object($play_id, $fields): Play|WP_Error|int|null {
    $play = $this->play_repo->update($play_id, $fields);
    return $play;
  }

  function get_object_by_id($play_id) {
    return $this->play_repo->get($play_id);
  }
}
