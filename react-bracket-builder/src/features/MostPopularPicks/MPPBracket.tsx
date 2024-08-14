import React from 'react'
import { BracketProps } from '../../brackets/shared/components/types'
import { DefaultBracket } from '../../brackets/shared/components/Bracket/DefaultBracket'
import { TeamSlotToggle } from '../../brackets/shared/components/TeamSlot'
import { Team } from '../../brackets/shared/models/Team'
import { MatchNode } from '../../brackets/shared/models/operations/MatchNode'
import { PercentageTeamSlot } from './PercentageTeamSlot'

export const MPPBracket = (props: BracketProps) => {
  const { matchTree } = props

  return <DefaultBracket {...props} TeamSlotComponent={PercentageTeamSlot} />
}
