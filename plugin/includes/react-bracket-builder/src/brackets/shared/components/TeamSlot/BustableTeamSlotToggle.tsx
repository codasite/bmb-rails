import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { ActiveTeamSlot } from './ActiveTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'

export const BustableTeamSlotToggle = (props: TeamSlotProps) => {
  const { team, match } = props

  const active = team && match.getWinner() === team ? true : false

  return active ? (
    <BaseTeamSlot {...props} textColor={'white'} backgroundColor={'red'} />
  ) : (
    <InactiveTeamSlot {...props} />
  )
}
