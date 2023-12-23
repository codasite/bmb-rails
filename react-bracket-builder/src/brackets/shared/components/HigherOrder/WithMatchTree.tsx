import React, { useState } from 'react'
import { MatchTree } from '../../models/MatchTree'
import {
  MatchTreeContext,
  MatchTreeContext1,
  MatchTreeContext2,
} from '../../context/context'

export const WithMatchTree = (Component: React.FC<any>) => {
  return (props: any) => {
    const [matchTree, setMatchTree] = useState<MatchTree>()
    const setClonedMatchTree = (tree: MatchTree) => {
      setMatchTree(tree.clone())
    }
    return (
      <MatchTreeContext.Provider
        value={{
          matchTree: matchTree,
          setMatchTree: setClonedMatchTree,
        }}
      >
        <Component
          matchTree={matchTree}
          setMatchTree={setClonedMatchTree}
          {...props}
        />
      </MatchTreeContext.Provider>
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
