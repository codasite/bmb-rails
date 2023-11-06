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
    BracketComponent = DefaultBracket,
    TeamSlotComponent = BusterTeamSlotToggle,
  } = props
  const { baseTree, setBaseTree, busterTree, setBusterTree } = getBustTrees()
  console.log('in BusterBracket')
  console.log('busterTree', busterTree)

  const handleTeamClick = (
    match: MatchNode,
    position: string,
    team?: Nullable<Team>
  ) => {
    // Match node always comes from buster bracket. Team can come from either the buster or bustee bracket
    if (!match || !team || !setBaseTree || !setBusterTree) {
      return
    }

    const roundIndex = match.roundIndex
    const matchIndex = match.matchIndex

    const busterMatch = busterTree.rounds[roundIndex].matches[matchIndex]
    const busterTeam =
      position === 'left' ? busterMatch.getTeam1() : busterMatch.getTeam2()

    match.pick(team)
    if (!busterTeam) {
      // allow buster to pick teams that don't yet exist in the buster bracket
      busterTree.syncPick(match)
    } else {
      busterMatch.pick(busterTeam)
    }

    setBusterTree(busterTree)
    setBaseTree(baseTree)
  }

  return (
    <BracketComponent
      TeamSlotComponent={TeamSlotComponent}
      onTeamClick={setBaseTree ? handleTeamClick : undefined}
      {...props}
    />
  )
}
