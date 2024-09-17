import { PaginatedBracketProps, PaginatedDefaultBracketProps } from '../types'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { ResultsBracket } from './ResultsBracket'
import { ResultsNavButtons } from './BracketActionButtons'

export const PaginatedResultsBracket = (props: PaginatedBracketProps) => {
  const newProps: PaginatedDefaultBracketProps = {
    ...props,
    forcePageAllPicked: false,
    NavButtonsComponent: ResultsNavButtons,
  }

  return (
    <ResultsBracket BracketComponent={PaginatedDefaultBracket} {...newProps} />
  )
}
