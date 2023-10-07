import React, { useContext } from 'react'
//@ts-ignore
import { DarkModeContext } from '../../context'
import { TeamSlotProps } from '../types'
import { BaseTeamSlot } from './BaseTeamSlot'

export const InactiveTeamSlot = (props: TeamSlotProps) => {
  const { team, onTeamClick } = props
  const darkMode = useContext(DarkModeContext)

  return (
    <BaseTeamSlot
      textColor={darkMode ? 'white' : 'dd-blue'}
      borderColor={darkMode ? 'white/50' : 'dd-blue/50'}
      onTeamClick={team ? onTeamClick : undefined}
      {...props}
    />
  )
}
