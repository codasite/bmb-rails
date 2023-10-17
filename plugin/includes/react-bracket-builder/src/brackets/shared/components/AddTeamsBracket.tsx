import React from 'react'
import { BracketProps } from './types'
import { DefaultBracket } from './DefaultBracket'
import { TeamSlotToggle } from './TeamSlot'
import { Team } from '../models/Team'
import { MatchNode } from '../models/operations/MatchNode'

export const AddTeamsBracket = (props: BracketProps) => {
  const { matchTree, setMatchTree } = props

  const handleTeamClick = (match: MatchNode, team: Team) => {
    if (!match) {
      return
    }
    if (!setMatchTree) {
      return
    }
    if (!team) {
      return
    }
    console.log('AddTeamsBracket handleTeamClick match', match)
  }

  return (
    <DefaultBracket
      {...props}
      TeamSlotComponent={TeamSlotToggle}
      onTeamClick={handleTeamClick}
    />
  )
}
