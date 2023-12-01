import { Nullable } from '../../../utils/types'
import { MatchNode } from './operations/MatchNode'
import { MatchRepr } from '../api/types/bracket'

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
  serialize(): Nullable<MatchRepr>[] {
    return this.matches.map((match, i) => {
      if (match === null) {
        return null
      }
      return match.serialize()
    })
  }
}
