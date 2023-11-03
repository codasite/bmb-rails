import React, { useState, useEffect } from 'react'
import { BustPlayBuilderProps } from '../types'
import { PaginatedPickableBracket } from '../../../shared/components/Bracket'
import { LandingPage } from './LandingPage'
import BustPlayPage from '../BustPlayPage'
import { FullBracketPage } from './FullBracketPage'
import { CallbackContext } from '../../../shared/context'

export const PaginatedBustPlayBuilder = (props: BustPlayBuilderProps) => {
  console.log('PaginatedBustPlayBuilder');
  const {
    matchTree,
    setMatchTree,
    darkMode,
    setDarkMode,
    processing,
    // handleUpdatePicks,
  } = props

  const [page, setPage] = useState('landing')

  useEffect(() => {
    if (!matchTree) {
      return
    }
    if (matchTree.allPicked()) {
      console.log('all picked')
      setPage('final')
    } else if (matchTree.anyPicked()) {
      setPage('bracket')
    }
  }, [])

  const onStart = () => {
    setPage('bracket')
  }

  const onFinished = () => {
    setPage('final')
  }

  let element: JSX.Element | null = null

  switch (page) {
    case 'landing':
      element = <LandingPage matchTree={matchTree} onStart={onStart} />
      break
    case 'bracket':
      element = (
        <CallbackContext.Provider value={onFinished}>
          <BustPlayPage
            matchTree={matchTree}
            setMatchTree={setMatchTree}
            onFinished={onFinished}
          />
        </CallbackContext.Provider>
      )
      break
    case 'final':
      element = (
        <FullBracketPage
          matchTree={matchTree}
          darkMode={darkMode}
          setDarkMode={setDarkMode}
          processing={processing}
          // handleUpdatePicks={handleUpdatePicks}
        />
      )
      break
  }

  return element
}
