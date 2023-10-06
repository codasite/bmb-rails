import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { ActiveTeamSlot } from './ActiveTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'
import { MatchTreeContext } from '../../context'

export const BustableTeamSlotToggle = (props: TeamSlotProps) => {
  const { team, match, teamPosition } = props

  const { matchTree: busterMatchTree, setMatchTree: setBusterMatchTree } =
    useContext(MatchTreeContext)

  const busteePicked = team && match.getWinner() === team ? true : false
  const busteeRoundIndex = match.roundIndex
  const busteeMatchIndex = match.matchIndex

  const busterMatch =
    busterMatchTree.rounds[busteeRoundIndex].matches[busteeMatchIndex]

  const busterTeam =
    teamPosition === 'left' ? busterMatch.getTeam1() : busterMatch.getTeam2()

  const busterPicked =
    busterTeam && busterMatch.getWinner() === busterTeam ? true : false

  const inactiveSlot = <InactiveTeamSlot {...props} />
  const busteePickedSlot = <InactiveTeamSlot borderColor="blue" {...props} />
  const busterPickedSlot = (
    <BaseTeamSlot textColor={'white'} backgroundColor={'red'} {...props} />
  )

  if (busteePicked) {
    return busteePickedSlot
  } else if (busterPicked) {
    return busterPickedSlot
  }

  return inactiveSlot
}
