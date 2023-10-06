import React from 'react'
import { MatchNode, Team } from '../../models/MatchTree'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { BustableTeamSlotToggle, TeamSlotToggle } from '../TeamSlot'
import { Nullable } from '../../../../utils/types'
import { PickableBracket } from './PickableBracket'

export const BustableBracket = (props: BracketProps) => {
  const {
    matchTree,
    setMatchTree,
    BracketComponent = PickableBracket,
    TeamSlotComponent = BustableTeamSlotToggle,
  } = props
  console.log('BustableBracket', props)

  return <BracketComponent TeamSlotComponent={TeamSlotComponent} {...props} />
}
