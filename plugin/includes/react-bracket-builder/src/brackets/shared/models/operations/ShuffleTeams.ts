import { MatchTree } from '../MatchTree'

export const shuffleTeams = (matchTree: MatchTree) => {
  // get all teams
  const teams = []
  // loop through rounds
  matchTree.rounds.forEach((round) => {
    // loop through matches
    round.matches.forEach((match) => {
      // loop through teams
      const team1 = match.getTeam1()
      const team2 = match.getTeam2()
      console.log('team1', team1)
      console.log('team2', team2)
    })
  })

  // shuffle teams
  for (let i = teams.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[teams[i], teams[j]] = [teams[j], teams[i]]
  }

  // set teams back into tree
}
