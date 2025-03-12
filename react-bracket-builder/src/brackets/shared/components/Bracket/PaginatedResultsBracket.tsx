import { PaginatedBracketProps } from '../types'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { ResultsBracket } from './ResultsBracket'
import { ResultsNavButtons } from './BracketActionButtons'

export const PaginatedResultsBracket = (props: PaginatedBracketProps) => {
  const newProps: PaginatedBracketProps = {
    ...props,
    disableNext: (visibleMatches) => {
      return false
    },
    NavButtonsComponent: ResultsNavButtons,
  }

  return (
    <ResultsBracket BracketComponent={PaginatedDefaultBracket} {...newProps} />
  )
}
