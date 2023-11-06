import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { getUniqueTeamClass } from '../Bracket/utils'

export const DefaultTeamSlot = (props: TeamSlotProps) => {
  const {
    team,
    match,
    teamPosition,
    height,
    width = 115,
    fontWeight = 500,
    fontSize = 16,
    getTeamClass = getUniqueTeamClass,
  } = props
  // console.log('winner', winner)
  const teamClass = getTeamClass(
    match.roundIndex,
    match.matchIndex,
    teamPosition ? teamPosition : 'left'
  )
  const active = true

  const baseStyles = [
    teamClass,
    'tw-flex',
    'tw-justify-center',
    'tw-items-center',
    'tw-whitespace-nowrap',
    `tw-w-[${width}px]`,
    `tw-h-[${height}px]`,
    'tw-text-14',
    'tw-font-500',
  ]

  const activeStyles = [
    'tw-bg-dd-blue',
    'dark:tw-bg-white',
    'tw-text-white',
    'dark:tw-text-dd-blue',
  ]

  const inactiveStyles = [
    'tw-border-2',
    'tw-border-solid',
    'tw-border-dd-blue/50',
    'dark:tw-border-white/50',
    'tw-text-dd-blue',
    'dark:tw-text-white',
  ]

  const styles = [
    ...baseStyles,
    ...(active ? activeStyles : inactiveStyles),
  ].join(' ')

  return (
    <div className={styles}>
      <span className={`tw-font-${fontWeight} tw-text-${fontSize}`}>
        {team ? team.name : ''}
      </span>
    </div>
  )
}
