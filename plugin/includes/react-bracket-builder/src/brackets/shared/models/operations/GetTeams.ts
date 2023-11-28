import { Round } from '../Round'

export function getTeams(rounds: Round[]) {
  const teams = []
  rounds.forEach((round) => {
    round.matches.forEach((match) => {
      if (!match) {
        return
      }
      const team1 = match.getTeam1()
      if (team1) {
        teams.push(team1)
      }
      const team2 = match.getTeam2()
      if (team2) {
        teams.push(team2)
      }
    })
  })
  return teams
}
