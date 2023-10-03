import React from 'react'
import { MatchNode, Team } from '../../models/MatchTree'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { TeamSlotToggle } from '../TeamSlot'

export const PreviewBracket = (props: BracketProps) => {
  const { matchTree } = props

  return <DefaultBracket {...props} TeamSlotComponent={TeamSlotToggle} />
}
