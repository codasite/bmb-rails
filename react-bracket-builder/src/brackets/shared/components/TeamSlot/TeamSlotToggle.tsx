import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { ActiveTeamSlot } from './ActiveTeamSlot'

export const TeamSlotToggle = (
  props: TeamSlotProps & {
    activeTeamSlot?: React.ReactNode
    inactiveTeamSlot?: React.ReactNode
  }
) => {
  const {
    activeTeamSlot = <ActiveTeamSlot {...props} />,
    inactiveTeamSlot = <InactiveTeamSlot {...props} />,
    team,
    match,
  } = props

  if (team && match.getWinner()?.id === team.id) {
    return <>{activeTeamSlot}</>
  }
  return <>{inactiveTeamSlot}</>
}
