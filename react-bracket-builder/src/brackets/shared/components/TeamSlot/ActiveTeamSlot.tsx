// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useState, useContext } from 'react'
//@ts-ignore
import { DarkModeContext } from '../../context/context'
import { BaseTeamSlot } from './BaseTeamSlot'
import { TeamSlotProps } from '../types'

export const ActiveTeamSlot = (props: TeamSlotProps) => {
  const { darkMode } = useContext(DarkModeContext)

  return (
    <BaseTeamSlot
      {...props}
      textColor={darkMode ? 'dd-blue' : 'white'}
      backgroundColor={darkMode ? 'white' : 'dd-blue'}
      borderWidth={0}
    />
  )
}
