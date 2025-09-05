# This file should ensure the existence of records required to run the application in every environment (production,
# development, test). The code here should be idempotent so that it can be executed at any point in every environment.

puts "Seeding database..."

# Create sample users
users = [
  {
    username: "admin",
    email: "admin@bracketmakerbuilder.com",
    password: "password",
    first_name: "Admin",
    last_name: "User"
  },
  {
    username: "johndoe",
    email: "john@example.com",
    password: "password",
    first_name: "John",
    last_name: "Doe"
  },
  {
    username: "janedoe",
    email: "jane@example.com",
    password: "password",
    first_name: "Jane",
    last_name: "Doe"
  },
  {
    username: "mikejohnson",
    email: "mike@example.com",
    password: "password",
    first_name: "Mike",
    last_name: "Johnson"
  },
  {
    username: "sarahwilson",
    email: "sarah@example.com",
    password: "password",
    first_name: "Sarah",
    last_name: "Wilson"
  }
]

created_users = users.map do |user_data|
  User.find_or_create_by(email: user_data[:email]) do |user|
    user.username = user_data[:username]
    user.password = user_data[:password]
    user.first_name = user_data[:first_name]
    user.last_name = user_data[:last_name]
  end
end

puts "Created #{created_users.length} users"

# Create sample teams for March Madness style bracket
teams_data = [
  # 1 seeds
  { name: "Duke", seed: 1 },
  { name: "Gonzaga", seed: 1 },
  { name: "Arizona", seed: 1 },
  { name: "Baylor", seed: 1 },

  # 2 seeds
  { name: "Kentucky", seed: 2 },
  { name: "Auburn", seed: 2 },
  { name: "Kansas", seed: 2 },
  { name: "Villanova", seed: 2 },

  # 3 seeds
  { name: "Texas Tech", seed: 3 },
  { name: "Purdue", seed: 3 },
  { name: "Tennessee", seed: 3 },
  { name: "Wisconsin", seed: 3 },

  # 4 seeds
  { name: "Arkansas", seed: 4 },
  { name: "Illinois", seed: 4 },
  { name: "UCLA", seed: 4 },
  { name: "Providence", seed: 4 },

  # Lower seeds
  { name: "UConn", seed: 5 },
  { name: "Houston", seed: 5 },
  { name: "Iowa", seed: 5 },
  { name: "Saint Mary's", seed: 5 },

  { name: "LSU", seed: 6 },
  { name: "Texas", seed: 6 },
  { name: "Alabama", seed: 6 },
  { name: "Colorado State", seed: 6 },

  { name: "Murray State", seed: 7 },
  { name: "USC", seed: 7 },
  { name: "Michigan State", seed: 7 },
  { name: "Ohio State", seed: 7 },

  { name: "North Carolina", seed: 8 },
  { name: "San Diego State", seed: 8 },
  { name: "Seton Hall", seed: 8 },
  { name: "Loyola Chicago", seed: 8 }
]

teams = teams_data.map do |team_data|
  Team.find_or_create_by(name: team_data[:name]) do |team|
    team.seed = team_data[:seed]
  end
end

puts "Created #{teams.length} teams"

# Create sample brackets
brackets_data = [
  {
    title: "March Madness 2024",
    month: "March",
    year: "2024",
    num_teams: 32,
    is_voting: false,
    round_names: ["First Round", "Second Round", "Sweet 16", "Elite 8", "Final Four", "Championship"]
  },
  {
    title: "Best Movie Bracket",
    month: "January",
    year: "2024",
    num_teams: 16,
    is_voting: true,
    round_names: ["Round 1", "Round 2", "Semifinals", "Finals"]
  },
  {
    title: "Office Bracket Challenge",
    month: "February",
    year: "2024",
    num_teams: 8,
    is_voting: false,
    fee: 10.00,
    round_names: ["Quarterfinals", "Semifinals", "Finals"]
  }
]

brackets = brackets_data.map do |bracket_data|
  Bracket.find_or_create_by(title: bracket_data[:title]) do |bracket|
    bracket.month = bracket_data[:month]
    bracket.year = bracket_data[:year]
    bracket.num_teams = bracket_data[:num_teams]
    bracket.is_voting = bracket_data[:is_voting]
    bracket.fee = bracket_data[:fee]
    bracket.round_names = bracket_data[:round_names]
    bracket.slug = bracket_data[:title].parameterize
  end
end

puts "Created #{brackets.length} brackets"

# Create some sample matches for the first bracket (March Madness)
march_madness = brackets.first
if march_madness&.bracket_matches&.empty?
  # First round matches (16 matches for 32 teams)
  first_round_teams = teams.first(32)

  first_round_teams.each_slice(2).with_index do |match_teams, index|
    BracketMatch.create!(
      bracket: march_madness,
      team1: match_teams[0],
      team2: match_teams[1],
      round: 1,
      position: index + 1
    )
  end

  # Second round matches (8 matches, no teams assigned yet)
  8.times do |i|
    BracketMatch.create!(
      bracket: march_madness,
      round: 2,
      position: i + 1
    )
  end

  # Sweet 16 matches (4 matches)
  4.times do |i|
    BracketMatch.create!(
      bracket: march_madness,
      round: 3,
      position: i + 1
    )
  end

  # Elite 8 matches (2 matches)
  2.times do |i|
    BracketMatch.create!(
      bracket: march_madness,
      round: 4,
      position: i + 1
    )
  end

  # Championship match
  BracketMatch.create!(
    bracket: march_madness,
    round: 5,
    position: 1
  )

  puts "Created bracket matches for March Madness bracket"
end

# Create some sample plays
march_madness = Bracket.find_by(title: "March Madness 2024")
if march_madness
  created_users.each_with_index do |user, index|
    next if march_madness.plays.exists?(user: user)

    play = Play.create!(
      bracket: march_madness,
      user: user,
      score: rand(0..100),
      picks_data: {
        "round_1" => Array.new(16) { rand(1..2) },
        "round_2" => Array.new(8) { rand(1..2) },
        "round_3" => Array.new(4) { rand(1..2) },
        "round_4" => Array.new(2) { rand(1..2) },
        "round_5" => [rand(1..2)]
      }
    )

    # Complete some plays randomly
    if rand < 0.7
      play.update!(completed_at: rand(1..30).days.ago)
    end
  end

  puts "Created sample plays for March Madness bracket"
end

# Create some sample results (simulate some completed matches)
if march_madness
  first_round_matches = march_madness.bracket_matches.where(round: 1).limit(8)

  first_round_matches.each do |match|
    if match.team1 && match.team2
      winner = [match.team1, match.team2].sample
      match.update!(winner: winner)

      # Create a result pick
      Pick.create!(
        bracket: march_madness,
        team: winner,
        bracket_match: match,
        round: match.round,
        position: match.position,
        is_result: true
      )
    end
  end

  puts "Created sample results for some matches"
end

puts "Database seeding completed!"
puts ""
puts "Sample data created:"
puts "- #{User.count} users"
puts "- #{Team.count} teams"
puts "- #{Bracket.count} brackets"
puts "- #{BracketMatch.count} bracket matches"
puts "- #{Play.count} plays"
puts "- #{Pick.count} picks"
puts ""
puts "You can now visit http://localhost:3000 to see the application with sample data."
puts "Sample login credentials:"
puts "- Email: admin@bracketmakerbuilder.com, Password: password"
puts "- Email: john@example.com, Password: password"
