import { Nullable } from '../../../utils/types'
import { MatchNode } from './operations/MatchNode'
import { MatchRepr } from '../api/types/bracket'
import { Team } from './Team'

export class Round {
  index: number
  depth: number
  matches: Array<Nullable<MatchNode>>
  constructor(
    index: number,
    depth: number,
    matches: Array<Nullable<MatchNode>> = []
  ) {
    this.index = index
    this.depth = depth
    this.matches = matches
  }
  allPicked(): boolean {
    return this.matches.every((match) => {
      if (match === null) {
        return true
      }
      return match.team1Wins || match.team2Wins
    })
  }
  getTeam(teamId: number): Nullable<Team> {
    for (const [index, match] of this.matches.entries()) {
      if (match === null) {
        continue
      }
      const team1 = match.getTeam1()
      const team2 = match.getTeam2()

      if (team1?.id === teamId) {
        team1.side = index < this.matches.length / 2 ? 'left' : 'right'
        return team1
      }

      if (team2?.id === teamId) {
        team2.side = index < this.matches.length / 2 ? 'left' : 'right'
        return team2
      }
    }

    return null
  }
  serialize(): Nullable<MatchRepr>[] {
    return this.matches.map((match, i) => {
      if (match === null) {
        return null
      }
      return match.serialize()
    })
  }
}
