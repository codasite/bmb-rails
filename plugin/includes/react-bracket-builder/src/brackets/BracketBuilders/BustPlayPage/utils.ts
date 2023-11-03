import { useContext } from 'react'
import {
  MatchTreeContext,
  MatchTreeContext1,
  MatchTreeContext2,
} from '../../shared/context'

export const getBustTrees = () => {
  const context0 = useContext(MatchTreeContext)
  const context1 = useContext(MatchTreeContext1)
  const context2 = useContext(MatchTreeContext2)

  return {
    baseTree: context0.matchTree,
    setBaseTree: context0.setMatchTree,
    busterTree: context1.matchTree,
    setBusterTree: context1.setMatchTree,
    busteeTree: context2.matchTree,
    setBusteeTree: context2.setMatchTree,
  }
}
