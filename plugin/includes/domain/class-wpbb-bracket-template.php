<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-post-base.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-custom-post-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-match.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-validation-exception.php';

class Wpbb_BracketTemplate extends Wpbb_PostBase
{
  /**
   * @var string
   */
  public $date;
  /**
   * @var int
   */
  public $num_teams;

  /**
   * @var int
   */
  public $wildcard_placement;

  /**
   * @var string
   *
   * HTML representation of the bracket. Used to generate bracket images.
   */
  public $html;

  /**
   * @var string
   *
   * URL of the bracket image
   */
  public $img_url;

  /**
   * @var Wpbb_Match[] Array of Wpbb_Match objects
   */
  public $matches;

  public function __construct(array $data = [])
  {
    parent::__construct($data);
    $this->date = $data['date'] ?? null;
    $this->num_teams = (int) ($data['num_teams'] ?? null);
    $this->wildcard_placement = (int) ($data['wildcard_placement'] ?? null);
    $this->matches = $data['matches'] ?? [];
  }

  public function get_num_rounds(): int
  {
    if (!$this->num_teams) {
      return 0;
    }
    return ceil(log($this->num_teams, 2));
  }

  public static function get_post_type(): string
  {
    return 'bracket_template';
  }

  public function get_post_meta(): array
  {
    return [
      'num_teams' => $this->num_teams,
      'wildcard_placement' => $this->wildcard_placement,
      'date' => $this->date,
    ];
  }

  public function get_update_post_meta(): array
  {
    return [
      'date' => $this->date,
    ];
  }

  /**
   * @throws Wpbb_ValidationException
   */
  public static function from_array(array $data): Wpbb_BracketTemplate
  {
    $requiredFields = [
      'num_teams',
      'wildcard_placement',
      'date',
      'author',
      'title',
      'matches',
    ];
    validateRequiredFields($data, $requiredFields);
    $matches = [];
    foreach ($data['matches'] as $match) {
      $matches[] = Wpbb_Match::from_array($match);
    }
    $data['matches'] = $matches;
    return new Wpbb_BracketTemplate($data);
  }

  public function to_array(): array
  {
    $template = parent::to_array();
    $template['num_teams'] = $this->num_teams;
    $template['wildcard_placement'] = $this->wildcard_placement;
    $template['date'] = $this->date;
    if ($this->matches) {
      $matches = [];
      foreach ($this->matches as $match) {
        $matches[] = $match->to_array();
      }
      $template['matches'] = $matches;
    }
    return $template;
  }
}
