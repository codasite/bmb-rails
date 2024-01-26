import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'

export const ResultsTeamSlotToggle = (props: TeamSlotProps) => {
  const { team, match } = props

  const active = team && match.getWinner() === team ? true : false

  return active ? (
    <BaseTeamSlot {...props} textColor={'dd-blue'} backgroundColor={'green'} />
  ) : (
    <InactiveTeamSlot {...props} />
  )
}
