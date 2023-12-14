import { ViewPlayPageProps } from './types'
import { BustPlay } from './BustPlay'
import { ViewPlay } from './ViewPlay'
import { WithWindowDimensions } from '../../shared/components/HigherOrder/WithWindowDimensions'
import {
  WithBracketMeta,
  WithDarkMode,
} from '../../shared/components/HigherOrder'

const ViewPlayPage = (props: ViewPlayPageProps) => {
  const { bracketPlay: play } = props

  if (!play) {
    return <div>Play not found</div>
  } else if (play.bustedPlay) {
    return <BustPlay {...props} />
  } else {
    return <ViewPlay {...props} />
  }
}

const WrappedViewPlayPage = WithWindowDimensions(
  WithBracketMeta(WithDarkMode(ViewPlayPage))
)
export default WrappedViewPlayPage
