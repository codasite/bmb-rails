import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { ActiveTeamSlot } from './ActiveTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'
import { MatchTreeContext } from '../../context'

export const BustableTeamSlotToggle = (props: TeamSlotProps) => {
  const { team, match, teamPosition } = props

  const { matchTree: busteeMatchTree, setMatchTree: setBusterMatchTree } =
    useContext(MatchTreeContext)

  const busterPicked = team && match.getWinner() === team ? true : false
  const busterRoundIndex = match.roundIndex
  const busterMatchIndex = match.matchIndex

  const busteeMatch =
    busteeMatchTree.rounds[busterRoundIndex].matches[busterMatchIndex]

  const busteeTeam =
    teamPosition === 'left' ? busteeMatch.getTeam1() : busteeMatch.getTeam2()

  const busteePicked =
    busteeTeam && busteeMatch.getWinner() === busteeTeam ? true : false

  const inactiveSlot = <InactiveTeamSlot {...props} />
  const busteePickedSlot = <InactiveTeamSlot borderColor="blue" {...props} />
  const busterPickedSlot = (
    <BaseTeamSlot textColor={'white'} backgroundColor={'red'} {...props} />
  )

  if (busterPicked) {
    return busterPickedSlot
  } else if (busteePicked) {
    return busteePickedSlot
  }

  return inactiveSlot
}
