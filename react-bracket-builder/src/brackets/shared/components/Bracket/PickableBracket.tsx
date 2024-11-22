// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { TeamSlotToggle } from '../TeamSlot'
import { Nullable } from '../../../../utils/types'
import { Team } from '../../models/Team'
import { MatchNode } from '../../models/operations/MatchNode'

export const PickableBracket = (props: BracketProps) => {
  const {
    matchTree,
    setMatchTree,
    BracketComponent = DefaultBracket,
    TeamSlotComponent = TeamSlotToggle,
  } = props

  const handleTeamClick = (
    match: MatchNode,
    position: string,
    team?: Nullable<Team>
  ) => {
    if (!match) {
      return
    }
    if (!setMatchTree) {
      return
    }
    if (!team) {
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
