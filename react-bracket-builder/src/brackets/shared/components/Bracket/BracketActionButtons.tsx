import React from 'react'
import { ActionButton, ActionButtonProps } from '../ActionButtons'
import { PaginatedNavButtonsProps } from '../types'
import { ReactComponent as ChevronLeft } from '../../assets/chevron-left.svg'
import { ReactComponent as ChevronRight } from '../../assets/chevron-right.svg'
import { ReactComponent as EditIcon } from '../../assets/edit-icon.svg'

export const PaginatedBracketButton = (props: ActionButtonProps) => {
  return <ActionButton size="small" {...props} />
}

export const DefaultEditButton = (props: ActionButtonProps) => {
  return (
    <PaginatedBracketButton variant="white" {...props}>
      <EditIcon />
      <span>Edit</span>
    </PaginatedBracketButton>
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
    PrevButtonComponent = DefaultPrevButton,
    NextButtonComponent = DefaultNextButton,
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

export const ResultsFullBracketButton = (props: ActionButtonProps) => {
  return (
    <DefaultFullBracketButton variant="yellow" borderWidth={1} {...props} />
  )
}

export const ResultsFinalButton = (props: ActionButtonProps) => {
  return <DefaultFinalButton variant="yellow" borderWidth={1} {...props} />
}
