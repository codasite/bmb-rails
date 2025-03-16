// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { MatchTree } from '../../../shared/models/MatchTree'
import { PaginatedResultsBracket } from '../../../shared/components/Bracket/PaginatedResultsBracket'
import { BracketBackground } from '../../../shared/components/BracketBackground'

interface PickableBracketPageProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  onFinished?: () => void
}

export const ResultsBracketPage = (props: PickableBracketPageProps) => {
  const { matchTree, setMatchTree, onFinished } = props

  return (
    <BracketBackground
      useImageBackground={matchTree?.allPicked()}
      className="tw-flex tw-py-48"
    >
      {matchTree && (
        <PaginatedResultsBracket
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          onFinished={onFinished}
        />
      )}
    </BracketBackground>
  )
}
