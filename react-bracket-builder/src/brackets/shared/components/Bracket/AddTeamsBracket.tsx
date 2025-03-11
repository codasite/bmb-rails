// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { BracketProps } from '../types'
import { DefaultBracket } from './DefaultBracket'
import { EditableTeamSlotSwitch } from '../TeamSlot'
import { AddTeamsFinalMatchChildren } from '../MatchBox/Children'
import { EditableTitleComponent } from '../MatchBox/Children/EditableTitleComponent'
export const AddTeamsBracket = (props: BracketProps) => {
  const {
    TitleComponent = EditableTitleComponent,
    BracketComponent = DefaultBracket,
  } = props
  return (
    <BracketComponent
      {...props}
      TeamSlotComponent={EditableTeamSlotSwitch}
      MatchBoxChildComponent={AddTeamsFinalMatchChildren}
      TitleComponent={TitleComponent}
      lineStyle={{
        className: '!tw-border-t-[#333551]',
      }}
    />
  )
}
