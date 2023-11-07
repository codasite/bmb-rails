import React, { useContext } from 'react'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { BusterTeamSlotToggle, TeamSlotToggle } from '../TeamSlot'
import { Nullable } from '../../../../utils/types'
import { PickableBracket } from './PickableBracket'
import { BusterMatchTreeContext } from '../../context'
import { Team } from '../../models/Team'
import { MatchNode } from '../../models/operations/MatchNode'
import { getBustTrees } from '../../../BracketBuilders/BustPlayPage/utils'

export const BusterBracket = (props: BracketProps) => {
  const {
    matchTree,
    setMatchTree,
    BracketComponent = DefaultBracket,
    TeamSlotComponent = BusterTeamSlotToggle,
  } = props
  const { busterTree } = getBustTrees()
  console.log('in BusterBracket')
  console.log('busterTree', busterTree)

  const handleTeamClick = (
    match: MatchNode,
    position: string,
    team?: Nullable<Team>
  ) => {
    const roundIndex = match.roundIndex
    const matchIndex = match.matchIndex
    if (!match || !team || !setMatchTree) {
      return
    }
    const busterMatch = busterTree.rounds[roundIndex].matches[matchIndex]
    const busterTeam =
      position === 'left' ? busterMatch.getTeam1() : busterMatch.getTeam2()
    if (!busterTeam) {
      return
    }

    match.pick(team)
    setMatchTree(matchTree)
  }

  return (
    <BracketComponent
      TeamSlotComponent={TeamSlotComponent}
      onTeamClick={setMatchTree ? handleTeamClick : undefined}
      {...props}
    />
  )
}
