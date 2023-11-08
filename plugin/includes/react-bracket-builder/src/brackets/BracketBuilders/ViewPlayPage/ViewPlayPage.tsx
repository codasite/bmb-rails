import { ViewPlayPageProps } from './types'
import { BusterPlayPage } from './BusterPlayPage'
import { BracketPlayPage } from './BracketPlayPage'
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
    return <BusterPlayPage {...props} />
  } else {
    return <BracketPlayPage {...props} />
  }
}

const WrappedViewPlayPage = WithWindowDimensions(
  WithBracketMeta(WithDarkMode(ViewPlayPage))
)
export default WrappedViewPlayPage
