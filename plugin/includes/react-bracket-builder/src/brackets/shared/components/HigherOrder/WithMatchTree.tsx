import React, { useState, useEffect, createContext, useContext } from 'react'
import { MatchTree } from '../../models/MatchTree'
import { useAppSelector, useAppDispatch } from '../../app/hooks'
import { setMatchTree, selectMatchTree } from '../../features/matchTreeSlice'
import { bracketBuilderStore } from '../../app/store'
import { Provider } from 'react-redux'
import {
  MatchTreeContext,
  MatchTreeContext1,
  MatchTreeContext2,
} from '../../context'

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

export const WithMatchTree3 = (Component: React.ComponentType<any>) => {
  return (props: any) => {
    const [matchTree0, setMatchTree0] = useState<MatchTree>()
    const [matchTree1, setMatchTree1] = useState<MatchTree>()
    const [matchTree2, setMatchTree2] = useState<MatchTree>()
    return (
      <MatchTreeContext.Provider
        value={{
          matchTree: matchTree0,
          setMatchTree: (tree) => {
            setMatchTree0(tree.clone())
          },
        }}
      >
        <MatchTreeContext1.Provider
          value={{
            matchTree: matchTree1,
            setMatchTree: (tree) => {
              console.log('setMatchTree1', tree)
              setMatchTree1(tree.clone())
            },
          }}
        >
          <MatchTreeContext2.Provider
            value={{
              matchTree: matchTree2,
              setMatchTree: (tree) => {
                setMatchTree2(tree.clone())
              },
            }}
          >
            <Component
              matchTree={matchTree0}
              matchTree1={matchTree1}
              matchTree2={matchTree2}
              setMatchTree={setMatchTree0}
              setMatchTree1={setMatchTree1}
              setMatchTree2={setMatchTree2}
              {...props}
            />
          </MatchTreeContext2.Provider>
        </MatchTreeContext1.Provider>
      </MatchTreeContext.Provider>
    )
  }
}

export default WithMatchTree
