import React from 'react'
import { MatchNode, Team } from '../models/MatchTree'
import { BracketProps } from './types'
import { DefaultBracket } from './DefaultBracket'
import { TeamSlotToggle } from './TeamSlot'

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
