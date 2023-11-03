import React, { useContext } from 'react'
import {
  ActionButton,
  ActionButtonProps,
  ActionButtonBase,
} from '../ActionButtons'
import { BracketActionButtonProps, PaginatedNavButtonsProps } from '../types'
import { CallbackContext } from '../../context'
import { ReactComponent as ChevronLeft } from '../../assets/chevron-left.svg'
import { ReactComponent as ChevronRight } from '../../assets/chevron-right.svg'
import { Next } from 'react-bootstrap/esm/PageItem'

export const PaginatedBracketButton = (props: ActionButtonProps) => {
  return <ActionButton size="small" {...props} />
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
      <span>Next</span>
      <ChevronRight />
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

export const DefaultFinalButton = (props: ActionButtonProps) => {
  return (
    <PaginatedBracketButton
      variant="white"
      filled={true}
      fontSize={16}
      {...props}
    >
      View Full Bracket
    </PaginatedBracketButton>
  )
}

export const DefaultNavButtons = (props: PaginatedNavButtonsProps) => {
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

export const ResultsNavButtons = (props: PaginatedNavButtonsProps) => {
  const {
    PrevButtonComponent = ResultsPrevButton,
    NextButtonComponent = ResultsNextButton,
    FullBracketBtnComponent = ResultsFullBracketButton,
    FinalButtonComponent = ResultsFinalButton,
  } = props

  return (
    <DefaultNavButtons
      PrevButtonComponent={PrevButtonComponent}
      NextButtonComponent={NextButtonComponent}
      FullBracketBtnComponent={FullBracketBtnComponent}
      FinalButtonComponent={FinalButtonComponent}
      {...props}
    />
  )
}

export const ResultsNextButton = (props: ActionButtonProps) => {
  return <DefaultNextButton variant="yellow" {...props} />
}

export const ResultsPrevButton = (props: ActionButtonProps) => {
  return <DefaultPrevButton variant="yellow" {...props} />
}

export const ResultsFullBracketButton = (props: ActionButtonProps) => {
  return <DefaultFullBracketButton variant="small-yellow" {...props} />
}

export const ResultsFinalButton = (props: ActionButtonProps) => {
  return <DefaultFinalButton variant="small-yellow" {...props} />
}
