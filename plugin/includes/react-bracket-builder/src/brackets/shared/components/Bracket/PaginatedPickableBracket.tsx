import React, { useEffect } from 'react'
import { PaginatedBracketProps, PaginatedDefaultBracketProps } from '../types'
import { PickableBracket } from './PickableBracket'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'

export const PaginatedPickableBracket = (props: PaginatedBracketProps) => {
  const { matchTree } = props
  const [page, setPage] = React.useState(0)
  const newProps: PaginatedDefaultBracketProps = {
    ...props,
    page,
    setPage,
    disableNext: (currentRoundMatches) =>
      currentRoundMatches.some((match) => match && !match.isPicked()),
  }

  console.log('PaginatedPickableBracket', matchTree)
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
    <PickableBracket BracketComponent={PaginatedDefaultBracket} {...newProps} />
  )
}
