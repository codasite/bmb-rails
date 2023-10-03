import React, { useState, useContext } from 'react'
import { TeamSlotProps } from './../types'
import { getUniqueTeamClass } from '../../utils'

export const BaseTeamSlot = (props: TeamSlotProps) => {
  const {
    team,
    match,
    teamPosition,
    height,
    width = 115,
    fontWeight = 500,
    fontSize = 16,
    textColor = 'white',
    backgroundColor,
    borderColor,
    getTeamClass = getUniqueTeamClass,
    onTeamClick,
    children,
  } = props
  // console.log('winner', winner)
  const teamClass = getTeamClass(
    match.roundIndex,
    match.matchIndex,
    teamPosition ? teamPosition : 'left'
  )

  const baseStyles = [
    teamClass,
    'tw-flex',
    'tw-justify-center',
    'tw-items-center',
    'tw-whitespace-nowrap',
    `tw-w-[${width}px]`,
    `tw-h-[${height}px]`,
    `tw-text-${textColor}`,
    `tw-font-${fontWeight}`,
    `tw-text-${fontSize}`,
  ]
  if (onTeamClick) {
    baseStyles.push('tw-cursor-pointer')
  }
  if (backgroundColor) {
    baseStyles.push(`tw-bg-${backgroundColor}`)
  }
  if (borderColor) {
    baseStyles.push(
      ...['tw-border-2', 'tw-border-solid', `tw-border-${borderColor}`]
    )
  }

  const styles = baseStyles.join(' ')

  const handleTeamClick = () => {
    if (onTeamClick) {
      onTeamClick(match, team)
    }
  }

  return (
    <div className={styles} onClick={handleTeamClick}>
      {children ? (
        children
      ) : (
        <span className={`tw-font-${fontWeight} tw-text-${fontSize}`}>
          {team ? team.name : ''}
        </span>
      )}
    </div>
  )
}
