<?php

?>
<div id="wpbb-admin-settings-panel">
    <!DOCTYPE html>
    <html>

    <body>
        <h1>Max Team Settings</h1>
        <?php

            require_once plugin_dir_path(dirname(__FILE__)) . '../includes/repository/class-wp-bracket-builder-bracket-repo.php';
            const bracket_repo = new Wp_Bracket_Builder_Bracket_Repository();
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $maxTeams = $_POST["maxTeam"];
                bracket_repo->add_max_teams($maxTeams);
            }
            $max_team_info = bracket_repo->get_max_teams();

        ?>
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <label for="name">Maximum number of team to participate:</label>
            <input type="number" id="maxTeam" name="maxTeam" value="<?if (isset($max_team_info['max_teams'])) { echo $max_team_info['max_teams']; } ?>" required><br><br>

            <input type="submit" value="Submit">
        </form>
    </body>

    </html>

</div>