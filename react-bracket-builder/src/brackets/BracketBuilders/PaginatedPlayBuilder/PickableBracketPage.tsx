import { PaginatedPickableBracket } from '../../shared/components/Bracket'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import { MatchTree } from '../../shared/models/MatchTree'

interface PickableBracketPageProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  onFinished?: () => void
}

export const PickableBracketPage = (props: PickableBracketPageProps) => {
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
        <PaginatedPickableBracket
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          onFinished={onFinished}
        />
      )}
    </div>
  )
}
