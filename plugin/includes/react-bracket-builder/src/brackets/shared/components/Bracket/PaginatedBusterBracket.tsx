import React, { useEffect } from 'react'
import { PaginatedBracketProps, PaginatedDefaultBracketProps } from '../types'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { ResultsBracket } from './ResultsBracket'
import {
  ResultsFinalButton,
  ResultsNavButtons,
  ResultsNextButton,
} from './BracketActionButtons'
import { BusterBracket } from './BusterBracket'

export const PaginatedBusterBracket = (props: PaginatedBracketProps) => {
  const { matchTree } = props
  const [page, setPage] = React.useState(0)
  const newProps: PaginatedDefaultBracketProps = {
    ...props,
    page,
    setPage,
    NavButtonsComponent: ResultsNavButtons,
  }

  return (
    <BusterBracket BracketComponent={PaginatedDefaultBracket} {...newProps} />
  )
}
