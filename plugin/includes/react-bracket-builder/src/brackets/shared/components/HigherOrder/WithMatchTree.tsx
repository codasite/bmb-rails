import React, { useState, useEffect, createContext, useContext } from 'react'
import { MatchTree } from '../../models/MatchTree'
import { useAppSelector, useAppDispatch } from '../../app/hooks'
import { setMatchTree, selectMatchTree } from '../../features/matchTreeSlice'
import { bracketBuilderStore } from '../../app/store'
import { Provider } from 'react-redux'
import WithProvider from './WithProvider'

export const WithMatchTree = (Component: React.FC<any>) => {
  return (props: any) => {
    const matchTree = useAppSelector(selectMatchTree)
    const dispatch = useAppDispatch()
    const setTree = (matchTree: MatchTree) =>
      dispatch(setMatchTree(matchTree.serialize()))
    return (
      <Provider store={bracketBuilderStore}>
        <Component matchTree={matchTree} setMatchTree={setTree} {...props} />
      </Provider>
    )
  }
}

export default WithMatchTree
