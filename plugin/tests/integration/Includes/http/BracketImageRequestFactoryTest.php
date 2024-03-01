<?php

use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\PostBracketInterface;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Service\Http\BracketImageRequestFactory;
use WStrategies\BMB\Includes\Service\ObjectStorage\ObjectStorageInterface;

class BracketImageRequestFactoryTest extends WPBB_UnitTestCase {
  public function test_get_request_data() {
    $bracket_mock = $this->createMock(PostBracketInterface::class);
    $bracket_mock->method('get_post_id')->willReturn(1);
    $bracket_mock->method('get_title')->willReturn('Test Bracket');
    $bracket_mock->method('get_date')->willReturn('2020-01-01');
    $bracket_mock->method('get_num_teams')->willReturn(4);
    $bracket_mock->method('get_matches')->willReturn([
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team([
          'id' => 1,
          'name' => 'Team 1',
        ]),
        'team2' => new Team([
          'id' => 2,
          'name' => 'Team 2',
        ]),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 1,
        'team1' => new Team([
          'id' => 3,
          'name' => 'Team 3',
        ]),
        'team2' => new Team([
          'id' => 4,
          'name' => 'Team 4',
        ]),
      ]),
    ]);
    $bracket_mock->method('get_picks')->willReturn([
      new Pick([
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => $bracket_mock->get_matches()[0]->team1->id,
      ]),
      new Pick([
        'round_index' => 0,
        'match_index' => 1,
        'winning_team_id' => $bracket_mock->get_matches()[1]->team2->id,
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 0,
        'winning_team_id' => $bracket_mock->get_matches()[0]->team1->id,
      ]),
    ]);

    $object_storage = $this->createMock(ObjectStorageInterface::class);

    $object_storage->method('get_upload_options')->willReturn([
      'test' => 'test',
    ]);

    $object_storage->method('get_service_name')->willReturn('testServiceName');

    $request_factory = new BracketImageRequestFactory([
      'object_storage' => $object_storage,
    ]);

    $request_data = $request_factory->get_request_data($bracket_mock, [
      'path' => 'http://react-server:8080/test',
      'method' => 'POST',
      'headers' => [
        'Content-Type' => 'application/json',
      ],
      'inch_height' => 10,
      'inch_width' => 10,
      'themes' => ['light'],
      'positions' => ['top'],
    ]);

    $body = json_encode([
      'inchHeight' => 10,
      'inchWidth' => 10,
      'storageService' => 'testServiceName',
      'storageOptions' => [
        'test' => 'test',
      ],
      'queryParams' => [
        'title' => 'Test Bracket',
        'date' => '2020-01-01',
        'inch_height' => 10,
        'inch_width' => 10,
        'num_teams' => 4,
        'picks' => [
          [
            'id' => null,
            'round_index' => 0,
            'match_index' => 0,
            'winning_team' => null,
            'winning_team_id' => 1,
          ],
          [
            'id' => null,
            'round_index' => 0,
            'match_index' => 1,
            'winning_team' => null,
            'winning_team_id' => 4,
          ],
          [
            'id' => null,
            'round_index' => 1,
            'match_index' => 0,
            'winning_team' => null,
            'winning_team_id' => 1,
          ],
        ],
        'matches' => [
          [
            'id' => null,
            'round_index' => 0,
            'match_index' => 0,
            'team1' => [
              'id' => 1,
              'name' => 'Team 1',
            ],
            'team2' => [
              'id' => 2,
              'name' => 'Team 2',
            ],
          ],
          [
            'id' => null,
            'round_index' => 0,
            'match_index' => 1,
            'team1' => [
              'id' => 3,
              'name' => 'Team 3',
            ],
            'team2' => [
              'id' => 4,
              'name' => 'Team 4',
            ],
          ],
        ],
        'theme' => 'light',
        'position' => 'top',
      ],
    ]);

    $expected = [
      'top_light' => [
        'url' => 'http://react-server:8080/test',
        'method' => 'POST',
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => $body,
      ],
    ];

    $this->assertEquals($expected, $request_data);
  }

  public function test_get_request_data_pdf() {
    $bracket_mock = $this->createMock(PostBracketInterface::class);
    $bracket_mock->method('get_post_id')->willReturn(1);
    $bracket_mock->method('get_title')->willReturn('Test Bracket');
    $bracket_mock->method('get_date')->willReturn('2020-01-01');
    $bracket_mock->method('get_num_teams')->willReturn(4);
    $bracket_mock->method('get_matches')->willReturn([
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team([
          'id' => 1,
          'name' => 'Team 1',
        ]),
        'team2' => new Team([
          'id' => 2,
          'name' => 'Team 2',
        ]),
      ]),
    ]);
    $bracket_mock->method('get_picks')->willReturn([
      new Pick([
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => 1,
      ]),
    ]);

    $object_storage = $this->createMock(ObjectStorageInterface::class);

    $object_storage->method('get_upload_options')->willReturn([
      'test' => 'test',
    ]);

    $object_storage->method('get_service_name')->willReturn('testServiceName');

    $request_factory = new BracketImageRequestFactory([
      'object_storage' => $object_storage,
    ]);

    $request_data = $request_factory->get_request_data($bracket_mock, [
      'path' => 'http://react-server:8080/test',
      'method' => 'POST',
      'headers' => [
        'Content-Type' => 'application/json',
      ],
      'inch_height' => 10,
      'inch_width' => 10,
      'themes' => ['light'],
      'positions' => ['top'],
      'pdf' => true,
    ]);

    $expected_body = json_encode([
      'inchHeight' => 10,
      'inchWidth' => 10,
      'storageService' => 'testServiceName',
      'pdf' => true,
      'storageOptions' => [
        'test' => 'test',
      ],
      'queryParams' => [
        'title' => 'Test Bracket',
        'date' => '2020-01-01',
        'inch_height' => 10,
        'inch_width' => 10,
        'num_teams' => 4,
        'picks' => [
          [
            'id' => null,
            'round_index' => 0,
            'match_index' => 0,
            'winning_team' => null,
            'winning_team_id' => 1,
          ],
        ],
        'matches' => [
          [
            'id' => null,
            'round_index' => 0,
            'match_index' => 0,
            'team1' => [
              'id' => 1,
              'name' => 'Team 1',
            ],
            'team2' => [
              'id' => 2,
              'name' => 'Team 2',
            ],
          ],
        ],
        'theme' => 'light',
        'position' => 'top',
      ],
    ]);

    $expected = [
      'top_light' => [
        'url' => 'http://react-server:8080/test',
        'method' => 'POST',
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => $expected_body,
      ],
    ];

    $this->assertEquals($expected, $request_data);
  }

  public function test_get_multiple_requests() {
    $bracket = $this->create_bracket([
      'title' => 'Test Bracket',
      'date' => '2020-01-01',
      'num_teams' => 4,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);
    $object_storage = $this->createMock(ObjectStorageInterface::class);

    $request_factory = $this->getMockBuilder(BracketImageRequestFactory::class)
      ->onlyMethods(['create_body'])
      ->setConstructorArgs([
        'args' => [
          'object_storage' => $object_storage,
        ],
      ])
      ->getMock();

    $request_factory->method('create_body')->willReturn('testBody');

    $request_data = $request_factory->get_request_data($bracket, [
      'path' => 'http://react-server:8080/test',
      'method' => 'POST',
      'headers' => [
        'Content-Type' => 'application/json',
      ],
      'inch_height' => 10,
      'inch_width' => 10,
      'themes' => ['light', 'dark'],
      'positions' => ['top', 'center'],
    ]);

    $expected = [
      'top_light' => [
        'url' => 'http://react-server:8080/test',
        'method' => 'POST',
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => 'testBody',
      ],
      'center_light' => [
        'url' => 'http://react-server:8080/test',
        'method' => 'POST',
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => 'testBody',
      ],
      'top_dark' => [
        'url' => 'http://react-server:8080/test',
        'method' => 'POST',
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => 'testBody',
      ],
      'center_dark' => [
        'url' => 'http://react-server:8080/test',
        'method' => 'POST',
        'headers' => [
          'Content-Type' => 'application/json',
        ],
        'body' => 'testBody',
      ],
    ];

    $this->assertEquals($expected, $request_data);
  }
}
