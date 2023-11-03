import React, { useState, useEffect } from 'react'
import { ResultsBuilderProps } from '../types'
import { PaginatedPickableBracket } from '../../../shared/components/Bracket'
import { LandingPage } from './LandingPage'
import { ResultsBracketPage } from './ResultsBracketPage'
import { FullBracketPage } from './FullBracketPage'
import { CallbackContext } from '../../../shared/context'

export const PaginatedResultsBuilder = (props: ResultsBuilderProps) => {
  const {
    matchTree,
    setMatchTree,
    darkMode,
    setDarkMode,
    processing,
    handleUpdatePicks,
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
        <ResultsBracketPage
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          onFinished={onFinished}
        />
      )
      break
    case 'final':
      element = (
        <FullBracketPage
          matchTree={matchTree}
          darkMode={darkMode}
          setDarkMode={setDarkMode}
          processing={processing}
          handleUpdatePicks={handleUpdatePicks}
        />
      )
      break
  }

  return element
}
