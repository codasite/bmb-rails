import React from 'react'
import { MatchNode, Team } from '../../models/MatchTree'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { BustableTeamSlotToggle, TeamSlotToggle } from '../TeamSlot'
import { Nullable } from '../../../../utils/types'
import { PickableBracket } from './PickableBracket'

export const BustableBracket = (props: BracketProps) => {
  const {
    matchTree,
    setMatchTree,
    BracketComponent = DefaultBracket,
    TeamSlotComponent = BustableTeamSlotToggle,
  } = props

  const handleTeamClick = (
    match: MatchNode,
    position: string,
    team?: Nullable<Team>
  ) => {
    // Match node always comes from buster bracket. Team can come from either the buster or bustee bracket
    console.log('handleTeamClick busting', match, team)
    if (!match) {
      return
    }
    if (!setMatchTree) {
      return
    }
    if (!team) {
      return
    }
    const roundIndex = match.roundIndex
    const matchIndex = match.matchIndex

    const busterMatch = matchTree.rounds[roundIndex].matches[matchIndex]
    const busterTeam =
      position === 'left' ? busterMatch.getTeam1() : busterMatch.getTeam2()

    busterMatch.pick(busterTeam)
    setMatchTree(matchTree)
  }

  return (
    <BracketComponent
      TeamSlotComponent={TeamSlotComponent}
      onTeamClick={handleTeamClick}
      {...props}
    />
  )
}
