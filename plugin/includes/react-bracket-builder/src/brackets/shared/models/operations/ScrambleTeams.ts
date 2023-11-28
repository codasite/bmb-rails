import { MatchTree } from '../MatchTree'
import { getTeams } from './GetTeams'
import { setTeams } from './SetTeams'

export const scrambleTeams = (
  matchTree: MatchTree,
  randomFloat: () => number = () => Math.random()
) => {
  // get all teams
  const teams = getTeams(matchTree.rounds)

  // scramble teams
  for (let i = teams.length - 1; i > 0; i--) {
    const j = Math.floor(randomFloat() * (i + 1))
    ;[teams[i], teams[j]] = [teams[j], teams[i]]
  }

  // set teams back into tree
  setTeams(matchTree, teams)
}

export const resetTeams = (matchTree: MatchTree) => {
  setTeams(matchTree, matchTree.teams)
}
