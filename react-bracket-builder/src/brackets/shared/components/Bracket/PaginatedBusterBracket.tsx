import React, { useEffect } from 'react'
import {
  PaginatedBracketProps,
  PaginatedDefaultBracketProps,
  PaginatedNavButtonsProps,
} from '../types'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { BusterBracket } from './BusterBracket'
import { DefaultFullBracketButton } from './BracketActionButtons'
import { DefaultFinalButton } from './BracketActionButtons'
import { DefaultNavButtons } from './BracketActionButtons'
import { ActionButtonProps } from '../ActionButtons'

export const BusterNavButtons = (props: PaginatedNavButtonsProps) => {
  const {
    FullBracketBtnComponent = BusterFullBracketButton,
    FinalButtonComponent = BusterFinalButton,
  } = props

  return (
    <DefaultNavButtons
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
