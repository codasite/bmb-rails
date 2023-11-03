import React, { useContext } from 'react'
import {
  ActionButton,
  ActionButtonProps,
  ActionButtonBase,
} from '../ActionButtons'
import { BracketActionButtonProps } from '../types'
import { CallbackContext } from '../../context'
import { ReactComponent as ChevronLeft } from '../../assets/chevron-left.svg'

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

export const PaginatedBracketButton = (props: ActionButtonProps) => {
  const { variant } = props

  switch (variant) {
    case 'white':
      return <WhitePaginatedBracketButton {...props} />
    case 'white-filled':
      return <WhiteFilledPaginatedBracketButton {...props} />
    default:
      return <PaginatedBracketButtonBase {...props} />
  }
}

export const WhitePaginatedBracketButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = 'transparent'
  const border = disabled ? 'white/20' : 'white'
  const textColor = disabled ? 'white/20' : 'white'

  return (
    <PaginatedBracketButtonBase
      backgroundColor={background}
      textColor={textColor}
      borderColor={border}
      borderWidth={1}
      {...props}
    />
  )
}

export const WhiteFilledPaginatedBracketButton = (props: ActionButtonProps) => {
  const { disabled } = props
  const background = disabled ? 'white/20' : 'white'
  const textColor = disabled ? 'white/20' : 'dd-blue'

  return (
    <PaginatedBracketButtonBase
      backgroundColor={background}
      textColor={textColor}
      {...props}
    />
  )
}

export const DefaultPrevButton = (props: ActionButtonProps) => {
  return (
    <PaginatedBracketButton variant="white" {...props}>
      <ChevronLeft />
    </PaginatedBracketButton>
  )
}

export const DefaultNextButton = (props: ActionButtonProps) => {
  return (
    <PaginatedBracketButton variant="white" {...props}>
      Next
    </PaginatedBracketButton>
  )
}

export const DefaultFullBracketButton = (props: ActionButtonProps) => {
  return (
    <PaginatedBracketButton variant="white" fontSize={16} {...props}>
      View Full Bracket
    </PaginatedBracketButton>
  )
}

interface DefaultNavButtonsProps {
  PrevButtonComponent?: React.FC<ActionButtonProps>
  NextButtonComponent?: React.FC<ActionButtonProps>
  FullBracketBtnComponent?: React.FC<ActionButtonProps>
  FinalButtonComponent?: React.FC<ActionButtonProps>
  disablePrev?: boolean
  disableNext?: boolean
  hasNext?: boolean
  onPrev?: () => void
  onNext?: () => void
  onFinished?: () => void
  onFullBracket?: () => void
}

export const DefaultNavButtons = (props: DefaultNavButtonsProps) => {
  const {
    PrevButtonComponent = DefaultPrevButton,
    NextButtonComponent = DefaultNextButton,
    FullBracketBtnComponent = DefaultFullBracketButton,
    FinalButtonComponent = DefaultFinalButton,
    onPrev,
    onNext,
    onFullBracket,
    onFinished,
    disableNext,
    hasNext,
    disablePrev,
  } = props
  return (
    <div className="tw-flex tw-flex-col tw-gap-10 tw-w-full">
      <div className="tw-flex tw-gap-10">
        {!disablePrev && <PrevButtonComponent onClick={onPrev} />}
        <div className="tw-flex tw-flex-col tw-flex-grow">
          {hasNext ? (
            <NextButtonComponent onClick={onNext} disabled={disableNext} />
          ) : (
            <FinalButtonComponent onClick={onFinished} />
          )}
        </div>
      </div>
      {hasNext && <FullBracketBtnComponent onClick={onFullBracket} />}
    </div>
  )
}

export const DefaultFinalButton = (props: ActionButtonProps) => {
  return (
    <PaginatedBracketButton variant="white-filled" fontSize={16} {...props}>
      View Full Bracket
    </PaginatedBracketButton>
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
