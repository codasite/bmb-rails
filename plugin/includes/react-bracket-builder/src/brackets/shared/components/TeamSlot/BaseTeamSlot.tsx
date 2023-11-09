import React, { useState, useContext } from 'react'
import { TeamSlotProps } from './../types'
import { getUniqueTeamClass, getTeamFontSize } from '../Bracket/utils'

export const BaseTeamSlot = (props: TeamSlotProps) => {
  const {
    team,
    match,
    teamPosition,
    height,
    width = 115,
    fontWeight = 500,
    getFontSize = getTeamFontSize,
    textColor = 'white',
    backgroundColor,
    borderColor,
    getTeamClass = getUniqueTeamClass,
    onTeamClick,
    children,
  } = props
  const teamClass = getTeamClass(
    match.roundIndex,
    match.matchIndex,
    teamPosition ? teamPosition : 'left'
  )

  const fontSizeToUse = getFontSize(match.roundIndex, team)

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
      onTeamClick(match, teamPosition, team)
    }
  }

  return (
    <div className={styles} onClick={handleTeamClick}>
      {children ? (
        children
      ) : (
        <span
          className={`tw-font-${fontWeight}`}
          style={{ fontSize: fontSizeToUse }}
        >
          {team ? team.name : ''}
        </span>
      )}
    </div>
  )
}
