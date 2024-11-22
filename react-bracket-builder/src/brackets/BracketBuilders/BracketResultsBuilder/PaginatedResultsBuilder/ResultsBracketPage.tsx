// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import { MatchTree } from '../../../shared/models/MatchTree'
import { PaginatedResultsBracket } from '../../../shared/components/Bracket/PaginatedResultsBracket'

interface PickableBracketPageProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  onFinished?: () => void
}

export const ResultsBracketPage = (props: PickableBracketPageProps) => {
  const { matchTree, setMatchTree, onFinished } = props

  let containerProps = {
    className: 'wpbb-reset tw-uppercase tw-dark tw-bg-dd-blue',
  }

  if (matchTree?.allPicked()) {
    containerProps['style'] = {
      backgroundImage: `url(${darkBracketBg})`,
      backgroundRepeat: 'no-repeat',
      backgroundSize: 'cover',
      backgroundPosition: 'center',
    }
  }
  return (
    <div {...containerProps}>
      {matchTree && (
        <PaginatedResultsBracket
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          onFinished={onFinished}
        />
      )}
    </div>
  )
}
