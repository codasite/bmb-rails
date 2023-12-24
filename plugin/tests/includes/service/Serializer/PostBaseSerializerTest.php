<?php

use Spatie\Snapshots\MatchesSnapshots;
use WStrategies\BMB\Includes\Service\Serializer\PostBaseSerializer;

class PostBaseSerializerTest extends WPBB_UnitTestCase {
  use MatchesSnapshots;

  public function test_serialize() {
    $bracket = $this->create_bracket([
      'id' => 99999999,
      'title' => 'Test Bracket',
      'published_date' => '2020-01-01 00:00:00',
      'slug' => 'test-bracket',
    ]);

    $serializer = new PostBaseSerializer();
    $serialized = $serializer->serialize($bracket);
    $this->assertEqualSets(
      $serializer->get_serialized_fields(),
      array_keys($serialized)
    );
    $this->assertMatchesSnapshot($serialized);
  }
}
