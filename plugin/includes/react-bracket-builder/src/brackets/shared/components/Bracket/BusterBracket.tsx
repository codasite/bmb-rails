import React, { useContext } from 'react'
import { MatchNode, Team } from '../../models/MatchTree'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { BusterTeamSlotToggle, TeamSlotToggle } from '../TeamSlot'
import { Nullable } from '../../../../utils/types'
import { PickableBracket } from './PickableBracket'
import { BusterMatchTreeContext } from '../../context'

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
    console.log('handleTeamClick busting', match, team)
    if (!match || !team || !setMatchTree || !setBusterMatchTree) {
      return
    }

    const roundIndex = match.roundIndex
    const matchIndex = match.matchIndex

    const busterMatch = busterMatchTree.rounds[roundIndex].matches[matchIndex]
    const busterTeam =
      position === 'left' ? busterMatch.getTeam1() : busterMatch.getTeam2()

    if (!busterMatch || !busterTeam) {
      return
    }

    busterMatch.pick(busterTeam)
    setBusterMatchTree(busterMatchTree)

    match.pick(team)
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
