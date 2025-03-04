<?php
namespace WStrategies\BMB\tests\integration\Public\Partials;

use WStrategies\BMB\Features\MobileApp\MobileAppUtils;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Public\Partials\CelebrityBracketsPage;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class CelebrityBracketsPageTest extends WPBB_UnitTestCase {
  private $page;
  private $mobile_app_utils;
  private $bracket_with_fee;
  private $bracket_without_fee;
  private $play;

  public function set_up(): void {
    parent::set_up();

    // Create test brackets and plays
    $this->bracket_with_fee = $this->create_bracket();
    update_post_meta($this->bracket_with_fee->id, 'bracket_fee', '10');
    wp_set_post_tags($this->bracket_with_fee->id, ['bmb_vip_featured'], true);

    $this->bracket_without_fee = $this->create_bracket();
    wp_set_post_tags(
      $this->bracket_without_fee->id,
      ['bmb_vip_featured'],
      true
    );

    $this->play = $this->create_play([
      'bracket_id' => $this->bracket_without_fee->id,
    ]);
    wp_set_post_tags($this->play->id, ['bmb_vip_featured'], true);

    // Mock MobileAppUtils
    $this->mobile_app_utils = $this->createMock(MobileAppUtils::class);

    // Create page instance with mocked utils
    $this->page = new CelebrityBracketsPage([
      'mobile_app_utils' => $this->mobile_app_utils,
    ]);
  }

  public function test_get_current_page_returns_one_when_no_paged_query_var(): void {
    $this->assertEquals(1, $this->page->get_current_page());
  }

  public function test_get_current_page_returns_paged_value(): void {
    set_query_var('paged', 2);
    $this->assertEquals(2, $this->page->get_current_page());
    set_query_var('paged', null);
  }

  public function test_mobile_app_request_filters_out_paid_brackets(): void {
    // Set mobile app request to true
    $this->mobile_app_utils->method('is_mobile_app_request')->willReturn(true);

    $result = $this->page->get_brackets_and_plays();
    $brackets_and_plays = $result['brackets_and_plays'];

    // Should only include free bracket and play
    $this->assertCount(2, $brackets_and_plays);

    // Verify the paid bracket is not included
    foreach ($brackets_and_plays as $item) {
      if ($item instanceof Bracket) {
        $this->assertNotEquals($this->bracket_with_fee->id, $item->id);
      }
    }
  }

  public function test_web_request_includes_all_brackets(): void {
    // Set mobile app request to false
    $this->mobile_app_utils->method('is_mobile_app_request')->willReturn(false);

    $result = $this->page->get_brackets_and_plays();
    $brackets_and_plays = $result['brackets_and_plays'];

    // Should include all brackets and plays
    $this->assertCount(3, $brackets_and_plays);

    // Verify both brackets are included
    $found_bracket_with_fee = false;
    $found_bracket_without_fee = false;
    $found_play = false;

    foreach ($brackets_and_plays as $item) {
      if ($item instanceof Bracket) {
        if ($item->id === $this->bracket_with_fee->id) {
          $found_bracket_with_fee = true;
        }
        if ($item->id === $this->bracket_without_fee->id) {
          $found_bracket_without_fee = true;
        }
      }
      if ($item instanceof Play) {
        if ($item->id === $this->play->id) {
          $found_play = true;
        }
      }
    }

    $this->assertTrue(
      $found_bracket_with_fee,
      'Paid bracket should be included'
    );
    $this->assertTrue(
      $found_bracket_without_fee,
      'Free bracket should be included'
    );
    $this->assertTrue($found_play, 'Play should be included');
  }

  public function test_pagination_works_correctly(): void {
    // Create enough brackets to trigger pagination
    for ($i = 0; $i < 7; $i++) {
      $bracket = $this->create_bracket();
      wp_set_post_tags($bracket->id, ['bmb_vip_featured'], true);
    }

    // Test first page
    $result = $this->page->get_brackets_and_plays();
    $this->assertCount(6, $result['brackets_and_plays']); // posts_per_page is 6
    $this->assertEquals(2, $result['num_pages']); // Should have 2 pages

    // Test second page
    set_query_var('paged', 2);
    $result = $this->page->get_brackets_and_plays();
    $this->assertGreaterThan(0, count($result['brackets_and_plays']));
    $this->assertEquals(2, $result['num_pages']);
    set_query_var('paged', null);
  }

  public function test_render_returns_string(): void {
    $this->mobile_app_utils->method('is_mobile_app_request')->willReturn(false);

    $output = $this->page->render();
    $this->assertIsString($output);
    $this->assertStringContainsString('Celebrity Brackets', $output);
    $this->assertStringContainsString('Featured', $output);
  }

  public function test_convert_post_to_entity_returns_correct_type(): void {
    $bracket_post = get_post($this->bracket_without_fee->id);
    $play_post = get_post($this->play->id);

    $reflection = new \ReflectionClass($this->page);
    $method = $reflection->getMethod('convert_post_to_entity');
    $method->setAccessible(true);

    $bracket_result = $method->invoke($this->page, $bracket_post);
    $play_result = $method->invoke($this->page, $play_post);

    $this->assertInstanceOf(Bracket::class, $bracket_result);
    $this->assertInstanceOf(Play::class, $play_result);
  }

  public function test_mobile_meta_query_structure(): void {
    $reflection = new \ReflectionClass($this->page);
    $method = $reflection->getMethod('get_mobile_meta_query');
    $method->setAccessible(true);

    $meta_query = $method->invoke($this->page);

    $this->assertEquals('OR', $meta_query['relation']);
    $this->assertCount(2, array_filter($meta_query, 'is_array'));

    // Check first condition (bracket_fee = 0)
    $this->assertEquals('bracket_fee', $meta_query[0]['key']);
    $this->assertEquals('0', $meta_query[0]['value']);
    $this->assertEquals('=', $meta_query[0]['compare']);

    // Check second condition (bracket_fee NOT EXISTS)
    $this->assertEquals('bracket_fee', $meta_query[1]['key']);
    $this->assertEquals('NOT EXISTS', $meta_query[1]['compare']);
  }
}
