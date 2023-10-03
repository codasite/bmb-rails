import React, { useState, useContext } from 'react'
//@ts-ignore
import { TeamSlotProps } from '../types'
import { InactiveTeamSlot } from './InactiveTeamSlot'
import { ActiveTeamSlot } from './ActiveTeamSlot'
import { BaseTeamSlot } from './BaseTeamSlot'
import { Team } from '../../models/MatchTree'

export const EditableTeamSlot = (props: TeamSlotProps) => {
  const { team, match, teamPosition, matchTree, setMatchTree } = props

  const [editing, setEditing] = useState(false)
  const [teamName, setTeamName] = useState('')

  const handleClick = () => {
    if (!setMatchTree) {
      return
    }
    setEditing(true)
  }

  const doneEditing = () => {
    setEditing(false)
    if (!setMatchTree) {
      return
    }
    console.log('done editing')
    console.log(teamName)
    const team = new Team(teamName)
    if (teamPosition === 'left') {
      match.setTeam1(team)
    } else if (teamPosition === 'right') {
      match.setTeam2(team)
    } else {
      console.error('Invalid team position')
    }
    setMatchTree(matchTree)
  }

  const handleUpdateTeamName = (e) => {
    const name = e.target.value
    setTeamName(name)
  }

  const label = team ? team.name : 'Add Team'

  return (
    <BaseTeamSlot
      {...props}
      backgroundColor={team && !editing ? 'transparent' : 'white/15'}
      borderColor="white/50"
      textColor="white"
      onTeamClick={handleClick}
    >
      {editing ? (
        <input
          type="text"
          className="tw-w-[inherit] tw-border-none tw-outline-none tw-text-white tw-bg-transparent tw-px-8 tw-font-sans tw-uppercase tw-font-500 tw-text-center"
          autoFocus
          onFocus={(e) => e.target.select()}
          value={teamName}
          onChange={handleUpdateTeamName}
          onBlur={doneEditing}
          onKeyUp={(e) => {
            if (e.key === 'Enter') {
              doneEditing()
            }
          }}
        />
      ) : (
        <span>{label}</span>
      )}
    </BaseTeamSlot>
  )
}
