import React, { useEffect } from 'react'
import {
  PaginatedBracketProps,
  PaginatedDefaultBracketProps,
  PaginatedNavButtonsProps,
} from '../types'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { ResultsBracket } from './ResultsBracket'
import { ResultsNavButtons } from './BracketActionButtons'
import { BusterBracket } from './BusterBracket'
import { DefaultFullBracketButton } from './BracketActionButtons'
import { DefaultFinalButton } from './BracketActionButtons'
import { DefaultNavButtons } from './BracketActionButtons'
import { DefaultNextButton } from './BracketActionButtons'
import { DefaultPrevButton } from './BracketActionButtons'
import { ActionButtonProps } from '../ActionButtons'

export const BusterNavButtons = (props: PaginatedNavButtonsProps) => {
  const {
    PrevButtonComponent = DefaultPrevButton,
    NextButtonComponent = DefaultNextButton,
    FullBracketBtnComponent = BusterFullBracketButton,
    FinalButtonComponent = BusterFinalButton,
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

export const BusterFullBracketButton = (props: ActionButtonProps) => {
  return <DefaultFullBracketButton variant="red" {...props} />
}

export const BusterFinalButton = (props: ActionButtonProps) => {
  return <DefaultFinalButton variant="red" {...props} />
}

export const PaginatedBusterBracket = (props: PaginatedBracketProps) => {
  const { matchTree } = props
  const [page, setPage] = React.useState(0)
  const newProps: PaginatedDefaultBracketProps = {
    ...props,
    page,
    setPage,
    NavButtonsComponent: BusterNavButtons,
  }

  return (
    <BusterBracket BracketComponent={PaginatedDefaultBracket} {...newProps} />
  )
}
