import { Nullable } from '../../../utils/types'
import {
  MatchPicks,
  MatchReq,
  MatchRes,
  MatchTreeRepr,
} from '../api/types/bracket'
import { WildcardPlacement } from './WildcardPlacement'
import { Team } from './Team'
import { MatchNode } from './operations/MatchNode'
import { Round } from './Round'
import { matchReprFromNumTeams } from './operations/MatchReprFromNumTeams'
import { getNumRounds } from './operations/GetNumRounds'
import { matchReprFromRes } from './operations/MatchReprFromRes'
import { linkNodes } from './operations/LinkNodes'

export class MatchTree {
  rounds: Round[]
  private numTeams: number
  private wildcardPlacement?: WildcardPlacement

  constructor(rounds: Round[] = [], wildcardPlacement?: WildcardPlacement) {
    linkNodes(rounds)
    this.rounds = rounds
    this.wildcardPlacement = wildcardPlacement
  }

  getRootMatch(): Nullable<MatchNode> {
    const lastRound = this.rounds[this.rounds.length - 1]
    if (!lastRound) {
      return null
    }
    return lastRound.matches[0]
  }

  clone(): MatchTree {
    return MatchTree.deserialize(this.serialize())
  }

  syncPick(otherMatch?: Nullable<MatchNode>): void {
    if (!otherMatch) {
      return
    }

    let thisMatch =
      this.rounds[otherMatch.roundIndex].matches[otherMatch.matchIndex]
    if (!thisMatch) {
      return
    }

    thisMatch.team1Wins = otherMatch.team1Wins
    thisMatch.team2Wins = otherMatch.team2Wins

    if (otherMatch.team1Wins) {
      this.syncPick(otherMatch.left)
    } else if (otherMatch.team2Wins) {
      this.syncPick(otherMatch.right)
    }
  }

  /**
   * Returns the total number of POSSIBLE teams in the tournament.
   * This should reflect the number of teams that would be used to create the same structure with MatchTree.fromNumTeams()
   */
  getNumTeams(): number {
    if (this.numTeams) {
      return this.numTeams
    }
    // count all non null matches in the tree
    const numMatches = this.rounds.reduce((acc, round) => {
      return (
        acc +
        round.matches.reduce((acc, match) => {
          if (match) {
            return acc + 1
          }
          return acc
        }, 0)
      )
    }, 0)

    const numTeams = numMatches + 1
    this.numTeams = numTeams

    return numTeams
  }

  getWildcardPlacement(): WildcardPlacement | undefined {
    return this.wildcardPlacement
  }

  serialize(): MatchTreeRepr {
    const tree = this
    const rounds = tree.rounds.map((round) => {
      return round.serialize()
    })
    return {
      rounds: rounds,
      wildcardPlacement: tree.wildcardPlacement,
    }
  }

  advanceTeam = (
    roundIndex: number,
    matchIndex: number,
    left: boolean,
    requireComplete: boolean = false
  ) => {
    if (requireComplete && roundIndex > 0) {
      const prevRound = this.rounds[roundIndex - 1]
      if (prevRound && !prevRound.allPicked()) {
        return
      }
    }
    const round = this.rounds[roundIndex]
    const match = round.matches[matchIndex]
    if (!match) {
      return
    }
    match.team1Wins = false
    match.team2Wins = false
    if (left) {
      match.team1Wins = true
    } else {
      match.team2Wins = true
    }
  }

  anyPicked = (): boolean => {
    return this.rounds.some((round) => {
      return round.matches.some((match) => {
        if (!match) {
          return false
        }
        return match.team1Wins || match.team2Wins
      })
    })
  }

  allPicked = (): boolean => {
    return this.rounds.every((round) => {
      return round.allPicked()
    })
  }

  allTeamsAdded = (): boolean => {
    return this.everyMatch((match) => {
      let hasNeededTeams = true
      if (!match.left && !match.getTeam1()) {
        hasNeededTeams = false
      }
      if (hasNeededTeams && !match.right && !match.getTeam2()) {
        hasNeededTeams = false
      }
      return hasNeededTeams
    })
  }

  toMatchReq = (): MatchReq[] => {
    const matches: MatchReq[] = []
    this.forEachMatch((match, matchIndex, roundIndex) => {
      const matchReq: MatchReq = {
        roundIndex: match.roundIndex,
        matchIndex: match.matchIndex,
      }
      if (!match.left) {
        matchReq.team1 = match.getTeam1()?.toTeamReq()
      }
      if (!match.right) {
        matchReq.team2 = match.getTeam2()?.toTeamReq()
      }
      if (matchReq.team1 || matchReq.team2) {
        matches.push(matchReq)
      }
    })
    return matches
  }

  toMatchPicks = (): MatchPicks[] => {
    const picks: MatchPicks[] = []
    this.rounds.forEach((round, roundIndex) => {
      round.matches.forEach((match, matchIndex) => {
        if (!match) {
          return
        }
        const { team1Wins, team2Wins } = match
        const team1 = match.getTeam1()
        const team2 = match.getTeam2()
        const team1Winner = team1Wins && team1
        const team2Winner = team2Wins && team2

        if (team1Winner && team1.id) {
          picks.push({
            roundIndex,
            matchIndex,
            winningTeamId: team1.id,
          })
        } else if (team2Winner && team2.id) {
          picks.push({
            roundIndex,
            matchIndex,
            winningTeamId: team2.id,
          })
        }
      })
    })
    console.log('picks', picks)
    return picks
  }

  forEachMatch = (
    callback: (match: MatchNode, matchIndex: number, roundIndex: number) => void
  ) => {
    this.rounds.forEach((round, roundIndex) => {
      round.matches.forEach((match, matchIndex) => {
        if (!match) {
          return
        }
        callback(match, matchIndex, roundIndex)
      })
    })
  }

  everyMatch = (
    callback: (
      match: MatchNode,
      matchIndex: number,
      roundIndex: number
    ) => boolean
  ) => {
    return this.rounds.every((round, roundIndex) => {
      return round.matches.every((match, matchIndex) => {
        if (!match) {
          return true
        }
        return callback(match, matchIndex, roundIndex)
      })
    })
  }

  findMatch = (
    callback: (match: Nullable<MatchNode>) => boolean
  ): Nullable<MatchNode> => {
    let foundMatch: Nullable<MatchNode> = null
    this.rounds.some((round) => {
      return round.matches.some((match) => {
        if (callback(match)) {
          foundMatch = match
          return true
        }
        return false
      })
    })
    return foundMatch
  }

  static fromNumTeams(
    numTeams: number,
    wildcardPlacement: WildcardPlacement = WildcardPlacement.Top
  ): MatchTree {
    const matches = matchReprFromNumTeams(numTeams, wildcardPlacement)
    return MatchTree.deserialize({
      rounds: matches,
      wildcardPlacement,
    })
  }

  static fromMatchRes(
    numTeams: number,
    matches: MatchRes[],
    wildcardPlacement?: WildcardPlacement
  ): MatchTree | null {
    const numRounds = getNumRounds(numTeams)

    try {
      const nestedMatches = matchReprFromRes(numRounds, matches)
      return MatchTree.deserialize({
        rounds: nestedMatches,
        wildcardPlacement,
      })
    } catch (e) {
      console.log(e)
      return null
    }
  }

  static fromPicks(
    numTeams: number,
    matches: MatchRes[],
    picks: MatchPicks[],
    wildcardPlacement?: WildcardPlacement
  ): MatchTree | null {
    const matchTree = MatchTree.fromMatchRes(
      numTeams,
      matches,
      wildcardPlacement
    )
    if (!matchTree) {
      return null
    }
    for (const pick of picks) {
      const { roundIndex, matchIndex, winningTeamId } = pick
      const match = matchTree.rounds[roundIndex].matches[matchIndex]
      if (!match) {
        return null
      }
      const team1 = match.getTeam1()
      const team2 = match.getTeam2()
      if (!team1 || !team2) {
        return null
      }
      if (team1.id === winningTeamId) {
        match.team1Wins = true
      } else if (team2.id === winningTeamId) {
        match.team2Wins = true
      } else {
        return null
      }
    }
    return matchTree
  }

  static deserialize(matchTreeRepr: MatchTreeRepr): MatchTree {
    const { rounds: matchRes, wildcardPlacement } = matchTreeRepr
    const rounds = matchRes.map((round, roundIndex) => {
      const depth = matchRes.length - roundIndex - 1
      const newRound = new Round(roundIndex, depth)
      const matches = round.map((match, matchIndex) => {
        if (match === null) {
          return null
        }
        const {
          id,
          team1: team1Repr,
          team2: team2Repr,
          team1Wins,
          team2Wins,
        } = match
        const team1 = team1Repr ? new Team(team1Repr.name, team1Repr.id) : null
        const team2 = team2Repr ? new Team(team2Repr.name, team2Repr.id) : null
        // const newMatch = new MatchNode(roundIndex, matchIndex, depth, match.id, team1, team2, team1Wins, team2Wins)
        const newMatch = new MatchNode({
          id,
          matchIndex,
          roundIndex,
          team1,
          team2,
          team1Wins,
          team2Wins,
          left: null,
          right: null,
          parent: null,
          depth,
        })
        return newMatch
      })
      newRound.matches = matches
      return newRound
    })
    const tree = new MatchTree(rounds, wildcardPlacement)
    return tree
  }
}
