import { PaginatedBracketProps, PaginatedDefaultBracketProps } from '../types'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { ResultsBracket } from './ResultsBracket'
import { ResultsNavButtons } from './BracketActionButtons'
import { useState } from 'react'

export const PaginatedResultsBracket = (props: PaginatedBracketProps) => {
  const [page, setPage] = useState(0)
  const newProps: PaginatedDefaultBracketProps = {
    ...props,
    page,
    setPage,
    forcePageAllPicked: false,
    NavButtonsComponent: ResultsNavButtons,
  }

  return (
    <ResultsBracket BracketComponent={PaginatedDefaultBracket} {...newProps} />
  )
}
