<?php

namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketTeamRepo;

class BracketAdminHooks implements HooksInterface {
  private $bracket_repo;
  private $team_repo;
  public function __construct($args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->team_repo = $args['team_repo'] ?? new BracketTeamRepo();
  }
  public function load(Loader $loader): void {
    $loader->add_action('add_meta_boxes_bracket', [$this, 'add_meta_boxes']);
    $loader->add_action('save_post_bracket', [
      $this,
      'save_bracket_teams_meta_box',
    ]);
  }

  public function add_meta_boxes(): void {
    add_meta_box(
      'bracket_teams_meta_box',
      'Teams',
      [$this, 'display_bracket_teams_meta_box'],
      'bracket',
      'normal',
      'high'
    );
  }

  public function display_bracket_teams_meta_box($post): void {
    $bracket = $this->bracket_repo->get($post->ID);
    if (!$bracket) {
      return;
    }
    $matches = $bracket->matches;
    wp_nonce_field('bracket_teams_meta_box', 'bracket_teams_meta_box_nonce');
    ?>
    <div class="wpbb-bracket-teams-table-container">
		<table class="wpbb-bracket-teams-table">
			<thead>
				<tr>
					<th>Round</th>
					<th>Match</th>
					<th>Team 1</th>
					<th>Team 2</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($matches as $match) { ?>
					<tr>
						<td><?php echo $match->round_index + 1; ?></td>
						<td><?php echo $match->match_index + 1; ?></td>
						<td>
              <?php if ($match->team1) { ?>
                <input
                  type="text"
                  name="bracket_teams[<?php echo $match->team1->id; ?>]"
                  id="bracket_teams[<?php echo $match->team1->id; ?>]"
                  value="<?php echo $match->team1->name; ?>"
                />
              <?php } ?>
						</td>
						<td>
              <?php if ($match->team2) { ?>
                <input
                  type="text"
                  name="bracket_teams[<?php echo $match->team2->id; ?>]"
                  id="bracket_teams[<?php echo $match->team2->id; ?>]"
                  value="<?php echo $match->team2->name; ?>"
                />
              <?php } ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
    </div>
		<?php
  }

  public function save_bracket_teams_meta_box($post_id): void {
    // Check if our nonce is set.
    if (!isset($_POST['bracket_teams_meta_box_nonce'])) {
      return;
    }

    // Verify that the nonce is valid.
    if (
      !wp_verify_nonce(
        $_POST['bracket_teams_meta_box_nonce'],
        'bracket_teams_meta_box'
      )
    ) {
      return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    // Check the user's permissions.
    if (
      isset($_POST['post_type']) &&
      Bracket::get_post_type() == $_POST['post_type']
    ) {
      if (!current_user_can('edit_page', $post_id)) {
        return;
      }
    } else {
      if (!current_user_can('edit_post', $post_id)) {
        return;
      }
    }
    // Make sure that it is set.
    if (!isset($_POST['bracket_teams'])) {
      return;
    }

    // update the teams
    foreach ($_POST['bracket_teams'] as $team_id => $team_name) {
      $team = $this->team_repo->get($team_id);
      if ($team && $team->name !== $team_name) {
        $team->name = $team_name;
        $this->team_repo->update($team_id, $team);
      }
    }
  }
}
