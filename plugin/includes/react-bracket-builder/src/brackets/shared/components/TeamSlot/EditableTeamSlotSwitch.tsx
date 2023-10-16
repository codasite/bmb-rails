import React from 'react'
import { TeamSlotProps } from '../types'
import { EditableTeamSlot } from './EditableTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'

const DisabledTeamSlot = (props: TeamSlotProps) => {
  return <BaseTeamSlot {...props} borderColor="white/25" />
}

export const EditableTeamSlotSwitch = (props: TeamSlotProps) => {
  const { match, teamPosition } = props

  let editable =
    teamPosition === 'left' ? match.left === null : match.right === null
  if (teamPosition === 'winner') {
    editable = false;
  }

  return editable ? (
    <EditableTeamSlot {...props} />
  ) : (
    <DisabledTeamSlot {...props} />
  )
}
