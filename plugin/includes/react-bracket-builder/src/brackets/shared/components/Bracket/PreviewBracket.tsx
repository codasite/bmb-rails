import React from 'react'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { TeamSlotToggle } from '../TeamSlot'
import { Team } from '../../models/Team'
import { MatchNode } from '../../models/operations/MatchNode'

export const PreviewBracket = (props: BracketProps) => {
  const { matchTree } = props

  return <DefaultBracket {...props} TeamSlotComponent={TeamSlotToggle} />
}
