import React from 'react'
import { MatchNode, Team } from '../../models/MatchTree'
import { BracketProps } from '../types'
import { DefaultBracket } from '../Bracket/DefaultBracket'
import { TeamSlotToggle } from '../TeamSlot'
import { Nullable } from '../../../../utils/types'

export const PickableBracket = (props: BracketProps) => {
  const {
    matchTree,
    setMatchTree,
    BracketComponent = DefaultBracket,
    TeamSlotComponent = TeamSlotToggle,
  } = props

  const handleTeamClick = (match: MatchNode, team?: Nullable<Team>) => {
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
