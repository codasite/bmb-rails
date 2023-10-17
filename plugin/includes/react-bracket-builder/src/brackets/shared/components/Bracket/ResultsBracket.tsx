import React from 'react'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { ResultsTeamSlotToggle } from '../TeamSlot'
import { Nullable } from '../../../../utils/types'
import { Team } from '../../models/Team'
import { MatchNode } from '../../models/operations/MatchNode'

export const ResultsBracket = (props: BracketProps) => {
  const { matchTree, setMatchTree } = props

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
    <DefaultBracket
      {...props}
      TeamSlotComponent={ResultsTeamSlotToggle}
      onTeamClick={handleTeamClick}
    />
  )
}
