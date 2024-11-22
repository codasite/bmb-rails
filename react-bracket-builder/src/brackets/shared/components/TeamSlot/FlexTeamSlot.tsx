// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { getUniqueTeamClass } from '../Bracket/utils'

export const FlexTeamSlot = (props: TeamSlotProps) => {
  const { team, match, teamPosition, height } = props

  const active =
    teamPosition === 'left' ? match.left === null : match.right === null

  const baseStyles = [
    'tw-rounded-4',
    `tw-h-[${height}px]`,
    'tw-max-w-[150px]',
    'tw-w-full',
  ]

  const activeStyles = ['tw-bg-white/30']

  const inactiveStyles = ['tw-bg-white/10']

  const styles = [
    ...baseStyles,
    ...(active ? activeStyles : inactiveStyles),
  ].join(' ')

  return <div className={styles}></div>
}
