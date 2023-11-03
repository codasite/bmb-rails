import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import { PaginatedResultsBracket } from '../../../shared/components/Bracket/PaginatedResultsBracket'
import { BracketPagesProps } from '../../PaginatedBuilderBase/types'

export const BustBracketPages = (props: BracketPagesProps) => {
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
        <div className="tw-flex tw-flex-col">
          <PaginatedResultsBracket
            matchTree={matchTree}
            setMatchTree={setMatchTree}
            onFinished={onFinished}
          />
        </div>
      )}
    </div>
  )
}
