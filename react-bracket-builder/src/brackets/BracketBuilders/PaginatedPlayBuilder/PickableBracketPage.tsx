import { PaginatedPickableBracket } from '../../shared/components/Bracket'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import { MatchTree } from '../../shared/models/MatchTree'
import { BracketBackground } from '../../shared/components/BracketBackground'

interface PickableBracketPageProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  onFinished?: () => void
}

export const PickableBracketPage = (props: PickableBracketPageProps) => {
  const { matchTree, setMatchTree, onFinished } = props

  return (
    <BracketBackground
      useImageBackground={matchTree?.allPicked()}
      className="tw-flex tw-py-48"
    >
      {matchTree && (
        <PaginatedPickableBracket
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          onFinished={onFinished}
        />
      )}
    </BracketBackground>
  )
}
