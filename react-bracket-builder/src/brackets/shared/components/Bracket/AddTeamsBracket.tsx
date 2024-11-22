// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { EditableTeamSlotSwitch } from '../TeamSlot'
import { AddTeamsFinalMatchChildren } from '../MatchBox/Children'

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
