import React, { useContext } from 'react'
import {
  ActionButton,
  ActionButtonProps,
  ActionButtonBase,
} from '../ActionButtons'
import { BracketActionButtonProps } from '../types'
import { CallbackContext } from '../../context'

export const PaginatedBracketButtonBase = (props: ActionButtonProps) => {
  return (
    <ActionButtonBase
      borderRadius={8}
      fontWeight={700}
      fontSize={24}
      height={48}
      {...props}
    />
  )
}

export const DefaultNextButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = 'transparent'
  const border = disabled ? 'white/20' : 'white'
  const textColor = disabled ? 'white/20' : 'white'

  return (
    <PaginatedBracketButtonBase
      backgroundColor={background}
      textColor={textColor}
      borderColor={border}
      borderWidth={4}
      {...props}
    >
      Next
    </PaginatedBracketButtonBase>
  )
}

export const DefaultFinalButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = disabled ? 'white/20' : 'white'
  const textColor = 'dd-blue'
  return (
    <PaginatedBracketButtonBase
      backgroundColor={background}
      textColor={textColor}
      width={300}
      {...props}
    >
      View Full Bracket
    </PaginatedBracketButtonBase>
  )
}

export const ResultsNextButton = (props: ActionButtonProps) => {
  const onFinished = useContext(CallbackContext)
  return (
    <div className="tw-flex tw-flex-col tw-gap-10">
      <DefaultNextButton {...props} />
      <PaginatedBracketButtonBase
        backgroundColor={'yellow/15'}
        borderColor={'yellow'}
        textColor={'yellow'}
        borderWidth={4}
        onClick={onFinished}
      >
        Full Bracket
      </PaginatedBracketButtonBase>
    </div>
  )
}

export const ResultsFinalButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = disabled ? 'yellow/20' : 'yellow'
  const textColor = 'dd-blue'
  return (
    <PaginatedBracketButtonBase
      backgroundColor={background}
      textColor={textColor}
      width={300}
      {...props}
    >
      View Full Bracket
    </PaginatedBracketButtonBase>
  )
}
