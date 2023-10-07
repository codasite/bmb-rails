import React from 'react'
import { MatchNode, Team } from '../../models/MatchTree'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { ResultsTeamSlotToggle } from '../TeamSlot'
import { Nullable } from '../../../../utils/types'

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
