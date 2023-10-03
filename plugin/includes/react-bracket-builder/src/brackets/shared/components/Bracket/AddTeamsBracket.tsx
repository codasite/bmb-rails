import React from 'react'
import { MatchNode, Team } from '../../models/MatchTree'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { EditableTeamSlotSwitch } from '../TeamSlot'
import { AddTeamsFinalMatchChildren } from '../MatchBox/Children/AddTeamsFinalMatchChildren'

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
