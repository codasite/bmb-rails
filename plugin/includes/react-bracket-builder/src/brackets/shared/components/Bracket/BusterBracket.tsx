import React, { useContext } from 'react'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { BusterTeamSlotToggle, TeamSlotToggle } from '../TeamSlot'
import { Nullable } from '../../../../utils/types'
import { PickableBracket } from './PickableBracket'
import { BusterMatchTreeContext } from '../../context'
import { Team } from '../../models/Team'
import { MatchNode } from '../../models/operations/MatchNode'

export const BusterBracket = (props: BracketProps) => {
  const {
    matchTree,
    setMatchTree,
    BracketComponent = DefaultBracket,
    TeamSlotComponent = BusterTeamSlotToggle,
  } = props

  const { matchTree: busterMatchTree, setMatchTree: setBusterMatchTree } =
    useContext(BusterMatchTreeContext)

  const handleTeamClick = (
    match: MatchNode,
    position: string,
    team?: Nullable<Team>
  ) => {
    // Match node always comes from buster bracket. Team can come from either the buster or bustee bracket
    if (!match || !team || !setMatchTree || !setBusterMatchTree) {
      return
    }

    const roundIndex = match.roundIndex
    const matchIndex = match.matchIndex

    const busterMatch = busterMatchTree.rounds[roundIndex].matches[matchIndex]
    const busterTeam =
      position === 'left' ? busterMatch.getTeam1() : busterMatch.getTeam2()

    match.pick(team)
    if (!busterTeam) {
      // allow buster to pick teams that don't yet exist in the buster bracket
      busterMatchTree.syncPick(match)
    } else {
      busterMatch.pick(busterTeam)
    }

    setBusterMatchTree(busterMatchTree)
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
