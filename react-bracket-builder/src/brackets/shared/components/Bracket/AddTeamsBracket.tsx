// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { EditableTeamSlotSwitch } from '../TeamSlot'
import { AddTeamsFinalMatchChildren } from '../MatchBox/Children'
import { EditableTitleComponent } from '../MatchBox/Children/EditableTitleComponent'
export const AddTeamsBracket = (props: BracketProps) => {
  console.log('add teams bracket', props)
  return (
    <DefaultBracket
      {...props}
      TeamSlotComponent={EditableTeamSlotSwitch}
      MatchBoxChildComponent={AddTeamsFinalMatchChildren}
      TitleComponent={EditableTitleComponent}
      lineStyle={{
        className: '!tw-border-t-[#333551]',
      }}
    />
  )
}
