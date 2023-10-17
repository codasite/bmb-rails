import React from 'react'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { EditableTeamSlotSwitch } from '../TeamSlot'
import { AddTeamsFinalMatchChildren } from '../MatchBox/Children/AddTeamsFinalMatchChildren'
import { Team } from '../../models/Team'
import { MatchNode } from '../../models/operations/MatchNode'

export const AddTeamsBracket = (props: BracketProps) => {
  return (
    <DefaultBracket
      {...props}
      TeamSlotComponent={EditableTeamSlotSwitch}
      MatchBoxChildComponent={AddTeamsFinalMatchChildren}
      lineStyle={{
        className: '!tw-border-t-[#333551]',
      }}
    />
  )
}
