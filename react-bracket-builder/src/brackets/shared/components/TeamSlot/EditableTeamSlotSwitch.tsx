import React from 'react'
import { TeamSlotProps } from '../types'
import { EditableTeamSlot } from './EditableTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'

export const DisabledTeamSlot = (props: TeamSlotProps) => {
  return <BaseTeamSlot {...props} borderColor="white/25" borderWidth={2} />
}

export const EditableTeamSlotSwitch = (props: TeamSlotProps) => {
  const { match, teamPosition, setMatchTree } = props
  let editable = match.isEditable(teamPosition)
  if (!setMatchTree) {
    editable = false
  }

  return editable ? (
    <EditableTeamSlot {...props} />
  ) : (
    <DisabledTeamSlot {...props} />
  )
}
