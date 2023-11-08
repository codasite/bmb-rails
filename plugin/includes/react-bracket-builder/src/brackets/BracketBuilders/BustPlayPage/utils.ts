import { useContext } from 'react'
import {
  MatchTreeContext,
  MatchTreeContext1,
  MatchTreeContext2,
} from '../../shared/context/context'

export const getBustTrees = () => {
  const context1 = useContext(MatchTreeContext1)
  const context2 = useContext(MatchTreeContext2)

  return {
    busterTree: context1.matchTree,
    setBusterTree: context1.setMatchTree,
    busteeTree: context2.matchTree,
    setBusteeTree: context2.setMatchTree,
  }
}
