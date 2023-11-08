import redBracketBg from '../../../shared/assets/bracket-bg-red.png'
import { PaginatedBusterBracket } from '../../../shared/components/Bracket/PaginatedBusterBracket'
import { BracketPagesProps } from '../../PaginatedBuilderBase/types'
import { getBustTrees } from '../utils'

export const BustBracketPages = (props: BracketPagesProps) => {
  console.log('BustBracketPages')
  const { matchTree, setMatchTree, onFinished } = props
  const { busterTree } = getBustTrees()

  let containerProps = {
    className: 'wpbb-reset tw-uppercase tw-dark tw-bg-dd-blue',
  }

  if (matchTree?.allPicked()) {
    containerProps['style'] = {
      backgroundImage: `url(${redBracketBg})`,
      backgroundRepeat: 'no-repeat',
      backgroundSize: 'cover',
      backgroundPosition: 'center',
    }
  }
  return (
    <div {...containerProps}>
      {matchTree && busterTree && (
        <div className="tw-flex tw-flex-col">
          <PaginatedBusterBracket
            matchTree={matchTree}
            setMatchTree={setMatchTree}
            onFinished={onFinished}
          />
        </div>
      )}
    </div>
  )
}
