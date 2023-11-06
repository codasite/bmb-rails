import React from 'react'
import { MatchBoxProps } from '../types'
import { Nullable } from '../../../../utils/types'
//@ts-ignore
import { DefaultTeamSlot } from '../TeamSlot'
import { Team } from '../../models/Team'

export const DefaultMatchBox = (props: MatchBoxProps) => {
  const {
    match,
    matchPosition,
    matchTree,
    setMatchTree,
    TeamSlotComponent = DefaultTeamSlot,
    teamGap = 20,
    teamHeight = 28,
    teamWidth = 115,
    teamFontSize,
    onTeamClick,
  } = props

  const center = matchPosition === 'center'
  const offset = teamHeight + teamGap

  if (!match) {
    return (
      <div
        className={`tw-h-[${teamHeight * 2 + teamGap}px] tw-w-[${teamWidth}px]`}
      />
    )
  }

  const getTeamSlot = (
    team: Nullable<Team> | undefined,
    teamPosition: string
  ) => {
    console.log('teamFontSize: ', teamFontSize);
    return (
      <TeamSlotComponent
        team={team}
        match={match}
        matchTree={matchTree}
        setMatchTree={setMatchTree}
        matchPosition={matchPosition}
        teamPosition={teamPosition}
        height={teamHeight}
        width={teamWidth}
        fontSize={teamFontSize}
        onTeamClick={onTeamClick}
      />
    )
  }

  return (
    <div
      className={`tw-flex tw-flex-col tw-gap-[${teamGap}px] tw-translate-y-[${
        center ? -offset : 0
      }px]`}
    >
      {getTeamSlot(match.getTeam1(), 'left')}
      {getTeamSlot(match.getTeam2(), 'right')}
    </div>
  )
}
