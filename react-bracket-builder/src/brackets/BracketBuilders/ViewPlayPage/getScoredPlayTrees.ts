import { useContext } from 'react'
import {
  MatchTreeContext,
  MatchTreeContext1,
} from '../../shared/context/context'

export const getScoredPlayTrees = () => {
  const context0 = useContext(MatchTreeContext)
  const context1 = useContext(MatchTreeContext1)

  return {
    playTree: context0.matchTree,
    setPlayTree: context0.setMatchTree,
    resultsTree: context1.matchTree,
    setResultsTree: context1.setMatchTree,
  }
}
