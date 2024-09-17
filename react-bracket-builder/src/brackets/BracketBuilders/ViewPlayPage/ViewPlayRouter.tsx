import React, { useState } from 'react'
import { ViewPlayPageProps } from './types'
import ViewStandardPlayPage from './ViewStandardPlay/ViewStandardPlay'
import { WithWindowDimensions } from '../../shared/components/HigherOrder/WithWindowDimensions'
import { WithBracketMeta } from '../../shared/components/HigherOrder'
import { ViewBracketResultsPage } from '../ViewBracketResultsPage/ViewBracketResultsPage'
import ViewVotingPlay from '../../../features/VotingBracket/ViewVotingPlay/ViewVotingPlay'

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
    if (props.bracketPlay.bracket?.isVoting) {
      return <ViewVotingPlay {...props} />
    }
    return <ViewStandardPlayPage {...props} />
  }
}

const WrappedViewPlayPage = WithWindowDimensions(
  WithBracketMeta(ViewPlayRouter)
)
export default WrappedViewPlayPage
