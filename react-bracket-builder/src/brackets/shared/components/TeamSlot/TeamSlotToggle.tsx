import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { ActiveTeamSlot } from './ActiveTeamSlot'

export const TeamSlotToggle = (props: TeamSlotProps) => {
  const { team, match } = props

  const active = team && match.getWinner() === team

  return active ? (
    <ActiveTeamSlot {...props} />
  ) : (
    <InactiveTeamSlot {...props} />
  )
}
