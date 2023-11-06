import redBracketBg from '../../../shared/assets/bracket-bg-red.png'
import { PaginatedBusterBracket } from '../../../shared/components/Bracket/PaginatedBusterBracket'
import { BracketPagesProps } from '../../PaginatedBuilderBase/types'
import { getBustTrees } from '../utils'

export const BustBracketPages = (props: BracketPagesProps) => {
  console.log('BustBracketPages')
  const { matchTree: baseTree, setMatchTree: setBaseTree, onFinished } = props
  const { busterTree } = getBustTrees()

  let containerProps = {
    className: 'wpbb-reset tw-uppercase tw-dark tw-bg-dd-blue',
  }

  if (baseTree?.allPicked()) {
    containerProps['style'] = {
      backgroundImage: `url(${redBracketBg})`,
      backgroundRepeat: 'no-repeat',
      backgroundSize: 'cover',
      backgroundPosition: 'center',
    }
  }
  return (
    <div {...containerProps}>
      {baseTree && busterTree && (
        <div className="tw-flex tw-flex-col">
          <PaginatedBusterBracket
            matchTree={baseTree}
            setMatchTree={setBaseTree}
            onFinished={onFinished}
          />
        </div>
      )}
    </div>
  )
}
