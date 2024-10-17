import { Team } from '../Team'
import { MatchTree } from '../MatchTree'

export function setTeams(
  matchTree: MatchTree,
  teams: Team[],
  verifyTeamsLength = true
) {
  if (matchTree.getNumTeams() != teams.length && verifyTeamsLength) {
    throw new Error(
      `Number of teams (${
        teams.length
      }) does not match number of teams in bracket (${matchTree.getNumTeams()})`
    )
  }
  const rounds = matchTree.rounds
  let index = 0
  rounds.forEach((round) => {
    round.matches.forEach((match) => {
      if (index >= teams.length) {
        return
      }
      if (!match) {
        return
      }
      if (!match.left) {
        match.setTeam1(teams[index++])
      }
      if (!match.right) {
        match.setTeam2(teams[index++])
      }
    })
  })
}
