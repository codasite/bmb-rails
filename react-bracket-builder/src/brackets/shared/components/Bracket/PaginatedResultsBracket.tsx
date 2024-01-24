import React, { useEffect } from 'react'
import { PaginatedBracketProps, PaginatedDefaultBracketProps } from '../types'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'
import { ResultsBracket } from './ResultsBracket'
import { ResultsNavButtons } from './BracketActionButtons'

export const PaginatedResultsBracket = (props: PaginatedBracketProps) => {
  const { matchTree } = props
  const [page, setPage] = React.useState(0)
  const newProps: PaginatedDefaultBracketProps = {
    ...props,
    page,
    setPage,
    forcePageAllPicked: false,
    NavButtonsComponent: ResultsNavButtons,
  }

  useEffect(() => {
    // try to determine page from matchTree
    if (!matchTree.anyPicked()) {
      return
    }
    if (matchTree.allPicked()) {
      return setPage((matchTree.rounds.length - 1) * 2)
    }
    // find first unpicked match
    const firstUnpickedMatch = matchTree.findMatch(
      (match) => match && !match.isPicked()
    )
    if (!firstUnpickedMatch) {
      return
    }
    const { roundIndex, matchIndex } = firstUnpickedMatch
    const numMatches = matchTree.rounds[roundIndex].matches.length
    let pageNum = roundIndex * 2
    if (matchIndex >= numMatches / 2) {
      pageNum++
    }
    setPage(pageNum)
  }, [])

  return (
    <ResultsBracket BracketComponent={PaginatedDefaultBracket} {...newProps} />
  )
}
