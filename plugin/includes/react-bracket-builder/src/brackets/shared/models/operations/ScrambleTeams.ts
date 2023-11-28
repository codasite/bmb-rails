import { MatchTree } from '../MatchTree'
import { getTeams } from './GetTeams'
import { setTeams } from './SetTeams'

export const scrambleTeams = (
  matchTree: MatchTree,
  scrambledIndices: number[],
  randomFloat: () => number = () => Math.random()
) => {
  if (matchTree.getNumTeams() != scrambledIndices.length) {
    throw new Error(
      `Number of scrambledIndices (${
        scrambledIndices.length
      }) does not match number of teams in bracket (${matchTree.getNumTeams()})`
    )
  }
  // get all teams
  const teams = getTeams(matchTree.rounds)

  // scramble teams
  for (let i = teams.length - 1; i > 0; i--) {
    const j = Math.floor(randomFloat() * (i + 1))
    ;[teams[i], teams[j]] = [teams[j], teams[i]]
    ;[scrambledIndices[i], scrambledIndices[j]] = [
      scrambledIndices[j],
      scrambledIndices[i],
    ]
  }

  // set teams back into tree
  setTeams(matchTree, teams)
  return scrambledIndices
}

export const resetTeams = (
  matchTree: MatchTree,
  scrambledIndices: number[]
) => {
  if (matchTree.getNumTeams() != scrambledIndices.length) {
    throw new Error(
      `Number of scrambledIndices (${
        scrambledIndices.length
      }) does not match number of teams in bracket (${matchTree.getNumTeams()})`
    )
  }
  const teams = getTeams(matchTree.rounds)
  const unscrambledTeams = Array(teams.length)

  // Iterate through the scrambled indices and rearrange the array accordingly
  for (let i = 0; i < scrambledIndices.length; i++) {
    const scrambledIndex = scrambledIndices[i]
    unscrambledTeams[scrambledIndex] = teams[i]
  }
  setTeams(matchTree, unscrambledTeams)
}
