import React, { useState } from 'react'
import { ViewPlayPageProps } from './types'
import ViewPlayPage from './ViewStandardPlay/ViewPlayPage'
import { WithWindowDimensions } from '../../shared/components/HigherOrder/WithWindowDimensions'
import {
  WithBracketMeta,
  WithDarkMode,
} from '../../shared/components/HigherOrder'
import { ViewBracketResultsPage } from '../ViewBracketResultsPage/ViewBracketResultsPage'

const ViewPlayRouter = (props: ViewPlayPageProps) => {
  const [page, setPage] = useState<'view' | 'results'>('view')
  const goToView = () => setPage('view')
  const goToResults = () => setPage('results')
  if (!props.bracketPlay) {
    return <div>Play not found</div>
  }
  if (page === 'results') {
    return <ViewBracketResultsPage {...props} />
  } else {
    return <ViewPlayPage {...props} />
  }
}

const WrappedViewPlayPage = WithWindowDimensions(
  WithBracketMeta(WithDarkMode(ViewPlayRouter))
)
export default WrappedViewPlayPage
