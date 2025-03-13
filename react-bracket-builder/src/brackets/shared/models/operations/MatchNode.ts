import { Nullable } from '../../../../utils/types'
import { Team } from '../Team'
import { MatchNodeArgs } from './MatchNodeArgs'
import { MatchPick, MatchRepr } from '../../api/types/bracket'
import { TeamPosition } from '../../components/types'

export class MatchNode {
  id?: number
  matchIndex: number
  roundIndex: number
  private team1: Nullable<Team> = null
  private team2: Nullable<Team> = null
  team1Wins?: boolean = false
  team2Wins?: boolean = false
  left: Nullable<MatchNode> = null
  right: Nullable<MatchNode> = null
  parent: Nullable<MatchNode> = null
  depth: number
  _pick: MatchPick | null = null
  constructor(args: MatchNodeArgs) {
    const {
      id,
      matchIndex,
      roundIndex,
      team1,
      team2,
      team1Wins,
      team2Wins,
      left,
      right,
      parent,
      depth,
      pick,
    } = args
    this.matchIndex = matchIndex
    this.roundIndex = roundIndex
    this.depth = depth
    this.id = id
    this.team1Wins = team1Wins
    this.team2Wins = team2Wins
    this.left = left ? left : null
    this.right = right ? right : null
    this.parent = parent ? parent : null
    this.team1 = team1 ? team1 : null
    this.team2 = team2 ? team2 : null
    this._pick = pick ? pick : null
  }
  serialize(): MatchRepr {
    const { id, matchIndex, roundIndex, team1, team2, team1Wins, team2Wins } =
      this
    return {
      id,
      matchIndex,
      roundIndex,
      team1: team1 ? team1.serialize() : undefined,
      team2: team2 ? team2.serialize() : undefined,
      team1Wins,
      team2Wins,
      pick: this._pick,
    }
  }
  getWinner(): Nullable<Team> {
    if (this.team1Wins) {
      return this.getTeam1()
    } else if (this.team2Wins) {
      return this.getTeam2()
    }
    return null
  }
  isLeftChild(): boolean {
    return this.parent !== null && this.parent.left === this
  }
  isRightChild(): boolean {
    return !this.isLeftChild()
  }
  getTeam(position: TeamPosition): Nullable<Team> {
    if (position === 'left') {
      return this.getTeam1()
    } else if (position === 'right') {
      return this.getTeam2()
    }
    return null
  }
  getTeam1(): Nullable<Team> {
    return this.team1 ?? this.left?.getWinner()
  }
  getTeam2(): Nullable<Team> {
    return this.team2 ?? this.right?.getWinner()
  }
  setTeam1(team: Team): void {
    this.team1 = team
  }
  setTeam2(team: Team): void {
    this.team2 = team
  }
  setTeam(team: Team, isTeam1: boolean): void {
    if (isTeam1) {
      this.setTeam1(team)
    } else {
      this.setTeam2(team)
    }
  }
  pick(team: Nullable<Team>): void {
    if (!team) {
      return
    }
    this.team1Wins = false
    this.team2Wins = false
    if (this.getTeam1() === team) {
      this.team1Wins = true
    } else if (this.getTeam2() === team) {
      this.team2Wins = true
    }
  }
  isPicked(): boolean {
    return this.team1Wins || this.team2Wins
  }
  isLeafTeam(teamPosition: TeamPosition): boolean {
    if (teamPosition === 'left') {
      return this.left === null
    } else if (teamPosition === 'right') {
      return this.right === null
    }
    return false
  }
  hasLeafTeam(): boolean {
    return this.isLeafTeam('left') || this.isLeafTeam('right')
  }
}
